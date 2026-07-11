<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Models\WorkflowModel;
use Modules\Workflow\Models\WorkflowVersionModel;
use Modules\Workflow\Repositories\WorkflowRepository;
use Modules\Workflow\Support\WorkflowException;
use Modules\Workflow\Support\WorkflowStatus;
use Modules\Workflow\Services\WorkflowValidatorService;

class WorkflowPublishingService
{
    public function __construct(
        private readonly ?WorkflowRepository $repository = null
    ) {
    }

    public function publish(int $workflowId, int $versionId): bool
    {
        $repository = $this->repository ?? new WorkflowRepository();

        $workflow = (new WorkflowModel())->find($workflowId);
        $version = $repository->findVersion($versionId);

        if (!$workflow) {
            throw new WorkflowException('El flujo seleccionado no existe.');
        }

        if (!$version || (int) $version['workflow_id'] !== $workflowId) {
            throw new WorkflowException(
                'La versión seleccionada no pertenece al flujo.'
            );
        }

        $validation = (new WorkflowValidatorService())
            ->validateVersion($versionId);

        if (!$validation->isValid()) {
            $messages = array_map(
                static fn(array $error): string => $error['message'],
                $validation->errors
            );

            throw new WorkflowException(
                "La versión no puede publicarse:\n- "
                . implode("\n- ", $messages)
            );
        }

        if (empty($version['start_node_key'])) {
            throw new WorkflowException(
                'La versión no tiene un nodo inicial configurado.'
            );
        }

        $startNode = $repository->findNode(
            $versionId,
            $version['start_node_key']
        );

        if (!$startNode) {
            throw new WorkflowException(
                'El nodo inicial configurado no existe.'
            );
        }

        $db = db_connect();
        $db->transStart();

        // Archivar versiones publicadas anteriormente.
        (new WorkflowVersionModel())
            ->where('workflow_id', $workflowId)
            ->where('status', WorkflowStatus::PUBLISHED)
            ->set([
                'status' => WorkflowStatus::ARCHIVED,
            ])
            ->update();

        (new WorkflowVersionModel())->update($versionId, [
            'status'       => WorkflowStatus::PUBLISHED,
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        (new WorkflowModel())->update($workflowId, [
            'status'            => WorkflowStatus::PUBLISHED,
            'active_version_id' => $versionId,
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new WorkflowException(
                'No fue posible publicar la versión del flujo.'
            );
        }

        return true;
    }
}