<?php

namespace Modules\Operations\Domain\Entities;

use DateTimeImmutable;
use DomainException;
use Modules\Operations\Domain\ValueObjects\WorkItemPriority;
use Modules\Operations\Domain\ValueObjects\WorkItemStatus;

final class WorkItem
{
    public function __construct(
        private readonly string $uuid,
        private readonly string $originType,
        private readonly string $originId,
        private readonly string $channel,
        private string $title,
        private ?string $summary,
        private WorkItemStatus $status,
        private WorkItemPriority $priority,
        private ?int $citizenId = null,
        private ?int $assignedUserId = null,
        private ?int $caseId = null,
        private ?DateTimeImmutable $openedAt = null,
        private ?DateTimeImmutable $firstResponseAt = null,
        private ?DateTimeImmutable $resolvedAt = null,
        private ?DateTimeImmutable $closedAt = null,
        private array $metadata = [],
    ) {
        if ($uuid === '' || $originType === '' || $originId === '' || $channel === '' || $title === '') {
            throw new DomainException('Work item requires UUID, origin, channel and title.');
        }

        $this->openedAt ??= new DateTimeImmutable();
    }

    public function assign(int $userId): void
    {
        if ($this->status->isTerminal()) {
            throw new DomainException('A terminal work item cannot be assigned.');
        }

        $this->assignedUserId = $userId;
        $this->status = new WorkItemStatus(WorkItemStatus::ASSIGNED);
    }

    public function start(): void
    {
        if ($this->status->isTerminal()) {
            throw new DomainException('A terminal work item cannot be started.');
        }

        $this->status = new WorkItemStatus(WorkItemStatus::IN_PROGRESS);
    }

    public function markResponded(?DateTimeImmutable $at = null): void
    {
        $this->firstResponseAt ??= $at ?? new DateTimeImmutable();
    }

    public function linkCase(int $caseId): void
    {
        $this->caseId = $caseId;
        $this->status = new WorkItemStatus(WorkItemStatus::CASE_CREATED);
    }

    public function resolve(?DateTimeImmutable $at = null): void
    {
        $this->resolvedAt = $at ?? new DateTimeImmutable();
        $this->status = new WorkItemStatus(WorkItemStatus::RESOLVED);
    }

    public function close(?DateTimeImmutable $at = null): void
    {
        $this->closedAt = $at ?? new DateTimeImmutable();
        $this->status = new WorkItemStatus(WorkItemStatus::CLOSED);
    }

    public function archive(): void
    {
        $this->status = new WorkItemStatus(WorkItemStatus::ARCHIVED);
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'citizen_id' => $this->citizenId,
            'origin_type' => $this->originType,
            'origin_id' => $this->originId,
            'channel' => $this->channel,
            'title' => $this->title,
            'summary' => $this->summary,
            'status' => $this->status->value(),
            'priority' => $this->priority->value(),
            'assigned_user_id' => $this->assignedUserId,
            'case_id' => $this->caseId,
            'opened_at' => $this->openedAt?->format('Y-m-d H:i:s'),
            'first_response_at' => $this->firstResponseAt?->format('Y-m-d H:i:s'),
            'resolved_at' => $this->resolvedAt?->format('Y-m-d H:i:s'),
            'closed_at' => $this->closedAt?->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata,
        ];
    }
}
