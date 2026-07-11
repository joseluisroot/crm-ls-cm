<?php

namespace Modules\Core\Event\Services;

use Modules\Core\Event\Contracts\EventSubscriberInterface;

class EventRegistry
{
    /** @var array<string, list<EventSubscriberInterface>> */
    private array $subscribers = [];

    public function subscribe(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber->subscribedTo() as $eventName) {
            $this->subscribers[$eventName][] = $subscriber;
        }
    }

    /** @return list<EventSubscriberInterface> */
    public function subscribersFor(string $eventName): array
    {
        return array_merge(
            $this->subscribers[$eventName] ?? [],
            $this->subscribers['*'] ?? [],
        );
    }
}
