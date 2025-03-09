<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method float getCreditAmount()
 * @method void setCreditAmount(float $creditAmount)
 * @method \DateTime getLastUpdated()
 * @method void setLastUpdated(\DateTime $lastUpdated)
 */
class UserCredits extends Entity implements JsonSerializable {
    protected $userId;
    protected $creditAmount;
    protected $lastUpdated;

    public function __construct() {
        $this->addType('creditAmount', 'float');
        $this->addType('lastUpdated', 'datetime');
    }

    public function jsonSerialize(): array {
        return [
            'user_id' => $this->userId,
            'credit_amount' => $this->creditAmount,
            'last_updated' => $this->lastUpdated ? $this->lastUpdated->format(\DateTime::ATOM) : null,
        ];
    }
}