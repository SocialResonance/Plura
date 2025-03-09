<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class MatchingFundMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'plura_matching_fund', MatchingFund::class);
    }

    /**
     * Get the matching fund record (there should only be one)
     * 
     * @return Entity|MatchingFund
     */
    public function getMatchingFund(): MatchingFund {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->orderBy('id', 'ASC')
            ->setMaxResults(1);

        try {
            return $this->findEntity($qb);
        } catch (DoesNotExistException $e) {
            // Create the matching fund if it doesn't exist
            $matchingFund = new MatchingFund();
            $matchingFund->setTotalAmount(1000); // Initial amount
            $matchingFund->setLastDistributionDate(new \DateTime());
            return $this->insert($matchingFund);
        }
    }

    /**
     * Update the matching fund amount
     * 
     * @param float $amount Amount to add (can be negative)
     * @return Entity|MatchingFund
     */
    public function updateFundAmount(float $amount): MatchingFund {
        $matchingFund = $this->getMatchingFund();
        $newAmount = $matchingFund->getTotalAmount() + $amount;
        $matchingFund->setTotalAmount($newAmount);
        
        return $this->update($matchingFund);
    }

    /**
     * Set the last distribution date to now
     * 
     * @return Entity|MatchingFund
     */
    public function updateDistributionDate(): MatchingFund {
        $matchingFund = $this->getMatchingFund();
        $matchingFund->setLastDistributionDate(new \DateTime());
        
        return $this->update($matchingFund);
    }
}