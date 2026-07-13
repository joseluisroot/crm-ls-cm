<?php

declare(strict_types=1);

namespace Modules\Integration\Application;

use Modules\Integration\Domain\IntegrationEvent;
use Modules\Integration\Domain\IntegrationEventRepositoryInterface;

final class IntegrationEventCaptureService
{
    public function __construct(private readonly IntegrationEventRepositoryInterface $repository)
    {
    }

    public function capture(
        string $source,
        string $eventType,
        array $payload,
        array $headers = [],
        ?string $externalEventId = null,
        ?string $endpoint = null,
        ?string $requestIp = null,
        ?string $signature = null,
        int $eventVersion = 1,
    ): array {
        $uuid = $this->uuidV4();
        $correlationId = $this->uuidV4();
        $receivedAt = date('Y-m-d H:i:s');

        $event = new IntegrationEvent(
            uuid: $uuid,
            source: strtoupper(trim($source)),
            eventType: strtoupper(trim($eventType ?: 'UNKNOWN')),
            eventVersion: $eventVersion,
            status: IntegrationEvent::STATUS_RECEIVED,
            correlationId: $correlationId,
            payloadJson: $this->encode($payload),
            headersJson: $headers === [] ? null : $this->encode($headers),
            externalEventId: $externalEventId,
            endpoint: $endpoint,
            requestIp: $requestIp,
            signature: $signature,
            receivedAt: $receivedAt,
        );

        return [
            'id' => $this->repository->create($event),
            'uuid' => $uuid,
            'correlation_id' => $correlationId,
            'received_at' => $receivedAt,
        ];
    }

    public function markProcessed(int $eventId, float $startedAt, array $trace = []): void
    {
        $this->repository->markProcessed($eventId, $this->elapsedMs($startedAt), $trace);
    }

    public function markFailed(int $eventId, float $startedAt, string $errorMessage, array $trace = []): void
    {
        $this->repository->markFailed($eventId, $errorMessage, $this->elapsedMs($startedAt), $trace);
    }

    private function elapsedMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    private function encode(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20, 12));
    }
}
