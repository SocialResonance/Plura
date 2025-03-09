<?php

declare(strict_types=1);

namespace OCA\Plura\Service;

use OCA\Plura\Db\CreditTransaction;
use OCA\Plura\Db\CreditTransactionMapper;
use OCA\Plura\Db\UserCredits;
use OCA\Plura\Db\UserCreditsMapper;
use OCA\Plura\Db\AdminParametersMapper;
use OCA\Plura\Db\MatchingFundMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUserSession;

class CreditService {
    /** @var UserCreditsMapper */
    private $userCreditsMapper;
    
    /** @var CreditTransactionMapper */
    private $creditTransactionMapper;
    
    /** @var AdminParametersMapper */
    private $adminParametersMapper;
    
    /** @var MatchingFundMapper */
    private $matchingFundMapper;
    
    /** @var IUserSession */
    private $userSession;

    public function __construct(
        UserCreditsMapper $userCreditsMapper,
        CreditTransactionMapper $creditTransactionMapper,
        AdminParametersMapper $adminParametersMapper,
        MatchingFundMapper $matchingFundMapper,
        IUserSession $userSession
    ) {
        $this->userCreditsMapper = $userCreditsMapper;
        $this->creditTransactionMapper = $creditTransactionMapper;
        $this->adminParametersMapper = $adminParametersMapper;
        $this->matchingFundMapper = $matchingFundMapper;
        $this->userSession = $userSession;
    }

    /**
     * Get the current user's credits
     * 
     * @return UserCredits
     */
    public function getCurrentUserCredits(): UserCredits {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \InvalidArgumentException('No user logged in');
        }
        
        return $this->userCreditsMapper->getOrCreateUserCredits($user->getUID());
    }

    /**
     * Get credits for a specific user
     * 
     * @param string $userId
     * @return UserCredits
     */
    public function getUserCredits(string $userId): UserCredits {
        return $this->userCreditsMapper->getOrCreateUserCredits($userId);
    }

    /**
     * Create an initial credit allocation for a new user
     * 
     * @param string $userId
     * @return CreditTransaction
     */
    public function createInitialAllocation(string $userId): CreditTransaction {
        // Get initial credit amount from parameters
        $initialAmount = $this->adminParametersMapper->getParameterValueFloat('initial_credit_amount', 100.0);
        
        // Create the transaction
        return $this->creditTransactionMapper->createTransaction(
            $userId,
            $initialAmount,
            CreditTransaction::TYPE_INITIAL_ALLOCATION
        );
    }

    /**
     * Get transaction history for the current user
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getCurrentUserTransactions(int $limit = 50, int $offset = 0): array {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \InvalidArgumentException('No user logged in');
        }
        
        return $this->creditTransactionMapper->findByUserId($user->getUID(), $limit, $offset);
    }

    /**
     * Get transaction history for a specific user
     * 
     * @param string $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUserTransactions(string $userId, int $limit = 50, int $offset = 0): array {
        return $this->creditTransactionMapper->findByUserId($userId, $limit, $offset);
    }

    /**
     * Add to the matching fund
     * 
     * @param float $amount
     * @return float New total amount
     */
    public function addToMatchingFund(float $amount): float {
        $matchingFund = $this->matchingFundMapper->updateFundAmount($amount);
        return $matchingFund->getTotalAmount();
    }

    /**
     * Get the matching fund total
     * 
     * @return float
     */
    public function getMatchingFundTotal(): float {
        $matchingFund = $this->matchingFundMapper->getMatchingFund();
        return $matchingFund->getTotalAmount();
    }

    /**
     * Create an admin adjustment transaction
     * 
     * @param string $userId
     * @param float $amount
     * @param string $reason
     * @return CreditTransaction
     */
    public function createAdminAdjustment(string $userId, float $amount, string $reason = ''): CreditTransaction {
        // Create the transaction
        $transaction = $this->creditTransactionMapper->createTransaction(
            $userId,
            $amount,
            CreditTransaction::TYPE_ADMIN_ADJUSTMENT
        );
        
        // Log the reason (in a real implementation, this might store the reason in a separate table)
        // For now, we'll just log it
        \OC::$server->getLogger()->info(
            'Admin credit adjustment for user ' . $userId . ': ' . $amount . ' - ' . $reason,
            ['app' => 'plura']
        );
        
        return $transaction;
    }
}