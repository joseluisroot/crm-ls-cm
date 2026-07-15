<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;

final class WorkItemActivityQueryService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function get(int $workItemId, int $limit = 20): array
    {
        $db = $this->db ?? db_connect();
        $limit = max(5, min($limit, 50));

        $timeEvents = $db->table('work_item_time_events e')
            ->select('e.id, e.event_type, e.occurred_at, e.metadata, u.name AS actor_name')
            ->join('admin_users u', 'u.id = e.user_id', 'left')
            ->where('e.work_item_id', $workItemId)
            ->orderBy('e.occurred_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $responses = $db->table('citizen_responses')
            ->select('id, channel, status, body, sent_at, created_at, error_message')
            ->where('work_item_id', $workItemId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $items = [];

        foreach ($timeEvents as $event) {
            $items[] = [
                'key' => 'event-' . $event['id'],
                'type' => strtoupper((string) $event['event_type']),
                'title' => $this->eventTitle((string) $event['event_type']),
                'description' => $this->eventDescription((string) $event['event_type'], (string) ($event['actor_name'] ?? 'Sistema')),
                'actor' => $event['actor_name'] ?: 'Sistema',
                'occurred_at' => $event['occurred_at'],
                'status' => 'INFO',
            ];
        }

        foreach ($responses as $response) {
            $status = strtoupper((string) ($response['status'] ?? ''));
            $items[] = [
                'key' => 'response-' . $response['id'],
                'type' => 'RESPONSE_' . ($status ?: 'RECORDED'),
                'title' => $status === 'SENT' ? 'Respuesta enviada' : ($status === 'FAILED' ? 'Error al enviar respuesta' : 'Respuesta registrada'),
                'description' => $this->responseDescription($response),
                'actor' => 'CIAC',
                'occurred_at' => $response['sent_at'] ?: $response['created_at'],
                'status' => $status === 'FAILED' ? 'ERROR' : ($status === 'SENT' ? 'SUCCESS' : 'INFO'),
            ];
        }

        usort($items, static fn (array $a, array $b): int => strcmp((string) $b['occurred_at'], (string) $a['occurred_at']));
        $items = array_slice($items, 0, $limit);

        return [
            'items' => $items,
            'total' => count($items),
            'workItemId' => $workItemId,
        ];
    }

    private function eventTitle(string $eventType): string
    {
        return match (strtoupper($eventType)) {
            'RECEIVED' => 'Atención recibida',
            'ASSIGNED' => 'Responsable asignado',
            'FIRST_DRAFT' => 'Primer borrador guardado',
            'FIRST_RESPONSE' => 'Primera respuesta confirmada',
            'RESOLVED' => 'Atención resuelta',
            default => ucfirst(strtolower(str_replace('_', ' ', $eventType))),
        };
    }

    private function eventDescription(string $eventType, string $actor): string
    {
        return match (strtoupper($eventType)) {
            'RECEIVED' => 'La interacción ingresó a la cola operativa.',
            'ASSIGNED' => 'La atención fue asignada a ' . $actor . '.',
            'FIRST_DRAFT' => $actor . ' guardó el primer borrador.',
            'FIRST_RESPONSE' => 'Se confirmó la primera respuesta al ciudadano.',
            'RESOLVED' => $actor . ' marcó la atención como resuelta.',
            default => 'Actividad operativa registrada por ' . $actor . '.',
        };
    }

    private function responseDescription(array $response): string
    {
        $channel = strtoupper((string) ($response['channel'] ?? 'CANAL'));
        $status = strtoupper((string) ($response['status'] ?? ''));

        if ($status === 'FAILED') {
            return 'No fue posible enviar la respuesta por ' . $channel . ': ' . trim((string) ($response['error_message'] ?? 'error no especificado'));
        }

        $body = trim((string) ($response['body'] ?? ''));
        if (mb_strlen($body) > 120) {
            $body = mb_substr($body, 0, 117) . '...';
        }

        return ($status === 'SENT' ? 'Enviada por ' : 'Registrada para ') . $channel . ($body !== '' ? ': ' . $body : '.');
    }
}
