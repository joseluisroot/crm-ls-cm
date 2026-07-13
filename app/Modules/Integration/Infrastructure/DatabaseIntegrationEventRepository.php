<?php

declare(strict_types=1);

namespace Modules\Integration\Infrastructure;

use CodeIgniter\Database\BaseConnection;
use Modules\Integration\Domain\IntegrationEvent;
use Modules\Integration\Domain\IntegrationEventRepositoryInterface;
use RuntimeException;

final class DatabaseIntegrationEventRepository implements IntegrationEventRepositoryInterface
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function create(IntegrationEvent $event): int
    {
        $builder = $this->connection()->table('integration_events');
        $builder->insert([
            'original_event_id' => $event->originalEventId,
            'replay_attempt' => $event->replayAttempt,
            'uuid' => $event->uuid,
            'source' => strtoupper($event->source),
            'event_type' => strtoupper($event->eventType),
            'event_version' => $event->eventVersion,
            'status' => $event->status,
            'external_event_id' => $event->externalEventId,
            'correlation_id' => $event->correlationId,
            'endpoint' => $event->endpoint,
            'request_ip' => $event->requestIp,
            'signature' => $event->signature,
            'payload_json' => $event->payloadJson,
            'headers_json' => $event->headersJson,
            'received_at' => $event->receivedAt,
            'replayed_at' => $event->originalEventId !== null ? $event->receivedAt : null,
            'created_at' => $event->receivedAt,
            'updated_at' => $event->receivedAt,
        ]);

        $id = (int) $this->connection()->insertID();
        if ($id <= 0) {
            throw new RuntimeException('Unable to persist integration event.');
        }

        return $id;
    }

    public function find(int $eventId): ?array
    {
        $row = $this->connection()->table('integration_events')->where('id', $eventId)->get()->getRowArray();
        return $row ?: null;
    }

    public function nextReplayAttempt(int $originalEventId): int
    {
        $row = $this->connection()->table('integration_events')
            ->selectMax('replay_attempt', 'max_attempt')
            ->groupStart()
                ->where('id', $originalEventId)
                ->orWhere('original_event_id', $originalEventId)
            ->groupEnd()
            ->get()
            ->getRowArray();

        return ((int) ($row['max_attempt'] ?? 0)) + 1;
    }

    public function markProcessed(int $eventId, int $processingTimeMs, array $trace = []): void
    {
        $this->updateStatus($eventId, IntegrationEvent::STATUS_PROCESSED, $processingTimeMs, null, $trace);
    }

    public function markFailed(int $eventId, string $errorMessage, int $processingTimeMs, array $trace = []): void
    {
        $this->updateStatus($eventId, IntegrationEvent::STATUS_FAILED, $processingTimeMs, $errorMessage, $trace);
    }

    private function updateStatus(int $eventId, string $status, int $processingTimeMs, ?string $errorMessage, array $trace): void
    {
        $now = date('Y-m-d H:i:s');
        $this->connection()->table('integration_events')->where('id', $eventId)->update([
            'status' => $status,
            'processing_time_ms' => max(0, $processingTimeMs),
            'processing_trace_json' => $trace === [] ? null : json_encode($trace, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'error_message' => $errorMessage,
            'processed_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}
