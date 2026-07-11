<?php

namespace Modules\Core\Event\Services;

use Modules\Core\Event\DTO\SystemEvent;
use Throwable;

class EventDispatcher
{
    public function __construct(private readonly EventRegistry $registry)
    {
    }

    public function dispatch(SystemEvent $event): void
    {
        foreach ($this->registry->subscribersFor($event->name) as $subscriber) {
            try {
                $subscriber->handle($event);
            } catch (Throwable $exception) {
                log_message('error', 'Event subscriber failed for {event}: {message}', [
                    'event' => $event->name,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
