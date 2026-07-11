<?php

namespace Modules\Workflow\Controllers;

use App\Controllers\BaseController;
use Modules\Workflow\Models\WorkflowNodeModel;
use Modules\Workflow\Models\WorkflowTransitionModel;
use Modules\Workflow\Services\WorkflowEditorService;
use Throwable;

class WorkflowTransitionController extends BaseController
{
    public function create(
        int $workflowId,
        int $versionId
    ) {
        $nodes = (new WorkflowNodeModel())
            ->where('workflow_version_id', $versionId)
            ->orderBy('name', 'ASC')
            ->findAll();

        return view(
            'Modules\Workflow\Views\Transitions\form',
            [
                'title' => 'Crear transición',
                'workflowId' => $workflowId,
                'versionId' => $versionId,
                'transition' => null,
                'nodes' => $nodes,
            ]
        );
    }

    public function store(
        int $workflowId,
        int $versionId
    ) {
        try {
            (new WorkflowEditorService())->createTransition(
                versionId: $versionId,
                data: $this->request->getPost()
            );

            return redirect()->to(
                site_url(
                    "admin/workflows/{$workflowId}/versions/{$versionId}"
                )
            )->with(
                'success',
                'Transición creada correctamente.'
            );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(
        int $workflowId,
        int $versionId,
        int $transitionId
    ) {
        $transition = (new WorkflowTransitionModel())
            ->where('id', $transitionId)
            ->where('workflow_version_id', $versionId)
            ->first();

        $nodes = (new WorkflowNodeModel())
            ->where('workflow_version_id', $versionId)
            ->orderBy('name', 'ASC')
            ->findAll();

        if (!$transition) {
            return redirect()
                ->back()
                ->with('error', 'La transición no existe.');
        }

        return view(
            'Modules\Workflow\Views\Transitions\form',
            [
                'title' => 'Editar transición',
                'workflowId' => $workflowId,
                'versionId' => $versionId,
                'transition' => $transition,
                'nodes' => $nodes,
            ]
        );
    }

    public function update(
        int $workflowId,
        int $versionId,
        int $transitionId
    ) {
        try {
            (new WorkflowEditorService())->updateTransition(
                versionId: $versionId,
                transitionId: $transitionId,
                data: $this->request->getPost()
            );

            return redirect()->to(
                site_url(
                    "admin/workflows/{$workflowId}/versions/{$versionId}"
                )
            )->with(
                'success',
                'Transición actualizada.'
            );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function delete(
        int $workflowId,
        int $versionId,
        int $transitionId
    ) {
        try {
            (new WorkflowEditorService())->deleteTransition(
                $versionId,
                $transitionId
            );

            return redirect()
                ->back()
                ->with('success', 'Transición eliminada.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}