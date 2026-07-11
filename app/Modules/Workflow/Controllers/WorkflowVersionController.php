<?php

namespace Modules\Workflow\Controllers;

use App\Controllers\BaseController;
use Modules\Workflow\Models\WorkflowModel;
use Modules\Workflow\Models\WorkflowNodeModel;
use Modules\Workflow\Models\WorkflowTransitionModel;
use Modules\Workflow\Models\WorkflowVersionModel;
use Modules\Workflow\Services\WorkflowValidatorService;

class WorkflowVersionController extends BaseController
{
    public function show(
        int $workflowId,
        int $versionId
    ) {
        $workflow = (new WorkflowModel())
            ->find($workflowId);

        $version = (new WorkflowVersionModel())
            ->where('id', $versionId)
            ->where('workflow_id', $workflowId)
            ->first();

        if (!$workflow || !$version) {
            return redirect()
                ->to(site_url('admin/workflows'))
                ->with(
                    'error',
                    'El workflow o la versión no existe.'
                );
        }

        $nodes = (new WorkflowNodeModel())
            ->where('workflow_version_id', $versionId)
            ->orderBy('position_y', 'ASC')
            ->orderBy('position_x', 'ASC')
            ->findAll();

        $transitions = (new WorkflowTransitionModel())
            ->where('workflow_version_id', $versionId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        return view(
            'Modules\Workflow\Views\Versions\show',
            [
                'title' => $workflow['name']
                    . ' v'
                    . $version['version_number'],
                'workflow' => $workflow,
                'version' => $version,
                'nodes' => $nodes,
                'transitions' => $transitions,
            ]
        );
    }

    public function validateVersion(
        int $workflowId,
        int $versionId
    ) {
        $workflow = (new WorkflowModel())->find($workflowId);

        $version = (new WorkflowVersionModel())
            ->where('id', $versionId)
            ->where('workflow_id', $workflowId)
            ->first();

        if (!$workflow || !$version) {
            return redirect()
                ->to(site_url('admin/workflows'))
                ->with(
                    'error',
                    'El workflow o la versión no existe.'
                );
        }

        $result = (new WorkflowValidatorService())
            ->validateVersion($versionId);

        return view(
            'Modules\Workflow\Views\Versions\validation',
            [
                'title' => 'Validación de workflow',
                'workflow' => $workflow,
                'version' => $version,
                'validation' => $result,
            ]
        );
    }
}