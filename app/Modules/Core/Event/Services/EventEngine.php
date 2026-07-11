<?php

namespace Modules\Core\Event\Services;

use Modules\Core\Event\DTO\SystemEvent;
use Modules\Core\Event\Models\SystemEventModel;
use RuntimeException;

class EventEngine
{
    public function __construct(
        private readonly EventDispatcher $dispatcher,
        private readonly SystemEventModel $model,
    ) {
    }

    public function publish(SystemEvent $event): SystemEvent
    {
        $inserted = $this->model->insert($event->toArray());

        if ($inserted === false) {
            throw new RuntimeException('The system event could not be persisted.');
        }

        $this->dispatcher->dispatch($event);

        return $event;
    }

    public function emit(
        string $name,
        string $module,
        array $payload = [],
        array $metadata = [],
        ?string $entityType = null,
        int|string|null $entityId = null,
        ?string $correlationId = null,
        ?string $causationId = null,
        int $version = 1,
        ?int $publishedBy = null,
    ): SystemEvent {
        return $this->publish(SystemEvent::create(
            name: $name,
            module: $module,
            payload: $payload,
            metadata: $metadata,
            entityType: $entityType,
            entityId: $entityId,
            correlationId: $correlationId,
            causationId: $causationId,
            version: $version,
            publishedBy: $publishedBy,
        ));
    }
}
