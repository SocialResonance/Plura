<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method float getAmount()
 * @method void setAmount(float $amount)
 * @method string getTransactionType()
 * @method void setTransactionType(string $transactionType)
 * @method int|null getRelatedEntityId()
 * @method void setRelatedEntityId(int $relatedEntityId)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 */
class CreditTransaction extends Entity implements JsonSerializable {
    // Transaction types
    public const TYPE_INITIAL_ALLOCATION = 'initial_allocation';
    public const TYPE_PROPOSAL_FUND = 'proposal_fund';
    public const TYPE_IMPLEMENTATION_REWARD = 'implementation_reward';
    public const TYPE_VOTE_COST = 'vote_cost';
    public const TYPE_PREDICTION_COST = 'prediction_cost';
    public const TYPE_PREDICTION_REWARD = 'prediction_reward';
    public const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';

    protected $userId;
    protected $amount;
    protected $transactionType;
    protected $relatedEntityId;
    protected $createdAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('amount', 'float');
        $this->addType('relatedEntityId', 'integer');
        $this->addType('createdAt', 'datetime');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'transaction_type' => $this->transactionType,
            'related_entity_id' => $this->relatedEntityId,
            'created_at' => $this->createdAt ? $this->createdAt->format(\DateTime::ATOM) : null,
        ];
    }
}