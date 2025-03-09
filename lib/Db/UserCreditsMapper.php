<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class UserCreditsMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'plura_user_credits', UserCredits::class);
    }

    /**
     * @param string $userId
     * @return Entity|UserCredits
     * @throws DoesNotExistException
     */
    public function findByUserId(string $userId): UserCredits {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));

        return $this->findEntity($qb);
    }

    /**
     * @param string $userId
     * @return Entity|UserCredits
     */
    public function getOrCreateUserCredits(string $userId): UserCredits {
        try {
            return $this->findByUserId($userId);
        } catch (DoesNotExistException $e) {
            // Create new user credits record
            $userCredits = new UserCredits();
            $userCredits->setUserId($userId);
            $userCredits->setCreditAmount(100); // Default initial credits
            $userCredits->setLastUpdated(new \DateTime());
            
            return $this->insert($userCredits);
        }
    }

    /**
     * @param string $userId
     * @param float $amount
     * @return Entity|UserCredits
     * @throws DoesNotExistException
     */
    public function updateCredits(string $userId, float $amount): UserCredits {
        $userCredits = $this->getOrCreateUserCredits($userId);
        $newAmount = $userCredits->getCreditAmount() + $amount;
        $userCredits->setCreditAmount($newAmount);
        $userCredits->setLastUpdated(new \DateTime());
        
        return $this->update($userCredits);
    }
}