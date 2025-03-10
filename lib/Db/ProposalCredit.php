<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method int getProposalId()
 * @method void setProposalId(int $proposalId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method float getAmount()
 * @method void setAmount(float $amount)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 */
class ProposalCredit extends Entity implements JsonSerializable {
    protected $proposalId;
    protected $userId;
    protected $amount;
    protected $createdAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('proposalId', 'integer');
        $this->addType('amount', 'float');
        $this->addType('createdAt', 'datetime');
        
        // Set default values
        $this->setCreatedAt(new \DateTime());
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'proposal_id' => $this->proposalId,
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'created_at' => $this->createdAt ? $this->createdAt->format(\DateTime::ATOM) : null,
        ];
    }
}