<?php

namespace Modules\Core\Event\Contracts;

use Modules\Core\Event\DTO\SystemEvent;

interface EventSubscriberInterface
{
    /**
     * @return list<string>
     */
    public function subscribedTo(): array;

    public function handle(SystemEvent $event): void;
}
