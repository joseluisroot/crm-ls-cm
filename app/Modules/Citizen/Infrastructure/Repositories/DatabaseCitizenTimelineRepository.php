<?php

declare(strict_types=1);

namespace Modules\Citizen\Infrastructure\Repositories;

use CodeIgniter\Database\BaseConnection;
use Modules\Citizen\Application\Contracts\CitizenTimelineRepositoryInterface;
use Modules\Citizen\Application\DTO\CitizenTimelineItemDTO;

final class DatabaseCitizenTimelineRepository implements CitizenTimelineRepositoryInterface
{
    public function __construct(
        private readonly ?BaseConnection $db = null,
    ) {
    }

    public function timeline(int $citizenId): array
    {
        $workItems = $this->workItems($citizenId);
        $workItemIds = array_map(
            static fn (array $row): int => (int) $row['id'],
            $workItems,
        );

        $items = array_map(
            fn (array $row): CitizenTimelineItemDTO => $this->mapWorkItem($row),
            $workItems,
        );

        foreach ($this->events($citizenId, $workItemIds) as $event) {
            $items[] = $this->mapEvent($event);
        }

        usort(
            $items,
            static fn (CitizenTimelineItemDTO $left, CitizenTimelineItemDTO $right): int =>
                strcmp($right->occurredAt, $left->occurredAt),
        );

        return $items;
    }

    public function metrics(int $citizenId): array
    {
        $connection = $this->connection();

        return [
            'total_work_items' => $connection->table('work_items')
                ->where('citizen_id', $citizenId)
                ->countAllResults(),
            'open_work_items' => $connection->table('work_items wi')
                ->join('work_item_statuses wis', 'wis.id = wi.status_id')
                ->where('wi.citizen_id', $citizenId)
                ->whereNotIn('wis.code', ['RESOLVED', 'CLOSED', 'ARCHIVED'])
                ->countAllResults(),
            'total_cases' => $connection->table('cases')
                ->where('citizen_id', $citizenId)
                ->countAllResults(),
            'total_identities' => $connection->table('citizen_social_identities')
                ->where('citizen_id', $citizenId)
                ->where('is_active', 1)
                ->countAllResults(),
        ];
    }

    private function workItems(int $citizenId): array
    {
        return $this->connection()->table('work_items wi')
            ->select(
                'wi.id, wi.uuid, wi.title, wi.summary, wi.origin_id, wi.created_at, '
                . 'wis.code AS status_code, wis.name AS status_name, '
                . 'wip.code AS priority_code, wip.name AS priority_name, '
                . 'wic.code AS channel_code'
            )
            ->join('work_item_statuses wis', 'wis.id = wi.status_id')
            ->join('work_item_priorities wip', 'wip.id = wi.priority_id')
            ->join('work_item_channels wic', 'wic.id = wi.channel_id')
            ->where('wi.citizen_id', $citizenId)
            ->orderBy('wi.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function events(int $citizenId, array $workItemIds): array
    {
        $connection = $this->connection();

        $citizenEvents = $connection->table('system_events')
            ->where('entity_type', 'citizen')
            ->where('entity_id', $citizenId)
            ->get()
            ->getResultArray();

        $workItemEvents = [];
        if ($workItemIds !== []) {
            $workItemEvents = $connection->table('system_events')
                ->where('entity_type', 'work_item')
                ->whereIn('entity_id', $workItemIds)
                ->get()
                ->getResultArray();
        }

        return array_merge($citizenEvents, $workItemEvents);
    }

    private function mapWorkItem(array $row): CitizenTimelineItemDTO
    {
        return new CitizenTimelineItemDTO(
            id: 'work_item:' . $row['id'],
            type: 'WORK_ITEM',
            title: 'Work Item creado',
            description: $row['summary'] ?: $row['title'],
            occurredAt: (string) $row['created_at'],
            metadata: [
                'work_item_id' => (int) $row['id'],
                'uuid' => $row['uuid'],
                'status' => $row['status_code'],
                'status_name' => $row['status_name'],
                'priority' => $row['priority_code'],
                'priority_name' => $row['priority_name'],
                'channel' => $row['channel_code'],
                'origin_id' => $row['origin_id'],
                'url' => site_url('admin/operations/' . $row['id']),
            ],
        );
    }

    private function mapEvent(array $row): CitizenTimelineItemDTO
    {
        $payload = json_decode((string) ($row['payload_json'] ?? '{}'), true);
        if (! is_array($payload)) {
            $payload = [];
        }

        return new CitizenTimelineItemDTO(
            id: 'event:' . $row['id'],
            type: 'SYSTEM_EVENT',
            title: $this->eventTitle((string) $row['event_name']),
            description: $this->eventDescription((string) $row['event_name'], $payload),
            occurredAt: (string) $row['published_at'],
            metadata: [
                'event_id' => (int) $row['id'],
                'event_name' => $row['event_name'],
                'entity_type' => $row['entity_type'],
                'entity_id' => $row['entity_id'],
                'payload' => $payload,
            ],
        );
    }

    private function eventTitle(string $eventName): string
    {
        return match ($eventName) {
            'citizen.identity.created' => 'Identidad ciudadana creada',
            'citizen.identity.resolved' => 'Identidad ciudadana reconocida',
            'operations.work_item.created' => 'Trabajo operativo creado',
            'operations.work_item.citizen_linked' => 'Work Item vinculado al ciudadano',
            'operations.work_item.assigned' => 'Work Item asignado',
            'operations.work_item.status_changed' => 'Estado del Work Item actualizado',
            'operations.work_item.priority_changed' => 'Prioridad del Work Item actualizada',
            'operations.work_item.responded' => 'Primera respuesta registrada',
            'operations.work_item.case_created' => 'Caso creado desde Work Item',
            default => str_replace(['.', '_'], ' ', ucfirst($eventName)),
        };
    }

    private function eventDescription(string $eventName, array $payload): ?string
    {
        return match ($eventName) {
            'operations.work_item.status_changed' => isset($payload['status'])
                ? 'Nuevo estado: ' . $payload['status']
                : null,
            'operations.work_item.priority_changed' => isset($payload['priority'])
                ? 'Nueva prioridad: ' . $payload['priority']
                : null,
            'operations.work_item.assigned' => isset($payload['assigned_user_id'])
                ? 'Responsable asignado #' . $payload['assigned_user_id']
                : null,
            'citizen.identity.created', 'citizen.identity.resolved' => isset($payload['channel'])
                ? 'Canal: ' . $payload['channel']
                : null,
            default => null,
        };
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}
