<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getParameterName()
 * @method void setParameterName(string $parameterName)
 * @method string getParameterValue()
 * @method void setParameterValue(string $parameterValue)
 * @method \DateTime getLastUpdated()
 * @method void setLastUpdated(\DateTime $lastUpdated)
 */
class AdminParameters extends Entity implements JsonSerializable {
    protected $parameterName;
    protected $parameterValue;
    protected $lastUpdated;

    public function __construct() {
        $this->addType('lastUpdated', 'datetime');
    }

    public function jsonSerialize(): array {
        return [
            'parameter_name' => $this->parameterName,
            'parameter_value' => $this->parameterValue,
            'last_updated' => $this->lastUpdated ? $this->lastUpdated->format(\DateTime::ATOM) : null,
        ];
    }
}