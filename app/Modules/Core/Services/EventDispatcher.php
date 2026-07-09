<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\CoreEventInterface;

class EventDispatcher
{
    private array $listeners = [];

    public function listen(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(CoreEventInterface $event): array
    {
        $results = [];

        foreach ($this->listeners[$event->name()] ?? [] as $listener) {
            $results[] = $listener($event);
        }

        return $results;
    }
}