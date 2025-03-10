<?php

declare(strict_types=1);

namespace OCA\Plura\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getDocumentId()
 * @method void setDocumentId(string $documentId)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method float getCreditsAllocated()
 * @method void setCreditsAllocated(float $creditsAllocated)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 * @method \DateTime|null getDeadline()
 * @method void setDeadline(\DateTime $deadline)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 */
class Proposal extends Entity implements JsonSerializable {
    // Status constants
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED = 'canceled';

    protected $title;
    protected $description;
    protected $documentId;
    protected $status;
    protected $creditsAllocated;
    protected $createdAt;
    protected $deadline;
    protected $userId;
    protected $priorityScore; // Not stored in DB, calculated on demand

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('creditsAllocated', 'float');
        $this->addType('createdAt', 'datetime');
        $this->addType('deadline', 'datetime');
        
        // Set default values
        $this->setStatus(self::STATUS_OPEN);
        $this->setCreditsAllocated(0.0);
        $this->setCreatedAt(new \DateTime());
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'document_id' => $this->documentId,
            'status' => $this->status,
            'credits_allocated' => $this->creditsAllocated,
            'created_at' => $this->createdAt ? $this->createdAt->format(\DateTime::ATOM) : null,
            'deadline' => $this->deadline ? $this->deadline->format(\DateTime::ATOM) : null,
            'user_id' => $this->userId,
            'priority_score' => $this->priorityScore ?? null,
        ];
    }

    /**
     * Set the calculated priority score for this proposal
     * 
     * @param float $score
     * @return void
     */
    public function setPriorityScore(float $score): void {
        $this->priorityScore = $score;
    }

    /**
     * Get the priority score for this proposal
     * 
     * @return float|null
     */
    public function getPriorityScore(): ?float {
        return $this->priorityScore ?? null;
    }
}