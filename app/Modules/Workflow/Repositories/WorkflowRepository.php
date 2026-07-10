<?php

namespace Modules\Workflow\Repositories;

use Modules\Workflow\Models\WorkflowExecutionModel;
use Modules\Workflow\Models\WorkflowModel;
use Modules\Workflow\Models\WorkflowNodeModel;
use Modules\Workflow\Models\WorkflowTransitionModel;
use Modules\Workflow\Models\WorkflowVersionModel;

class WorkflowRepository
{
    public function findWorkflowBySlug(string $slug): ?array
    {
        return (new WorkflowModel())
            ->where('slug', $slug)
            ->first();
    }

    public function findPublishedWorkflow(
        string $slug,
        string $channel = 'messenger'
    ): ?array {
        return (new WorkflowModel())
            ->where('slug', $slug)
            ->where('status', 'published')
            ->groupStart()
            ->where('channel', 'all')
            ->orWhere('channel', $channel)
            ->groupEnd()
            ->first();
    }

    public function findVersion(int $versionId): ?array
    {
        return (new WorkflowVersionModel())->find($versionId);
    }

    public function findPublishedVersion(int $workflowId): ?array
    {
        return (new WorkflowVersionModel())
            ->where('workflow_id', $workflowId)
            ->where('status', 'published')
            ->orderBy('version_number', 'DESC')
            ->first();
    }

    public function findNode(
        int $versionId,
        string $nodeKey
    ): ?array {
        return (new WorkflowNodeModel())
            ->where('workflow_version_id', $versionId)
            ->where('node_key', $nodeKey)
            ->first();
    }

    public function findTransitions(
        int $versionId,
        string $sourceNodeKey
    ): array {
        return (new WorkflowTransitionModel())
            ->where('workflow_version_id', $versionId)
            ->where('source_node_key', $sourceNodeKey)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function findRunningExecution(
        int $conversationId,
        ?int $workflowId = null
    ): ?array {
        $model = new WorkflowExecutionModel();

        $model
            ->where('conversation_id', $conversationId)
            ->where('status', 'running');

        if ($workflowId !== null) {
            $model->where('workflow_id', $workflowId);
        }

        return $model
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function findExecution(int $executionId): ?array
    {
        return (new WorkflowExecutionModel())->find($executionId);
    }

    public function createExecution(array $data): int
    {
        return (int) (new WorkflowExecutionModel())->insert($data);
    }

    public function updateExecution(
        int $executionId,
        array $data
    ): bool {
        return (bool) (new WorkflowExecutionModel())
            ->update($executionId, $data);
    }
}