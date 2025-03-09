<?php

declare(strict_types=1);

namespace OCA\Plura\Service;

use OCA\Plura\Db\AdminParameters;
use OCA\Plura\Db\AdminParametersMapper;
use OCP\IUser;
use OCP\IUserSession;
use OCP\IGroupManager;

class ParameterService {
    /** @var AdminParametersMapper */
    private $adminParametersMapper;
    
    /** @var IUserSession */
    private $userSession;
    
    /** @var IGroupManager */
    private $groupManager;

    public function __construct(
        AdminParametersMapper $adminParametersMapper,
        IUserSession $userSession,
        IGroupManager $groupManager
    ) {
        $this->adminParametersMapper = $adminParametersMapper;
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
    }

    /**
     * Check if the current user is an admin
     * 
     * @return bool
     */
    private function isAdmin(): bool {
        $user = $this->userSession->getUser();
        if ($user === null) {
            return false;
        }
        
        return $this->groupManager->isAdmin($user->getUID());
    }

    /**
     * Get all parameters
     * 
     * @return AdminParameters[]
     * @throws \InvalidArgumentException If the user is not an admin
     */
    public function getAllParameters(): array {
        if (!$this->isAdmin()) {
            throw new \InvalidArgumentException('Only admins can get all parameters');
        }
        
        return $this->adminParametersMapper->findAll();
    }

    /**
     * Get a parameter by name
     * 
     * @param string $name
     * @return string
     */
    public function getParameter(string $name): string {
        return $this->adminParametersMapper->getParameterValue($name);
    }

    /**
     * Get a parameter as float
     * 
     * @param string $name
     * @param float $default
     * @return float
     */
    public function getFloatParameter(string $name, float $default = 0.0): float {
        return $this->adminParametersMapper->getParameterValueFloat($name, $default);
    }

    /**
     * Get a parameter as int
     * 
     * @param string $name
     * @param int $default
     * @return int
     */
    public function getIntParameter(string $name, int $default = 0): int {
        return $this->adminParametersMapper->getParameterValueInt($name, $default);
    }

    /**
     * Update a parameter
     * 
     * @param string $name
     * @param string $value
     * @return AdminParameters
     * @throws \InvalidArgumentException If the user is not an admin
     */
    public function updateParameter(string $name, string $value): AdminParameters {
        if (!$this->isAdmin()) {
            throw new \InvalidArgumentException('Only admins can update parameters');
        }
        
        return $this->adminParametersMapper->updateParameter($name, $value);
    }

    /**
     * Get all system parameters as an associative array
     * 
     * @return array
     */
    public function getSystemParameters(): array {
        $params = $this->adminParametersMapper->findAll();
        $result = [];
        
        foreach ($params as $param) {
            $result[$param->getParameterName()] = $param->getParameterValue();
        }
        
        return $result;
    }
}