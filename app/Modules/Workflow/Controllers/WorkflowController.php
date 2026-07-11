<?php

namespace Modules\Workflow\Controllers;

use App\Controllers\BaseController;
use Modules\Workflow\Models\WorkflowModel;
use Modules\Workflow\Models\WorkflowNodeModel;
use Modules\Workflow\Models\WorkflowTransitionModel;
use Modules\Workflow\Models\WorkflowVersionModel;
use Modules\Workflow\Services\WorkflowManagementService;
use Modules\Workflow\Services\WorkflowPublishingService;
use Modules\Workflow\Support\WorkflowException;
use Throwable;

class WorkflowController extends BaseController
{
    public function index()
    {
        $workflows = (new WorkflowModel())
            ->select([
                'workflows.*',
                'workflow_versions.version_number AS active_version_number',
            ])
            ->join(
                'workflow_versions',
                'workflow_versions.id = workflows.active_version_id',
                'left'
            )
            ->orderBy('workflows.updated_at', 'DESC')
            ->findAll();

        return view('Modules\Workflow\Views\index', [
            'title' => 'Workflows',
            'workflows' => $workflows,
        ]);
    }

    public function create()
    {
        return view('Modules\Workflow\Views\create', [
            'title' => 'Crear workflow',
        ]);
    }

    public function store()
    {
        $name = trim(
            (string) $this->request->getPost('name')
        );

        $slug = trim(
            (string) $this->request->getPost('slug')
        );

        $description = trim(
            (string) $this->request->getPost('description')
        );

        $channel = trim(
            (string) $this->request->getPost('channel')
        );

        if (!in_array($channel, [
            'all',
            'messenger',
            'whatsapp',
            'instagram',
            'webchat',
        ], true)) {
            $channel = 'all';
        }

        try {
            $result = (new WorkflowManagementService())
                ->createWorkflow(
                    name: $name,
                    slug: $slug,
                    description: $description !== ''
                        ? $description
                        : null,
                    channel: $channel,
                    createdBy: (int) session()->get('admin_user_id')
                );

            return redirect()->to(
                site_url(
                    'admin/workflows/' . $result['workflow_id']
                )
            )->with(
                'success',
                'Workflow creado correctamente.'
            );
        } catch (WorkflowException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message(
                'error',
                'Error creando workflow: ' . $e->getMessage()
            );

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'No fue posible crear el workflow.'
                );
        }
    }

    public function show(int $workflowId)
    {
        $workflow = (new WorkflowModel())->find($workflowId);

        if (!$workflow) {
            return redirect()
                ->to(site_url('admin/workflows'))
                ->with('error', 'El workflow solicitado no existe.');
        }

        $versions = (new WorkflowVersionModel())
            ->where('workflow_id', $workflowId)
            ->orderBy('version_number', 'DESC')
            ->findAll();

        foreach ($versions as &$version) {
            $version['nodes_count'] = (new WorkflowNodeModel())
                ->where(
                    'workflow_version_id',
                    $version['id']
                )
                ->countAllResults();

            $version['transitions_count'] = (
            new WorkflowTransitionModel()
            )
                ->where(
                    'workflow_version_id',
                    $version['id']
                )
                ->countAllResults();
        }
        unset($version);

        return view('Modules\Workflow\Views\show', [
            'title' => $workflow['name'],
            'workflow' => $workflow,
            'versions' => $versions,
        ]);
    }

    public function createVersion(int $workflowId)
    {
        try {
            $versionId = (new WorkflowManagementService())
                ->createEmptyVersion(
                    workflowId: $workflowId,
                    createdBy: (int) session()->get('admin_user_id')
                );

            return redirect()->to(
                site_url(
                    'admin/workflows/'
                    . $workflowId
                    . '/versions/'
                    . $versionId
                )
            )->with(
                'success',
                'Nueva versión creada.'
            );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function cloneVersion(
        int $workflowId,
        int $versionId
    ) {
        try {
            $newVersionId = (new WorkflowManagementService())
                ->cloneVersion(
                    workflowId: $workflowId,
                    sourceVersionId: $versionId,
                    createdBy: (int) session()->get('admin_user_id')
                );

            return redirect()->to(
                site_url(
                    'admin/workflows/'
                    . $workflowId
                    . '/versions/'
                    . $newVersionId
                )
            )->with(
                'success',
                'Versión clonada correctamente.'
            );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function publish(
        int $workflowId,
        int $versionId
    ) {
        try {
            (new WorkflowPublishingService())->publish(
                workflowId: $workflowId,
                versionId: $versionId
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Versión publicada correctamente.'
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function archive(int $workflowId)
    {
        try {
            (new WorkflowManagementService())
                ->archiveWorkflow($workflowId);

            return redirect()
                ->to(site_url('admin/workflows'))
                ->with(
                    'success',
                    'Workflow archivado.'
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}