<?php

namespace Modules\Operations\Infrastructure\Repositories;

use CodeIgniter\Database\BaseConnection;
use Modules\Operations\Domain\Entities\WorkItem;
use Modules\Operations\Domain\Repositories\WorkItemRepositoryInterface;
use Modules\Operations\Infrastructure\Models\WorkItemModel;
use RuntimeException;

final class DatabaseWorkItemRepository implements WorkItemRepositoryInterface
{
    public function __construct(
        private readonly ?BaseConnection $db = null,
        private readonly ?WorkItemModel $model = null,
    ) {
    }

    public function create(WorkItem $workItem): int
    {
        $data = $workItem->toArray();
        $data['origin_type_id'] = $this->catalogId('work_item_origin_types', $data['origin_type']);
        $data['channel_id'] = $this->catalogId('work_item_channels', $data['channel']);
        $data['status_id'] = $this->catalogId('work_item_statuses', $data['status']);
        $data['priority_id'] = $this->catalogId('work_item_priorities', $data['priority']);
        $data['metadata_json'] = $this->json($data['metadata'] ?? []);

        unset($data['origin_type'], $data['channel'], $data['status'], $data['priority'], $data['metadata']);

        $id = $this->workItemModel()->insert($data, true);

        if (! $id) {
            throw new RuntimeException('Unable to persist work item.');
        }

        return (int) $id;
    }

    public function findByOrigin(string $originType, string $originId): ?array
    {
        return $this->connection()->table('work_items wi')
            ->select('wi.*, ot.code AS origin_type, ch.code AS channel, st.code AS status, pr.code AS priority')
            ->join('work_item_origin_types ot', 'ot.id = wi.origin_type_id')
            ->join('work_item_channels ch', 'ch.id = wi.channel_id')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->join('work_item_priorities pr', 'pr.id = wi.priority_id')
            ->where('ot.code', $originType)
            ->where('wi.origin_id', $originId)
            ->get()
            ->getRowArray() ?: null;
    }

    public function find(int $id): ?array
    {
        return $this->connection()->table('work_items wi')
            ->select('wi.*, ot.code AS origin_type, ch.code AS channel, st.code AS status, pr.code AS priority')
            ->join('work_item_origin_types ot', 'ot.id = wi.origin_type_id')
            ->join('work_item_channels ch', 'ch.id = wi.channel_id')
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->join('work_item_priorities pr', 'pr.id = wi.priority_id')
            ->where('wi.id', $id)
            ->get()
            ->getRowArray() ?: null;
    }

    public function updateState(int $id, array $changes): void
    {
        foreach (['status', 'priority'] as $catalog) {
            if (! array_key_exists($catalog, $changes)) {
                continue;
            }

            $table = $catalog === 'status' ? 'work_item_statuses' : 'work_item_priorities';
            $changes[$catalog . '_id'] = $this->catalogId($table, (string) $changes[$catalog]);
            unset($changes[$catalog]);
        }

        if (isset($changes['metadata']) && is_array($changes['metadata'])) {
            $changes['metadata_json'] = $this->json($changes['metadata']);
            unset($changes['metadata']);
        }

        $this->workItemModel()->update($id, $changes);
    }

    private function catalogId(string $table, string $code): int
    {
        $row = $this->connection()->table($table)
            ->select('id')
            ->where('code', $code)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        if (! $row) {
            throw new RuntimeException(sprintf('Catalog value %s was not found in %s.', $code, $table));
        }

        return (int) $row['id'];
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }

    private function workItemModel(): WorkItemModel
    {
        return $this->model ?? new WorkItemModel();
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
