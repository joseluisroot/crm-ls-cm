<?php

namespace Modules\Workflow\Services;

use Modules\Core\Event\Contracts\EventSubscriberInterface;
use Modules\Core\Event\DTO\SystemEvent;
use Modules\Workflow\Models\WorkflowExecutionLogModel;
use Modules\Workflow\Models\WorkflowExecutionNodeModel;
use Modules\Workflow\Models\WorkflowExecutionPayloadModel;
use Modules\Workflow\Models\WorkflowExecutionSnapshotModel;
use Modules\Workflow\Models\WorkflowExecutionVariableModel;

class RuntimeInspectorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly WorkflowExecutionNodeModel $nodes = new WorkflowExecutionNodeModel(),
        private readonly WorkflowExecutionLogModel $logs = new WorkflowExecutionLogModel(),
        private readonly WorkflowExecutionSnapshotModel $snapshots = new WorkflowExecutionSnapshotModel(),
        private readonly WorkflowExecutionPayloadModel $payloads = new WorkflowExecutionPayloadModel(),
        private readonly WorkflowExecutionVariableModel $variables = new WorkflowExecutionVariableModel(),
    ) {
    }

    public function subscribedTo(): array
    {
        return [
            'workflow.started',
            'workflow.node.started',
            'workflow.node.completed',
            'workflow.node.failed',
            'workflow.completed',
            'workflow.failed',
        ];
    }

    public function handle(SystemEvent $event): void
    {
        $executionId = $this->executionId($event);

        if ($executionId <= 0) {
            log_message('warning', 'Runtime Inspector ignored {event}: execution id missing.', [
                'event' => $event->name,
            ]);
            return;
        }

        $nodeId = match ($event->name) {
            'workflow.node.started' => $this->startNode($executionId, $event),
            'workflow.node.completed' => $this->finishNode($executionId, $event, 'completed'),
            'workflow.node.failed' => $this->finishNode($executionId, $event, 'failed'),
            default => null,
        };

        $this->writeLog($executionId, $nodeId, $event);
        $this->writeSnapshot($executionId, $nodeId, $event);
        $this->writePayload($executionId, $nodeId, $event);
        $this->writeVariables($executionId, $nodeId, $event);
    }

    private function executionId(SystemEvent $event): int
    {
        return (int) ($event->payload['execution_id'] ?? $event->entityId ?? 0);
    }

    private function startNode(int $executionId, SystemEvent $event): ?int
    {
        $nodeKey = (string) ($event->payload['node_key'] ?? '');

        if ($nodeKey === '') {
            return null;
        }

        $attempt = (int) ($event->payload['attempt'] ?? 1);

        $id = $this->nodes->insert([
            'workflow_execution_id' => $executionId,
            'event_uuid' => $event->uuid,
            'node_key' => $nodeKey,
            'node_name' => $event->payload['node_name'] ?? null,
            'node_type' => $event->payload['node_type'] ?? null,
            'status' => 'running',
            'attempt' => max(1, $attempt),
            'started_at' => $event->publishedAt ?? date('Y-m-d H:i:s'),
        ], true);

        return $id === false ? null : (int) $id;
    }

    private function finishNode(int $executionId, SystemEvent $event, string $status): ?int
    {
        $nodeKey = (string) ($event->payload['node_key'] ?? '');

        if ($nodeKey === '') {
            return null;
        }

        $node = $this->nodes
            ->where('workflow_execution_id', $executionId)
            ->where('node_key', $nodeKey)
            ->where('status', 'running')
            ->orderBy('id', 'DESC')
            ->first();

        if (!$node) {
            $nodeId = $this->startNode($executionId, $event);
            $node = $nodeId ? $this->nodes->find($nodeId) : null;
        }

        if (!$node) {
            return null;
        }

        $this->nodes->update((int) $node['id'], [
            'status' => $status,
            'event_uuid' => $event->uuid,
            'finished_at' => $event->publishedAt ?? date('Y-m-d H:i:s'),
            'duration_ms' => isset($event->payload['duration_ms'])
                ? (int) $event->payload['duration_ms']
                : null,
            'error_class' => $event->payload['error_class'] ?? null,
            'error_message' => $event->payload['error_message'] ?? null,
        ]);

        return (int) $node['id'];
    }

    private function writeLog(int $executionId, ?int $nodeId, SystemEvent $event): void
    {
        $level = $event->name === 'workflow.failed' || $event->name === 'workflow.node.failed'
            ? 'error'
            : 'info';

        $this->logs->insert([
            'workflow_execution_id' => $executionId,
            'workflow_execution_node_id' => $nodeId,
            'event_uuid' => $event->uuid,
            'level' => $event->payload['level'] ?? $level,
            'message' => $event->payload['message'] ?? $event->name,
            'context_json' => json_encode($event->payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'created_at' => $event->publishedAt ?? date('Y-m-d H:i:s'),
        ]);
    }

    private function writeSnapshot(int $executionId, ?int $nodeId, SystemEvent $event): void
    {
        if (!array_key_exists('snapshot', $event->payload)) {
            return;
        }

        $this->snapshots->insert([
            'workflow_execution_id' => $executionId,
            'workflow_execution_node_id' => $nodeId,
            'event_uuid' => $event->uuid,
            'snapshot_type' => $event->name,
            'snapshot_json' => json_encode($event->payload['snapshot'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'created_at' => $event->publishedAt ?? date('Y-m-d H:i:s'),
        ]);
    }

    private function writePayload(int $executionId, ?int $nodeId, SystemEvent $event): void
    {
        if (!isset($event->payload['transport_payload']) || !is_array($event->payload['transport_payload'])) {
            return;
        }

        $transport = $event->payload['transport_payload'];

        $this->payloads->insert([
            'workflow_execution_id' => $executionId,
            'workflow_execution_node_id' => $nodeId,
            'event_uuid' => $event->uuid,
            'direction' => $transport['direction'] ?? 'out',
            'channel' => $transport['channel'] ?? null,
            'payload_json' => json_encode($transport['payload'] ?? null, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'headers_json' => json_encode($transport['headers'] ?? null, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'status_code' => isset($transport['status_code']) ? (int) $transport['status_code'] : null,
            'created_at' => $event->publishedAt ?? date('Y-m-d H:i:s'),
        ]);
    }

    private function writeVariables(int $executionId, ?int $nodeId, SystemEvent $event): void
    {
        $changes = $event->payload['variables'] ?? [];

        if (!is_array($changes)) {
            return;
        }

        foreach ($changes as $name => $change) {
            $old = is_array($change) && array_key_exists('old', $change) ? $change['old'] : null;
            $new = is_array($change) && array_key_exists('new', $change) ? $change['new'] : $change;

            $this->variables->insert([
                'workflow_execution_id' => $executionId,
                'workflow_execution_node_id' => $nodeId,
                'event_uuid' => $event->uuid,
                'variable_name' => (string) $name,
                'variable_type' => get_debug_type($new),
                'old_value_json' => json_encode($old, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'new_value_json' => json_encode($new, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'created_at' => $event->publishedAt ?? date('Y-m-d H:i:s'),
            ]);
        }
    }
}
