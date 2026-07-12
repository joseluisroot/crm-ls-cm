<?php

namespace Modules\Operations\Infrastructure\Publishers;

final class WorkItemEventPublisher
{
    public function created(int $workItemId, array $payload): void
    {
        service('eventEngine')->emit(
            name: 'operations.work_item.created',
            module: 'operations',
            payload: $payload,
            entityType: 'work_item',
            entityId: $workItemId,
        );
    }

    public function changed(int $workItemId, string $eventName, array $payload): void
    {
        service('eventEngine')->emit(
            name: 'operations.work_item.' . $eventName,
            module: 'operations',
            payload: $payload,
            entityType: 'work_item',
            entityId: $workItemId,
        );
    }
}
