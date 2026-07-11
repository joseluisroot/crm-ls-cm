<?php

namespace Modules\Workflow\Services;

use Modules\Core\Event\DTO\SystemEvent;
use Modules\Core\Event\Services\EventEngine;
use Throwable;

class WorkflowRuntimeEventPublisher
{
    public function __construct(
        private readonly EventEngine $events,
    ) {
    }

    public function workflowStarted(array $execution, array $workflow = []): SystemEvent
    {
        return $this->emit('workflow.started', $execution, [
            'workflow_id'         => (int) $execution['workflow_id'],
            'workflow_version_id' => (int) $execution['workflow_version_id'],
            'conversation_id'     => (int) $execution['conversation_id'],
            'node_key'            => $execution['current_node_key'],
            'workflow_name'       => $workflow['name'] ?? null,
            'started_at'          => $execution['started_at'] ?? date('Y-m-d H:i:s'),
        ]);
    }

    public function nodeStarted(array $execution, array $node, int $attempt = 1): SystemEvent
    {
        return $this->emit('workflow.node.started', $execution, [
            'node_key'   => $node['node_key'],
            'node_name'  => $node['name'] ?? null,
            'node_type'  => $node['node_type'] ?? null,
            'attempt'    => $attempt,
            'started_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function nodeCompleted(
        array $execution,
        array $node,
        float $startedAt,
        array $extra = []
    ): SystemEvent {
        return $this->emit('workflow.node.completed', $execution, array_merge([
            'node_key'    => $node['node_key'],
            'node_name'   => $node['name'] ?? null,
            'node_type'   => $node['node_type'] ?? null,
            'finished_at' => date('Y-m-d H:i:s'),
            'duration_ms' => $this->durationMs($startedAt),
        ], $extra));
    }

    public function nodeFailed(
        array $execution,
        array $node,
        float $startedAt,
        Throwable $error,
        int $attempt = 1
    ): SystemEvent {
        return $this->emit('workflow.node.failed', $execution, [
            'node_key'     => $node['node_key'],
            'node_name'    => $node['name'] ?? null,
            'node_type'    => $node['node_type'] ?? null,
            'attempt'      => $attempt,
            'finished_at'  => date('Y-m-d H:i:s'),
            'duration_ms'  => $this->durationMs($startedAt),
            'error_class'  => $error::class,
            'error_message'=> $error->getMessage(),
        ]);
    }

    public function workflowCompleted(array $execution, array $extra = []): SystemEvent
    {
        return $this->emit('workflow.completed', $execution, array_merge([
            'completed_at' => date('Y-m-d H:i:s'),
            'node_key'     => $execution['current_node_key'] ?? null,
        ], $extra));
    }

    public function workflowFailed(array $execution, Throwable $error): SystemEvent
    {
        return $this->emit('workflow.failed', $execution, [
            'failed_at'     => date('Y-m-d H:i:s'),
            'node_key'      => $execution['current_node_key'] ?? null,
            'error_class'   => $error::class,
            'error_message' => $error->getMessage(),
        ]);
    }

    private function emit(string $name, array $execution, array $payload): SystemEvent
    {
        $executionId = (int) $execution['id'];

        return $this->events->emit(
            name: $name,
            module: 'workflow',
            payload: array_merge([
                'workflow_execution_id' => $executionId,
            ], $payload),
            metadata: [
                'source' => self::class,
            ],
            entityType: 'workflow_execution',
            entityId: $executionId,
            correlationId: $this->correlationId($execution),
        );
    }

    private function correlationId(array $execution): string
    {
        $metadata = json_decode($execution['metadata'] ?? '{}', true) ?: [];

        return (string) ($metadata['correlation_id'] ?? sprintf(
            'workflow-execution-%d',
            (int) $execution['id']
        ));
    }

    private function durationMs(float $startedAt): int
    {
        return max(0, (int) round((microtime(true) - $startedAt) * 1000));
    }
}
