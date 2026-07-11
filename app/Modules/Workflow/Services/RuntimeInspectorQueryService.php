<?php

namespace Modules\Workflow\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;

class RuntimeInspectorQueryService
{
    public function __construct(
        private readonly BaseConnection $db,
    ) {
    }

    public function execution(int $executionId): ?array
    {
        $execution = $this->db
            ->table('workflow_executions e')
            ->select('e.*, w.name AS workflow_name, w.slug AS workflow_slug, v.version_number')
            ->join('workflows w', 'w.id = e.workflow_id', 'left')
            ->join('workflow_versions v', 'v.id = e.workflow_version_id', 'left')
            ->where('e.id', $executionId)
            ->get()
            ->getRowArray();

        if (!$execution) {
            return null;
        }

        $execution['metadata'] = $this->decode($execution['metadata'] ?? null);
        $execution['timeline'] = $this->timeline($executionId);
        $execution['summary'] = $this->summary($executionId);

        return $execution;
    }

    public function timeline(int $executionId): array
    {
        $nodes = $this->db
            ->table('workflow_execution_nodes')
            ->where('workflow_execution_id', $executionId)
            ->orderBy('started_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($nodes as &$node) {
            $nodeId = (int) $node['id'];
            $node['logs'] = $this->logs($executionId, $nodeId);
            $node['variables'] = $this->variables($executionId, $nodeId);
            $node['payloads'] = $this->payloads($executionId, $nodeId);
            $node['snapshots'] = $this->snapshots($executionId, $nodeId);
        }
        unset($node);

        return $nodes;
    }

    public function summary(int $executionId): array
    {
        $row = $this->db
            ->table('workflow_execution_nodes')
            ->select('COUNT(*) AS total_nodes')
            ->select("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_nodes", false)
            ->select("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_nodes", false)
            ->select('COALESCE(SUM(duration_ms), 0) AS total_duration_ms', false)
            ->select('COALESCE(MAX(duration_ms), 0) AS slowest_node_ms', false)
            ->where('workflow_execution_id', $executionId)
            ->get()
            ->getRowArray() ?? [];

        return [
            'total_nodes'       => (int) ($row['total_nodes'] ?? 0),
            'completed_nodes'   => (int) ($row['completed_nodes'] ?? 0),
            'failed_nodes'      => (int) ($row['failed_nodes'] ?? 0),
            'total_duration_ms' => (int) ($row['total_duration_ms'] ?? 0),
            'slowest_node_ms'   => (int) ($row['slowest_node_ms'] ?? 0),
        ];
    }

    public function recent(int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        return $this->db
            ->table('workflow_executions e')
            ->select('e.*, w.name AS workflow_name, w.slug AS workflow_slug, v.version_number')
            ->join('workflows w', 'w.id = e.workflow_id', 'left')
            ->join('workflow_versions v', 'v.id = e.workflow_version_id', 'left')
            ->orderBy('e.started_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function logs(int $executionId, ?int $nodeId = null): array
    {
        $builder = $this->db
            ->table('workflow_execution_logs')
            ->where('workflow_execution_id', $executionId)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC');

        if ($nodeId !== null) {
            $builder->where('workflow_execution_node_id', $nodeId);
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['context'] = $this->decode($row['context_json'] ?? null);
            unset($row['context_json']);
        }
        unset($row);

        return $rows;
    }

    public function variables(int $executionId, ?int $nodeId = null): array
    {
        $builder = $this->db
            ->table('workflow_execution_variables')
            ->where('workflow_execution_id', $executionId)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC');

        if ($nodeId !== null) {
            $builder->where('workflow_execution_node_id', $nodeId);
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['old_value'] = $this->decode($row['old_value_json'] ?? null);
            $row['new_value'] = $this->decode($row['new_value_json'] ?? null);
            unset($row['old_value_json'], $row['new_value_json']);
        }
        unset($row);

        return $rows;
    }

    public function payloads(int $executionId, ?int $nodeId = null): array
    {
        $builder = $this->db
            ->table('workflow_execution_payloads')
            ->where('workflow_execution_id', $executionId)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC');

        if ($nodeId !== null) {
            $builder->where('workflow_execution_node_id', $nodeId);
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['payload'] = $this->decode($row['payload_json'] ?? null);
            $row['headers'] = $this->decode($row['headers_json'] ?? null);
            unset($row['payload_json'], $row['headers_json']);
        }
        unset($row);

        return $rows;
    }

    public function snapshots(int $executionId, ?int $nodeId = null): array
    {
        $builder = $this->db
            ->table('workflow_execution_snapshots')
            ->where('workflow_execution_id', $executionId)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC');

        if ($nodeId !== null) {
            $builder->where('workflow_execution_node_id', $nodeId);
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['snapshot'] = $this->decode($row['snapshot_json'] ?? null);
            unset($row['snapshot_json']);
        }
        unset($row);

        return $rows;
    }

    private function decode(?string $json): mixed
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $json;
    }
}
