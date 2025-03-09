<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class CreditTransactionMapper extends QBMapper {
    
    /** @var UserCreditsMapper */
    private $userCreditsMapper;

    public function __construct(IDBConnection $db, UserCreditsMapper $userCreditsMapper) {
        parent::__construct($db, 'plura_credit_transactions', CreditTransaction::class);
        $this->userCreditsMapper = $userCreditsMapper;
    }

    /**
     * Find transactions by user ID
     * 
     * @param string $userId
     * @param int $limit
     * @param int $offset
     * @return Entity[]|CreditTransaction[]
     */
    public function findByUserId(string $userId, int $limit = 50, int $offset = 0): array {
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
     * @param string $userId
     * @param string $transactionType
     * @param int $limit
     * @param int $offset
     * @return Entity[]|CreditTransaction[]
     */
    public function findByUserIdAndType(string $userId, string $transactionType, int $limit = 50, int $offset = 0): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)))
            ->andWhere($qb->expr()->eq('transaction_type', $qb->createNamedParameter($transactionType, IQueryBuilder::PARAM_STR)))
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $this->findEntities($qb);
    }

    /**
     * Create a transaction and update user credits
     * 
     * @param string $userId
     * @param float $amount
     * @param string $transactionType
     * @param int|null $relatedEntityId
     * @return Entity|CreditTransaction
     */
    public function createTransaction(string $userId, float $amount, string $transactionType, ?int $relatedEntityId = null): CreditTransaction {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Update user credits
            $this->userCreditsMapper->updateCredits($userId, $amount);
            
            // Create transaction record
            $transaction = new CreditTransaction();
            $transaction->setUserId($userId);
            $transaction->setAmount($amount);
            $transaction->setTransactionType($transactionType);
            if ($relatedEntityId !== null) {
                $transaction->setRelatedEntityId($relatedEntityId);
            }
            $transaction->setCreatedAt(new \DateTime());
            
            $result = $this->insert($transaction);
            
            // Commit transaction
            $this->db->commit();
            
            return $result;
        } catch (\Exception $e) {
            // Rollback on error
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Count transactions by type
     * 
     * @param string $transactionType
     * @return int
     */
    public function countByType(string $transactionType): int {
        $qb = $this->db->getQueryBuilder();
        $qb->select($qb->func()->count('*', 'count'))
            ->from($this->getTableName())
            ->where($qb->expr()->eq('transaction_type', $qb->createNamedParameter($transactionType, IQueryBuilder::PARAM_STR)));

        $result = $qb->execute();
        $row = $result->fetch();
        $result->closeCursor();

        return (int) ($row['count'] ?? 0);
    }

    /**
     * Sum transaction amounts by type
     * 
     * @param string $transactionType
     * @return float
     */
    public function sumByType(string $transactionType): float {
        $qb = $this->db->getQueryBuilder();
        $qb->select($qb->func()->sum('amount', 'sum'))
            ->from($this->getTableName())
            ->where($qb->expr()->eq('transaction_type', $qb->createNamedParameter($transactionType, IQueryBuilder::PARAM_STR)));

        $result = $qb->execute();
        $row = $result->fetch();
        $result->closeCursor();

        return (float) ($row['sum'] ?? 0);
    }
}