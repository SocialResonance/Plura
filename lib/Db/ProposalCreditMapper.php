<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Mapper for handling credits allocated to proposals
 */
class ProposalCreditMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'plura_proposal_credits', ProposalCredit::class);
    }

    /**
     * Find all credit allocations for a specific proposal
     * 
     * @param int $proposalId
     * @return Entity[]|ProposalCredit[]
     */
    public function findByProposal(int $proposalId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('proposal_id', $qb->createNamedParameter($proposalId, IQueryBuilder::PARAM_INT)))
            ->orderBy('created_at', 'DESC');

        return $this->findEntities($qb);
    }

    /**
     * Find credit allocations by a specific user
     * 
     * @param string $userId
     * @param int $limit
     * @param int $offset
     * @return Entity[]|ProposalCredit[]
     */
    public function findByUser(string $userId, int $limit = 50, int $offset = 0): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)))
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $this->findEntities($qb);
    }

    /**
     * Find a credit allocation by proposal and user
     * 
     * @param int $proposalId
     * @param string $userId
     * @return Entity|ProposalCredit|null
     */
    public function findByProposalAndUser(int $proposalId, string $userId): ?ProposalCredit {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('proposal_id', $qb->createNamedParameter($proposalId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));

        try {
            return $this->findEntity($qb);
        } catch (DoesNotExistException $e) {
            return null;
        }
    }

    /**
     * Get the total credits allocated by a user
     * 
     * @param string $userId
     * @return float
     */
    public function getTotalByUser(string $userId): float {
        $qb = $this->db->getQueryBuilder();
        $qb->select($qb->func()->sum('amount', 'total'))
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));

        $result = $qb->execute();
        $row = $result->fetch();
        $result->closeCursor();

        return (float) ($row['total'] ?? 0);
    }

    /**
     * Get the square root of credits allocated by each user for a proposal
     * Used for quadratic funding calculations
     * 
     * @param int $proposalId
     * @return array [userId => sqrt(amount)]
     */
    public function getSquareRootSumByProposal(int $proposalId): array {
        $credits = $this->findByProposal($proposalId);
        $result = [];
        
        foreach ($credits as $credit) {
            $amount = $credit->getAmount();
            $userId = $credit->getUserId();
            $result[$userId] = sqrt(max(0, $amount));
        }
        
        return $result;
    }

    /**
     * Calculate the quadratic funding score for a proposal
     * 
     * @param int $proposalId
     * @return float
     */
    public function calculateQuadraticScore(int $proposalId): float {
        $sqrtSums = $this->getSquareRootSumByProposal($proposalId);
        
        if (empty($sqrtSums)) {
            return 0;
        }
        
        $sum = array_sum($sqrtSums);
        return $sum * $sum; // (√a + √b + √c)²
    }

    /**
     * Update or create a credit allocation
     * 
     * @param int $proposalId
     * @param string $userId
     * @param float $amount
     * @return Entity|ProposalCredit
     */
    public function updateOrCreate(int $proposalId, string $userId, float $amount): ProposalCredit {
        $credit = $this->findByProposalAndUser($proposalId, $userId);
        
        if ($credit === null) {
            // Create new allocation
            $credit = new ProposalCredit();
            $credit->setProposalId($proposalId);
            $credit->setUserId($userId);
            $credit->setAmount($amount);
            
            return $this->insert($credit);
        } else {
            // Update existing allocation
            $newAmount = $credit->getAmount() + $amount;
            $credit->setAmount($newAmount);
            $credit->setCreatedAt(new \DateTime()); // Update timestamp
            
            return $this->update($credit);
        }
    }
}