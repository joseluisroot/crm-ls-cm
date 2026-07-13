<?php

declare(strict_types=1);

namespace Modules\Integration\Application;

use CodeIgniter\Database\BaseConnection;

final class IntegrationEventQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function search(array $filters = [], int $limit = 100): array
    {
        $limit = max(1, min($limit, 250));
        $builder = $this->connection()->table('integration_events');

        foreach (['status', 'source', 'event_type'] as $field) {
            $value = strtoupper(trim((string) ($filters[$field] ?? '')));
            if ($value !== '') {
                $builder->where($field, $value);
            }
        }

        $correlationId = trim((string) ($filters['correlation_id'] ?? ''));
        if ($correlationId !== '') {
            $builder->like('correlation_id', $correlationId);
        }

        $externalEventId = trim((string) ($filters['external_event_id'] ?? ''));
        if ($externalEventId !== '') {
            $builder->like('external_event_id', $externalEventId);
        }

        return $builder
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function metrics(): array
    {
        $row = $this->connection()->query(
            "SELECT COUNT(*) total_events,
                    SUM(status = 'PROCESSED') processed_events,
                    SUM(status = 'FAILED') failed_events,
                    SUM(replay_attempt > 0) replay_events,
                    COALESCE(ROUND(AVG(processing_time_ms)), 0) average_processing_ms
             FROM integration_events"
        )->getRowArray() ?: [];

        return [
            'total_events' => (int) ($row['total_events'] ?? 0),
            'processed_events' => (int) ($row['processed_events'] ?? 0),
            'failed_events' => (int) ($row['failed_events'] ?? 0),
            'replay_events' => (int) ($row['replay_events'] ?? 0),
            'average_processing_ms' => (int) ($row['average_processing_ms'] ?? 0),
        ];
    }

    public function detail(int $eventId): ?array
    {
        $event = $this->connection()
            ->table('integration_events')
            ->where('id', $eventId)
            ->get()
            ->getRowArray();

        if (! $event) {
            return null;
        }

        $rootId = (int) ($event['original_event_id'] ?: $event['id']);
        $lineage = $this->connection()
            ->table('integration_events')
            ->groupStart()
                ->where('id', $rootId)
                ->orWhere('original_event_id', $rootId)
            ->groupEnd()
            ->orderBy('replay_attempt', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return [
            'event' => $this->decodeJsonFields($event),
            'lineage' => array_map(fn (array $row): array => $this->decodeJsonFields($row), $lineage),
        ];
    }

    public static function decode(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function decodeJsonFields(array $event): array
    {
        $event['payload'] = self::decode($event['payload_json'] ?? null);
        $event['headers'] = self::decode($event['headers_json'] ?? null);
        $event['processing_trace'] = self::decode($event['processing_trace_json'] ?? null);

        return $event;
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}
