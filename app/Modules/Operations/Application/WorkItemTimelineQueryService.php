<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;

final class WorkItemTimelineQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    /** @return array{events: array<int, array<string, mixed>>, metrics: array<string, mixed>} */
    public function get(int $workItemId): array
    {
        $db = $this->connection();
        $item = $db->table('work_items')
            ->select('id, opened_at, created_at, assigned_user_id')
            ->where('id', $workItemId)
            ->get()
            ->getRowArray();

        if (! $item) {
            return ['events' => [], 'metrics' => []];
        }

        $timeEvents = $db->table('work_item_time_events e')
            ->select('e.event_type, e.occurred_at, e.metadata, e.user_id, u.name AS user_name')
            ->join('admin_users u', 'u.id = e.user_id', 'left')
            ->where('e.work_item_id', $workItemId)
            ->orderBy('e.occurred_at', 'ASC')
            ->orderBy('e.id', 'ASC')
            ->get()
            ->getResultArray();

        $events = [];
        foreach ($timeEvents as $event) {
            $type = strtoupper((string) $event['event_type']);
            $metadata = json_decode((string) ($event['metadata'] ?? '{}'), true);
            $events[] = [
                'type' => $type,
                'label' => $this->label($type),
                'description' => $this->description($type, $event['user_name'] ?? null),
                'occurred_at' => $event['occurred_at'],
                'user_name' => $event['user_name'] ?? null,
                'metadata' => is_array($metadata) ? $metadata : [],
                'tone' => $this->tone($type),
                'source' => 'sla',
            ];
        }

        if ($events === []) {
            $receivedAt = $item['opened_at'] ?: $item['created_at'];
            if ($receivedAt) {
                $events[] = [
                    'type' => 'RECEIVED',
                    'label' => 'Atención recibida',
                    'description' => 'La interacción ingresó a CIAC.',
                    'occurred_at' => $receivedAt,
                    'user_name' => null,
                    'metadata' => [],
                    'tone' => 'blue',
                    'source' => 'work_item',
                ];
            }
        }

        $systemEvents = $db->table('system_events')
            ->select('event_name, published_at, payload_json')
            ->where('entity_type', 'work_item')
            ->where('entity_id', $workItemId)
            ->orderBy('published_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($systemEvents as $event) {
            $payload = json_decode((string) ($event['payload_json'] ?? '{}'), true);
            $events[] = [
                'type' => 'ACTIVITY',
                'label' => $this->humanize((string) $event['event_name']),
                'description' => 'Actividad registrada en el ciclo operativo.',
                'occurred_at' => $event['published_at'],
                'user_name' => null,
                'metadata' => is_array($payload) ? $payload : [],
                'tone' => 'slate',
                'source' => 'system',
            ];
        }

        usort($events, static fn (array $a, array $b): int => strcmp((string) $a['occurred_at'], (string) $b['occurred_at']));

        return [
            'events' => $events,
            'metrics' => $this->metrics($item, $timeEvents),
        ];
    }

    /** @param array<string, mixed> $item @param array<int, array<string, mixed>> $events */
    private function metrics(array $item, array $events): array
    {
        $received = $this->firstDate($events, 'RECEIVED') ?: ($item['opened_at'] ?: $item['created_at']);
        $assigned = $this->firstDate($events, 'ASSIGNED');
        $draft = $this->firstDate($events, 'FIRST_DRAFT');
        $response = $this->firstDate($events, 'FIRST_RESPONSE');
        $resolved = $this->firstDate($events, 'RESOLVED');
        $end = $resolved ?: date('Y-m-d H:i:s');

        return [
            'received_at' => $received,
            'assigned_at' => $assigned,
            'first_draft_at' => $draft,
            'first_response_at' => $response,
            'resolved_at' => $resolved,
            'total_seconds' => $this->diff($received, $end),
            'assignment_seconds' => $this->diff($received, $assigned),
            'first_draft_seconds' => $this->diff($received, $draft),
            'first_response_seconds' => $this->diff($received, $response),
            'current_owner_seconds' => $this->diff($this->lastDate($events, 'ASSIGNED') ?: $received, $end),
            'is_resolved' => $resolved !== null,
        ];
    }

    /** @param array<int, array<string, mixed>> $events */
    private function firstDate(array $events, string $type): ?string
    {
        foreach ($events as $event) {
            if (strtoupper((string) $event['event_type']) === $type) return (string) $event['occurred_at'];
        }
        return null;
    }

    /** @param array<int, array<string, mixed>> $events */
    private function lastDate(array $events, string $type): ?string
    {
        $date = null;
        foreach ($events as $event) {
            if (strtoupper((string) $event['event_type']) === $type) $date = (string) $event['occurred_at'];
        }
        return $date;
    }

    private function diff(?string $from, ?string $to): ?int
    {
        if (! $from || ! $to) return null;
        try {
            return max(0, (new DateTimeImmutable($to))->getTimestamp() - (new DateTimeImmutable($from))->getTimestamp());
        } catch (\Throwable) {
            return null;
        }
    }

    private function label(string $type): string
    {
        return match ($type) {
            'RECEIVED' => 'Atención recibida',
            'ASSIGNED' => 'Responsable asignado',
            'FIRST_DRAFT' => 'Primer borrador guardado',
            'FIRST_RESPONSE' => 'Primera respuesta enviada',
            'RESOLVED' => 'Atención resuelta',
            default => $this->humanize($type),
        };
    }

    private function description(string $type, ?string $userName): string
    {
        return match ($type) {
            'RECEIVED' => 'La interacción ingresó a CIAC.',
            'ASSIGNED' => $userName ? 'Asignada a ' . $userName . '.' : 'Se asignó un responsable.',
            'FIRST_DRAFT' => $userName ? $userName . ' guardó el primer borrador.' : 'Se guardó el primer borrador.',
            'FIRST_RESPONSE' => $userName ? $userName . ' envió la primera respuesta.' : 'Se envió la primera respuesta.',
            'RESOLVED' => $userName ? $userName . ' resolvió la atención.' : 'La atención fue resuelta.',
            default => 'Actividad registrada.',
        };
    }

    private function tone(string $type): string
    {
        return match ($type) {
            'RECEIVED' => 'blue',
            'ASSIGNED' => 'violet',
            'FIRST_DRAFT' => 'amber',
            'FIRST_RESPONSE', 'RESOLVED' => 'green',
            default => 'slate',
        };
    }

    private function humanize(string $value): string
    {
        return ucfirst(strtolower(str_replace(['_', '.'], ' ', $value)));
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}
