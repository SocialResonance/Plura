<?php

declare(strict_types=1);

namespace OCA\Plura\Service;

use OCA\Plura\Db\Proposal;
use OCA\Plura\Db\ProposalMapper;
use OCA\Plura\Db\ProposalCredit;
use OCA\Plura\Db\ProposalCreditMapper;
use OCA\Plura\Db\CreditTransaction;
use OCA\Plura\Db\CreditTransactionMapper;
use OCA\Plura\Db\MatchingFundMapper;
use OCA\Plura\Db\UserCreditsMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUserSession;
use OCP\AppFramework\Db\Entity;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;

class ProposalService {
    /** @var ProposalMapper */
    private $proposalMapper;
    
    /** @var ProposalCreditMapper */
    private $proposalCreditMapper;
    
    /** @var CreditTransactionMapper */
    private $creditTransactionMapper;
    
    /** @var MatchingFundMapper */
    private $matchingFundMapper;
    
    /** @var UserCreditsMapper */
    private $userCreditsMapper;
    
    /** @var IUserSession */
    private $userSession;
    
    /** @var IRootFolder */
    private $rootFolder;
    
    /** @var ParameterService */
    private $parameterService;

    public function __construct(
        ProposalMapper $proposalMapper,
        ProposalCreditMapper $proposalCreditMapper,
        CreditTransactionMapper $creditTransactionMapper,
        MatchingFundMapper $matchingFundMapper,
        UserCreditsMapper $userCreditsMapper,
        IUserSession $userSession,
        IRootFolder $rootFolder,
        ParameterService $parameterService
    ) {
        $this->proposalMapper = $proposalMapper;
        $this->proposalCreditMapper = $proposalCreditMapper;
        $this->creditTransactionMapper = $creditTransactionMapper;
        $this->matchingFundMapper = $matchingFundMapper;
        $this->userCreditsMapper = $userCreditsMapper;
        $this->userSession = $userSession;
        $this->rootFolder = $rootFolder;
        $this->parameterService = $parameterService;
    }

    /**
     * Create a new proposal
     * 
     * @param string $title
     * @param string $description
     * @param string $documentId
     * @param \DateTime|null $deadline
     * @return Entity|Proposal
     * @throws \InvalidArgumentException
     */
    public function createProposal(string $title, string $description, string $documentId, ?\DateTime $deadline = null): Proposal {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \InvalidArgumentException('No user logged in');
        }
        
        // Validate document ID
        $this->validateDocument($documentId, $user->getUID());
        
        // Create proposal
        $proposal = new Proposal();
        $proposal->setTitle($title);
        $proposal->setDescription($description);
        $proposal->setDocumentId($documentId);
        $proposal->setUserId($user->getUID());
        
        // Set deadline if provided
        if ($deadline !== null) {
            $proposal->setDeadline($deadline);
        } else {
            // Set default deadline (14 days from now, or based on parameter)
            $days = $this->parameterService->getIntParameter('proposal_funding_period_days', 14);
            $defaultDeadline = new \DateTime();
            $defaultDeadline->modify("+{$days} days");
            $proposal->setDeadline($defaultDeadline);
        }
        
