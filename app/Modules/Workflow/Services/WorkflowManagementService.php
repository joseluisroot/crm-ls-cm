<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Models\WorkflowModel;
use Modules\Workflow\Models\WorkflowNodeModel;
use Modules\Workflow\Models\WorkflowTransitionModel;
use Modules\Workflow\Models\WorkflowVersionModel;
use Modules\Workflow\Support\WorkflowException;
use Modules\Workflow\Support\WorkflowStatus;
use Throwable;

class WorkflowManagementService
{
    public function createWorkflow(
        string $name,
        string $slug,
        ?string $description,
        string $channel,
        ?int $createdBy = null
    ): array {
        $name = trim($name);
        $slug = $this->normalizeSlug($slug);

        if ($name === '') {
            throw new WorkflowException(
                'El nombre del workflow es obligatorio.'
            );
        }

        if ($slug === '') {
            throw new WorkflowException(
                'El slug del workflow es obligatorio.'
            );
        }

        $workflowModel = new WorkflowModel();

        $existing = $workflowModel
            ->where('slug', $slug)
            ->first();

        if ($existing) {
            throw new WorkflowException(
                'Ya existe un workflow con ese slug.'
            );
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $workflowId = (int) $workflowModel->insert([
                'name'              => $name,
                'slug'              => $slug,
                'description'       => $description,
                'channel'           => $channel,
                'status'            => WorkflowStatus::DRAFT,
                'active_version_id' => null,
                'created_by'        => $createdBy,
            ]);

            if ($workflowId <= 0) {
                throw new WorkflowException(
                    'No fue posible crear el workflow.'
                );
            }

            $versionId = (int) (new WorkflowVersionModel())->insert([
                'workflow_id'    => $workflowId,
                'version_number' => 1,
                'status'         => WorkflowStatus::DRAFT,
                'start_node_key' => null,
                'published_at'   => null,
                'created_by'     => $createdBy,
            ]);

            if ($versionId <= 0) {
                throw new WorkflowException(
                    'No fue posible crear la versión inicial.'
                );
            }

            $db->transCommit();

            return [
                'workflow_id' => $workflowId,
                'version_id'  => $versionId,
            ];
        } catch (Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function createEmptyVersion(
        int $workflowId,
        ?int $createdBy = null
    ): int {
        $workflow = (new WorkflowModel())->find($workflowId);

        if (!$workflow) {
            throw new WorkflowException(
                'El workflow seleccionado no existe.'
            );
        }

        $lastVersion = (new WorkflowVersionModel())
            ->where('workflow_id', $workflowId)
            ->orderBy('version_number', 'DESC')
            ->first();

        $nextVersion = (int) ($lastVersion['version_number'] ?? 0) + 1;

        $versionId = (int) (new WorkflowVersionModel())->insert([
            'workflow_id'    => $workflowId,
            'version_number' => $nextVersion,
            'status'         => WorkflowStatus::DRAFT,
            'start_node_key' => null,
            'published_at'   => null,
            'created_by'     => $createdBy,
        ]);

        if ($versionId <= 0) {
            throw new WorkflowException(
                'No fue posible crear la nueva versión.'
            );
        }

        return $versionId;
    }

    public function cloneVersion(
        int $workflowId,
        int $sourceVersionId,
        ?int $createdBy = null
    ): int {
        $versionModel = new WorkflowVersionModel();
        $nodeModel = new WorkflowNodeModel();
        $transitionModel = new WorkflowTransitionModel();

        $sourceVersion = $versionModel->find($sourceVersionId);

        if (
            !$sourceVersion
            || (int) $sourceVersion['workflow_id'] !== $workflowId
        ) {
            throw new WorkflowException(
                'La versión seleccionada no pertenece al workflow.'
            );
        }

        $lastVersion = $versionModel
            ->where('workflow_id', $workflowId)
            ->orderBy('version_number', 'DESC')
            ->first();

        $nextVersion = (int) ($lastVersion['version_number'] ?? 0) + 1;

        $db = db_connect();
        $db->transBegin();

        try {
            $newVersionId = (int) $versionModel->insert([
                'workflow_id'    => $workflowId,
                'version_number' => $nextVersion,
                'status'         => WorkflowStatus::DRAFT,
                'start_node_key' => $sourceVersion['start_node_key'],
                'published_at'   => null,
                'created_by'     => $createdBy,
            ]);

            if ($newVersionId <= 0) {
                throw new WorkflowException(
                    'No fue posible clonar la versión.'
                );
            }

            $nodes = $nodeModel
                ->where('workflow_version_id', $sourceVersionId)
                ->findAll();

            foreach ($nodes as $node) {
                $nodeModel->insert([
                    'workflow_version_id' => $newVersionId,
                    'node_key'            => $node['node_key'],
                    'name'                => $node['name'],
                    'node_type'           => $node['node_type'],
                    'message_text'        => $node['message_text'],
                    'context_key'         => $node['context_key'],
                    'configuration'       => $node['configuration'],
                    'position_x'          => $node['position_x'],
                    'position_y'          => $node['position_y'],
                    'is_terminal'         => $node['is_terminal'],
                ]);
            }

            $transitions = $transitionModel
                ->where('workflow_version_id', $sourceVersionId)
                ->findAll();

            foreach ($transitions as $transition) {
                $transitionModel->insert([
                    'workflow_version_id' => $newVersionId,
                    'source_node_key'     => $transition['source_node_key'],
                    'target_node_key'     => $transition['target_node_key'],
                    'label'               => $transition['label'],
                    'payload'             => $transition['payload'],
                    'condition_type'      => $transition['condition_type'],
                    'condition_value'     => $transition['condition_value'],
                    'sort_order'          => $transition['sort_order'],
                ]);
            }

            $db->transCommit();

            return $newVersionId;
        } catch (Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function archiveWorkflow(int $workflowId): bool
    {
        $workflow = (new WorkflowModel())->find($workflowId);

        if (!$workflow) {
            throw new WorkflowException(
                'El workflow seleccionado no existe.'
            );
        }

        return (bool) (new WorkflowModel())->update(
            $workflowId,
            [
                'status' => WorkflowStatus::ARCHIVED,
            ]
        );
    }

    private function normalizeSlug(string $slug): string
    {
        $slug = mb_strtolower(trim($slug));

        $slug = preg_replace(
            '/[^a-z0-9]+/u',
            '-',
            $slug
        );

        return trim((string) $slug, '-');
    }
}