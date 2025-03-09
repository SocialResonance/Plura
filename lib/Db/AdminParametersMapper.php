<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class AdminParametersMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'plura_admin_parameters', AdminParameters::class);
    }

    /**
     * @param string $parameterName
     * @return Entity|AdminParameters
     * @throws DoesNotExistException
     */
    public function findByName(string $parameterName): AdminParameters {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('parameter_name', $qb->createNamedParameter($parameterName, IQueryBuilder::PARAM_STR)));

        return $this->findEntity($qb);
    }

    /**
     * @return Entity[]|AdminParameters[]
     */
    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->orderBy('parameter_name', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * @param string $parameterName
     * @param string $parameterValue
     * @return Entity|AdminParameters
     */
    public function updateParameter(string $parameterName, string $parameterValue): AdminParameters {
        try {
            $parameter = $this->findByName($parameterName);
            $parameter->setParameterValue($parameterValue);
            $parameter->setLastUpdated(new \DateTime());
            
            return $this->update($parameter);
        } catch (DoesNotExistException $e) {
            // Create new parameter
            $parameter = new AdminParameters();
            $parameter->setParameterName($parameterName);
            $parameter->setParameterValue($parameterValue);
            $parameter->setLastUpdated(new \DateTime());
            
            return $this->insert($parameter);
        }
    }

    /**
     * Get a parameter value, returns default if not found
     * 
     * @param string $parameterName
     * @param string $default
     * @return string
     */
    public function getParameterValue(string $parameterName, string $default = ''): string {
        try {
            $parameter = $this->findByName($parameterName);
            return $parameter->getParameterValue();
        } catch (DoesNotExistException $e) {
            return $default;
        }
    }

    /**
     * Get a parameter value as float, returns default if not found
     * 
     * @param string $parameterName
     * @param float $default
     * @return float
     */
    public function getParameterValueFloat(string $parameterName, float $default = 0.0): float {
        try {
            $parameter = $this->findByName($parameterName);
            return (float) $parameter->getParameterValue();
        } catch (DoesNotExistException $e) {
            return $default;
        }
    }

    /**
     * Get a parameter value as int, returns default if not found
     * 
     * @param string $parameterName
     * @param int $default
     * @return int
     */
    public function getParameterValueInt(string $parameterName, int $default = 0): int {
        try {
            $parameter = $this->findByName($parameterName);
            return (int) $parameter->getParameterValue();
        } catch (DoesNotExistException $e) {
            return $default;
        }
    }
}