        return $this->proposalMapper->insert($proposal);
    }

    /**
     * Get a proposal by ID
     * 
     * @param int $id
     * @return Entity|Proposal
     * @throws DoesNotExistException
     */
    public function getProposal(int $id): Proposal {
        $proposal = $this->proposalMapper->find($id);
        
        // Calculate priority score
        $quadraticScore = $this->proposalCreditMapper->calculateQuadraticScore($id);
        $proposal->setPriorityScore($quadraticScore);
        
        return $proposal;
    }

    /**
     * Get all proposals
     * 
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderDirection
     * @return array
     */
    public function getAllProposals(int $limit = 50, int $offset = 0, string $orderBy = 'credits_allocated', string $orderDirection = 'DESC'): array {
        $proposals = $this->proposalMapper->findAll($limit, $offset, $orderBy, $orderDirection);
        
        // Calculate priority scores for all proposals
        foreach ($proposals as $proposal) {
            $quadraticScore = $this->proposalCreditMapper->calculateQuadraticScore($proposal->getId());
            $proposal->setPriorityScore($quadraticScore);
        }
        
        return $proposals;
    }

    /**
     * Get open proposals
     * 
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderDirection
     * @return array
     */
    public function getOpenProposals(int $limit = 50, int $offset = 0, string $orderBy = 'credits_allocated', string $orderDirection = 'DESC'): array {
        $proposals = $this->proposalMapper->findOpen($limit, $offset, $orderBy, $orderDirection);
        
        // Calculate priority scores for all proposals
        foreach ($proposals as $proposal) {
            $quadraticScore = $this->proposalCreditMapper->calculateQuadraticScore($proposal->getId());
            $proposal->setPriorityScore($quadraticScore);
        }
        
        return $proposals;
    }

    /**
     * Get proposals created by the current user
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMyProposals(int $limit = 50, int $offset = 0): array {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \InvalidArgumentException('No user logged in');
        }
        
        return $this->proposalMapper->findByUser($user->getUID(), $limit, $offset);
    }

    /**
     * Allocate credits to a proposal
     * 
     * @param int $proposalId
     * @param float $amount
     * @return array Allocation details
     * @throws \InvalidArgumentException
     * @throws DoesNotExistException
     */
    public function allocateCredits(int $proposalId, float $amount): array {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \InvalidArgumentException('No user logged in');
        }
        
        $userId = $user->getUID();
        
        // Validate amount
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Credit amount must be positive');
        }
        
        // Check if proposal exists and is open
        $proposal = $this->proposalMapper->find($proposalId);
        if ($proposal->getStatus() !== Proposal::STATUS_OPEN) {
            throw new \InvalidArgumentException('Cannot allocate credits to a closed proposal');
        }
        
        // Check if user has enough credits
        $userCredits = $this->userCreditsMapper->getOrCreateUserCredits($userId);
        if ($userCredits->getCreditAmount() < $amount) {
            throw new \InvalidArgumentException('Not enough credits available');
        }
        
        // Begin transaction
        $this->creditTransactionMapper->getDb()->beginTransaction();
        
        try {
            // Create credit transaction (negative amount, decreases user's balance)
            $transaction = $this->creditTransactionMapper->createTransaction(
                $userId,
                -$amount,
                CreditTransaction::TYPE_PROPOSAL_FUND,
                $proposalId
            );
            
            // Allocate credits to proposal
            $proposalCredit = $this->proposalCreditMapper->updateOrCreate($proposalId, $userId, $amount);
            
            // Update proposal's total allocated credits
            $proposal = $this->proposalMapper->updateCredits($proposalId, $amount);
            
            // Calculate new quadratic score
            $quadraticScore = $this->proposalCreditMapper->calculateQuadraticScore($proposalId);
            $proposal->setPriorityScore($quadraticScore);
            
            // Commit transaction
            $this->creditTransactionMapper->getDb()->commit();
            
            return [
                'proposal' => $proposal,
                'transaction' => $transaction,
                'allocation' => $proposalCredit,
                'quadratic_score' => $quadraticScore
            ];
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->creditTransactionMapper->getDb()->rollBack();
            throw $e;
        }
    }

    /**
     * Get detailed proposal information with credit allocations
     * 
     * @param int $proposalId
     * @return array
     * @throws DoesNotExistException
     */
    public function getProposalDetails(int $proposalId): array {
        $proposal = $this->getProposal($proposalId);
        $credits = $this->proposalCreditMapper->findByProposal($proposalId);
        $quadraticScore = $this->proposalCreditMapper->calculateQuadraticScore($proposalId);
        
        $sqrtSums = $this->proposalCreditMapper->getSquareRootSumByProposal($proposalId);
        $rawCredits = $proposal->getCreditsAllocated();
        
        // Calculate matching fund bonus
        $matchingFundBonus = $quadraticScore - $rawCredits;
        $matchingFundBonus = max(0, $matchingFundBonus); // Ensure non-negative
        
        return [
            'proposal' => $proposal,
            'credits' => $credits,
            'calculation' => [
                'raw_credits' => $rawCredits,
                'square_root_sums' => $sqrtSums,
                'quadratic_score' => $quadraticScore,
                'matching_fund_bonus' => $matchingFundBonus
            ]
        ];
    }

    /**
     * Update a proposal
     * 
     * @param int $id
     * @param string $title
     * @param string $description
     * @param string $status
     * @param \DateTime|null $deadline
     * @return Entity|Proposal
     * @throws DoesNotExistException
     * @throws \InvalidArgumentException
     */
    public function updateProposal(int $id, string $title, string $description, string $status, ?\DateTime $deadline = null): Proposal {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \InvalidArgumentException('No user logged in');
        }
        
        $proposal = $this->proposalMapper->find($id);
        
        // Check if user is the creator or an admin
        if ($proposal->getUserId() !== $user->getUID() && !$this->isAdmin()) {
            throw new \InvalidArgumentException('Only the creator can update the proposal');
        }
        
        // Update proposal details
        $proposal->setTitle($title);
        $proposal->setDescription($description);
        
        // Update status if valid
        $validStatuses = [
            Proposal::STATUS_OPEN,
            Proposal::STATUS_CLOSED,
            Proposal::STATUS_COMPLETED,
            Proposal::STATUS_CANCELED
        ];
        
        if (in_array($status, $validStatuses)) {
            $proposal->setStatus($status);
        }
        
        // Update deadline if provided
        if ($deadline !== null) {
            $proposal->setDeadline($deadline);
        }
        
        return $this->proposalMapper->update($proposal);
    }

    /**
     * Validate document ID to check if it exists and user has access
     * 
     * @param string $documentId
     * @param string $userId
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateDocument(string $documentId, string $userId): bool {
        try {
            $userFolder = $this->rootFolder->getUserFolder($userId);
            $nodes = $userFolder->getById((int) $documentId);
            
            if (empty($nodes)) {
                throw new \InvalidArgumentException('Document not found or not accessible');
            }
            
            return true;
        } catch (NotFoundException $e) {
            throw new \InvalidArgumentException('Document not found or not accessible');
        }
    }

    /**
     * Check if current user is an admin
     *
     * @return bool
     */
    private function isAdmin(): bool {
        $user = $this->userSession->getUser();
        if ($user === null) {
            return false;
        }
        
        $groupManager = \OC::$server->getGroupManager();
        return $groupManager->isAdmin($user->getUID());
    }
}