<?php

declare(strict_types=1);

namespace Modules\Integration\Domain;

interface IntegrationEventRepositoryInterface
{
    public function create(IntegrationEvent $event): int;

    public function find(int $eventId): ?array;

    public function nextReplayAttempt(int $originalEventId): int;

    public function markProcessed(int $eventId, int $processingTimeMs, array $trace = []): void;

    public function markFailed(int $eventId, string $errorMessage, int $processingTimeMs, array $trace = []): void;
}
