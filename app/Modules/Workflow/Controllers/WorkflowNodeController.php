<?php

namespace Modules\Workflow\Controllers;

use App\Controllers\BaseController;
use Modules\Workflow\Models\WorkflowModel;
use Modules\Workflow\Models\WorkflowNodeModel;
use Modules\Workflow\Models\WorkflowVersionModel;
use Modules\Workflow\Services\WorkflowEditorService;
use Modules\Workflow\Support\WorkflowNodeTypes;
use Throwable;

class WorkflowNodeController extends BaseController
{
    public function create(
        int $workflowId,
        int $versionId
    ) {
        $data = $this->loadWorkflowVersion(
            $workflowId,
            $versionId
        );

        return view(
            'Modules\Workflow\Views\Nodes\form',
            array_merge($data, [
                'title' => 'Crear nodo',
                'node' => null,
                'nodeTypes' => WorkflowNodeTypes::all(),
            ])
        );
    }

    public function store(
        int $workflowId,
        int $versionId
    ) {
        try {
            (new WorkflowEditorService())->createNode(
                versionId: $versionId,
                data: $this->request->getPost()
            );

            return redirect()->to(
                site_url(
                    "admin/workflows/{$workflowId}/versions/{$versionId}"
                )
            )->with(
                'success',
                'Nodo creado correctamente.'
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
        int $nodeId
    ) {
        $data = $this->loadWorkflowVersion(
            $workflowId,
            $versionId
        );

        $node = (new WorkflowNodeModel())
            ->where('id', $nodeId)
            ->where('workflow_version_id', $versionId)
            ->first();

        if (!$node) {
            return redirect()
                ->back()
                ->with('error', 'El nodo no existe.');
        }

        return view(
            'Modules\Workflow\Views\Nodes\form',
            array_merge($data, [
                'title' => 'Editar nodo',
                'node' => $node,
                'nodeTypes' => WorkflowNodeTypes::all(),
            ])
        );
    }

    public function update(
        int $workflowId,
        int $versionId,
        int $nodeId
    ) {
        try {
            (new WorkflowEditorService())->updateNode(
                versionId: $versionId,
                nodeId: $nodeId,
                data: $this->request->getPost()
            );

            return redirect()->to(
                site_url(
                    "admin/workflows/{$workflowId}/versions/{$versionId}"
                )
            )->with(
                'success',
                'Nodo actualizado correctamente.'
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
        int $nodeId
    ) {
        try {
            (new WorkflowEditorService())->deleteNode(
                $versionId,
                $nodeId
            );

            return redirect()
                ->back()
                ->with('success', 'Nodo eliminado.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    private function loadWorkflowVersion(
        int $workflowId,
        int $versionId
    ): array {
        $workflow = (new WorkflowModel())->find($workflowId);

        $version = (new WorkflowVersionModel())
            ->where('id', $versionId)
            ->where('workflow_id', $workflowId)
            ->first();

        if (!$workflow || !$version) {
            throw new \RuntimeException(
                'El workflow o la versión no existe.'
            );
        }

        return compact('workflow', 'version');
    }
}