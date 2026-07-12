<?php

namespace Modules\Operations\Application;

use Modules\Operations\Domain\Entities\WorkItem;
use Modules\Operations\Domain\Repositories\WorkItemRepositoryInterface;
use Modules\Operations\Domain\ValueObjects\WorkItemPriority;
use Modules\Operations\Domain\ValueObjects\WorkItemStatus;
use Modules\Operations\Infrastructure\Publishers\WorkItemEventPublisher;

final class CitizenOperationsService
{
    public function __construct(
        private readonly WorkItemRepositoryInterface $repository,
        private readonly WorkItemEventPublisher $publisher,
    ) {
    }

    public function create(CreateWorkItemData $data): array
    {
        $existing = $this->repository->findByOrigin($data->originType, $data->originId);
        if ($existing) {
            return $existing;
        }

        $workItem = new WorkItem(
            uuid: $this->uuidV4(),
            originType: $data->originType,
            originId: $data->originId,
            channel: $data->channel,
            title: $data->title,
            summary: $data->summary,
            status: new WorkItemStatus(WorkItemStatus::NEW),
            priority: new WorkItemPriority($data->priority),
            citizenId: $data->citizenId,
            metadata: $data->metadata,
        );

        $id = $this->repository->create($workItem);
        $created = $this->repository->find($id) ?? ['id' => $id] + $workItem->toArray();

        $this->publisher->created($id, [
            'work_item_id' => $id,
            'uuid' => $created['uuid'] ?? null,
            'citizen_id' => $data->citizenId,
            'origin_type' => $data->originType,
            'origin_id' => $data->originId,
            'channel' => $data->channel,
            'priority' => $data->priority,
            'status' => WorkItemStatus::NEW,
        ]);

        return $created;
    }

    public function assign(int $workItemId, int $userId): array
    {
        $this->repository->updateState($workItemId, [
            'assigned_user_id' => $userId,
            'status' => WorkItemStatus::ASSIGNED,
        ]);

        $this->publisher->changed($workItemId, 'assigned', [
            'work_item_id' => $workItemId,
            'assigned_user_id' => $userId,
            'status' => WorkItemStatus::ASSIGNED,
        ]);

        return $this->repository->find($workItemId) ?? [];
    }

    public function linkCase(int $workItemId, int $caseId): array
    {
        $this->repository->updateState($workItemId, [
            'case_id' => $caseId,
            'status' => WorkItemStatus::CASE_CREATED,
        ]);

        $this->publisher->changed($workItemId, 'case_created', [
            'work_item_id' => $workItemId,
            'case_id' => $caseId,
            'status' => WorkItemStatus::CASE_CREATED,
        ]);

        return $this->repository->find($workItemId) ?? [];
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
