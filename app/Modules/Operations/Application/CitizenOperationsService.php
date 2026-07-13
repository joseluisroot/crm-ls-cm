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
            if ($data->citizenId !== null && empty($existing['citizen_id'])) {
                return $this->linkCitizen((int) $existing['id'], $data->citizenId);
            }

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

    public function linkCitizen(int $workItemId, int $citizenId): array
    {
        $this->repository->updateState($workItemId, ['citizen_id' => $citizenId]);

        $this->publisher->changed($workItemId, 'citizen_linked', [
            'work_item_id' => $workItemId,
            'citizen_id' => $citizenId,
        ]);

        return $this->repository->find($workItemId) ?? [];
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

    public function changeStatus(int $workItemId, string $status): array
    {
        $status = (new WorkItemStatus(strtoupper($status)))->value();
        $changes = ['status' => $status];
        $now = date('Y-m-d H:i:s');

        if ($status === WorkItemStatus::IN_PROGRESS) {
            $changes['opened_at'] = $now;
        } elseif ($status === WorkItemStatus::RESOLVED) {
            $changes['resolved_at'] = $now;
        } elseif ($status === WorkItemStatus::CLOSED) {
            $changes['closed_at'] = $now;
        }

        $this->repository->updateState($workItemId, $changes);
        $this->publisher->changed($workItemId, 'status_changed', [
            'work_item_id' => $workItemId,
            'status' => $status,
        ]);

        return $this->repository->find($workItemId) ?? [];
    }

    public function changePriority(int $workItemId, string $priority): array
    {
        $priority = (new WorkItemPriority(strtoupper($priority)))->value();
        $this->repository->updateState($workItemId, ['priority' => $priority]);
        $this->publisher->changed($workItemId, 'priority_changed', [
            'work_item_id' => $workItemId,
            'priority' => $priority,
        ]);

        return $this->repository->find($workItemId) ?? [];
    }

    public function markResponded(int $workItemId): array
    {
        $this->repository->updateState($workItemId, [
            'first_response_at' => date('Y-m-d H:i:s'),
            'status' => WorkItemStatus::WAITING_CITIZEN,
        ]);
        $this->publisher->changed($workItemId, 'responded', [
            'work_item_id' => $workItemId,
            'status' => WorkItemStatus::WAITING_CITIZEN,
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
