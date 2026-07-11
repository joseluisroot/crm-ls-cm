<?php

namespace Modules\Workflow\Controllers;

use App\Controllers\BaseController;
use Modules\Workflow\Models\WorkflowModel;
use Modules\Workflow\Models\WorkflowSimulationModel;
use Modules\Workflow\Services\WorkflowSimulatorService;
use Modules\Workflow\Support\WorkflowException;
use Throwable;

class WorkflowSimulatorController extends BaseController
{
    public function index()
    {
        $workflows = (new WorkflowModel())
            ->where('status', 'published')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('Modules\Workflow\Views\Simulator\index', [
            'title' => 'Simulador de workflows',
            'workflows' => $workflows,
        ]);
    }

    public function start()
    {
        $workflowSlug = trim(
            (string) $this->request->getPost('workflow_slug')
        );

        if ($workflowSlug === '') {
            return redirect()
                ->back()
                ->with('error', 'Selecciona un workflow para iniciar.');
        }

        try {
            $response = (new WorkflowSimulatorService())->start(
                workflowSlug: $workflowSlug,
                createdBy: (int) session()->get('admin_user_id')
            );

            return redirect()->to(
                site_url(
                    'admin/workflows/simulator/'
                    . $response->executionId
                )
            );
        } catch (WorkflowException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message(
                'error',
                'Error iniciando simulación: ' . $e->getMessage()
            );

            return redirect()
                ->back()
                ->with(
                    'error',
                    'No fue posible iniciar la simulación.'
                );
        }
    }

    public function show(int $simulationId)
    {
        $simulation = (new WorkflowSimulationModel())
            ->select([
                'workflow_simulations.*',
                'workflows.name AS workflow_name',
                'workflows.slug AS workflow_slug',
                'workflow_versions.version_number',
            ])
            ->join(
                'workflows',
                'workflows.id = workflow_simulations.workflow_id'
            )
            ->join(
                'workflow_versions',
                'workflow_versions.id = workflow_simulations.workflow_version_id'
            )
            ->where('workflow_simulations.id', $simulationId)
            ->first();

        if (!$simulation) {
            return redirect()
                ->to(site_url('admin/workflows/simulator'))
                ->with('error', 'La simulación solicitada no existe.');
        }

        try {
            $response = (new WorkflowSimulatorService())
                ->current($simulationId);
        } catch (Throwable $e) {
            log_message(
                'error',
                'Error cargando simulación: ' . $e->getMessage()
            );

            return redirect()
                ->to(site_url('admin/workflows/simulator'))
                ->with('error', $e->getMessage());
        }

        return view(
            'Modules\Workflow\Views\Simulator\show',
            [
                'title' => 'Simulador de workflow',
                'simulation' => $simulation,
                'response' => $response,
                'context' => json_decode(
                    $simulation['context_data'] ?? '{}',
                    true
                ) ?: [],
                'executionLog' => json_decode(
                    $simulation['execution_log'] ?? '[]',
                    true
                ) ?: [],
            ]
        );
    }

    public function interact(int $simulationId)
    {
        $text = trim(
            (string) $this->request->getPost('text')
        );

        $payload = trim(
            (string) $this->request->getPost('payload')
        );

        try {
            (new WorkflowSimulatorService())->handle(
                simulationId: $simulationId,
                text: $text !== '' ? $text : null,
                payload: $payload !== '' ? $payload : null
            );

            return redirect()->to(
                site_url(
                    'admin/workflows/simulator/' . $simulationId
                )
            );
        } catch (WorkflowException $e) {
            $this->registerError(
                $simulationId,
                $e->getMessage()
            );

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            $this->registerError(
                $simulationId,
                $e->getMessage()
            );

            log_message(
                'error',
                'Error ejecutando simulación: ' . $e->getMessage()
            );

            return redirect()
                ->back()
                ->with(
                    'error',
                    'No fue posible procesar la interacción.'
                );
        }
    }

    public function restart(int $simulationId)
    {
        $simulationModel = new WorkflowSimulationModel();
        $simulation = $simulationModel->find($simulationId);

        if (!$simulation) {
            return redirect()
                ->to(site_url('admin/workflows/simulator'))
                ->with('error', 'La simulación no existe.');
        }

        $workflow = (new WorkflowModel())
            ->find($simulation['workflow_id']);

        if (!$workflow) {
            return redirect()
                ->to(site_url('admin/workflows/simulator'))
                ->with('error', 'El workflow asociado no existe.');
        }

        try {
            $response = (new WorkflowSimulatorService())->start(
                workflowSlug: $workflow['slug'],
                createdBy: (int) session()->get('admin_user_id')
            );

            return redirect()->to(
                site_url(
                    'admin/workflows/simulator/'
                    . $response->executionId
                )
            );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    private function registerError(
        int $simulationId,
        string $message
    ): void {
        (new WorkflowSimulationModel())->update(
            $simulationId,
            [
                'last_error' => $message,
            ]
        );
    }
}