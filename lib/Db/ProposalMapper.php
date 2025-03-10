<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ProposalMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'plura_proposals', Proposal::class);
    }

    /**
     * Find a proposal by ID
     * 
     * @param int $id
     * @return Entity|Proposal
     * @throws DoesNotExistException
     */
    public function find(int $id): Proposal {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * Find all proposals
     * 
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderDirection
     * @return Entity[]|Proposal[]
     */
    public function findAll(int $limit = 50, int $offset = 0, string $orderBy = 'credits_allocated', string $orderDirection = 'DESC'): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->orderBy($orderBy, $orderDirection)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $this->findEntities($qb);
    }

    /**
     * Find open proposals
     * 
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderDirection
     * @return Entity[]|Proposal[]
     */
    public function findOpen(int $limit = 50, int $offset = 0, string $orderBy = 'credits_allocated', string $orderDirection = 'DESC'): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('status', $qb->createNamedParameter(Proposal::STATUS_OPEN, IQueryBuilder::PARAM_STR)))
            ->orderBy($orderBy, $orderDirection)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $this->findEntities($qb);
    }

    /**
     * Find proposals created by a specific user
     * 
     * @param string $userId
     * @param int $limit
     * @param int $offset
     * @return Entity[]|Proposal[]
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
     * Find proposals for a specific document
     * 
     * @param string $documentId
     * @param int $limit
     * @param int $offset
     * @return Entity[]|Proposal[]
     */
    public function findByDocument(string $documentId, int $limit = 50, int $offset = 0): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('document_id', $qb->createNamedParameter($documentId, IQueryBuilder::PARAM_STR)))
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $this->findEntities($qb);
    }

    /**
     * Update a proposal's credits allocated
     * 
     * @param int $id
     * @param float $amount
     * @return Entity|Proposal
     * @throws DoesNotExistException
     */
    public function updateCredits(int $id, float $amount): Proposal {
        $proposal = $this->find($id);
        $newAmount = $proposal->getCreditsAllocated() + $amount;
        $proposal->setCreditsAllocated($newAmount);
        
        return $this->update($proposal);
    }

    /**
     * Count total proposals
     * 
     * @return int
     */
    public function count(): int {
        $qb = $this->db->getQueryBuilder();
        $qb->select($qb->func()->count('*', 'count'))
            ->from($this->getTableName());

        $result = $qb->execute();
        $row = $result->fetch();
        $result->closeCursor();

        return (int) ($row['count'] ?? 0);
    }

    /**
     * Count open proposals
     * 
     * @return int
     */
    public function countOpen(): int {
        $qb = $this->db->getQueryBuilder();
        $qb->select($qb->func()->count('*', 'count'))
            ->from($this->getTableName())
            ->where($qb->expr()->eq('status', $qb->createNamedParameter(Proposal::STATUS_OPEN, IQueryBuilder::PARAM_STR)));

        $result = $qb->execute();
        $row = $result->fetch();
        $result->closeCursor();

        return (int) ($row['count'] ?? 0);
    }
}