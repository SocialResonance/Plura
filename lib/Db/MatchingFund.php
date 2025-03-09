<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method float getTotalAmount()
 * @method void setTotalAmount(float $totalAmount)
 * @method \DateTime getLastDistributionDate()
 * @method void setLastDistributionDate(\DateTime $lastDistributionDate)
 */
class MatchingFund extends Entity implements JsonSerializable {
    protected $id;
    protected $totalAmount;
    protected $lastDistributionDate;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('totalAmount', 'float');
        $this->addType('lastDistributionDate', 'datetime');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'total_amount' => $this->totalAmount,
            'last_distribution_date' => $this->lastDistributionDate ? $this->lastDistributionDate->format(\DateTime::ATOM) : null,
        ];
    }
}