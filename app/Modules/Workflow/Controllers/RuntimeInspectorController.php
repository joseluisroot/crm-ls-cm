<?php

namespace Modules\Workflow\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Workflow\Services\RuntimeInspectorQueryService;

class RuntimeInspectorController extends BaseController
{
    public function __construct(
        private readonly ?RuntimeInspectorQueryService $inspector = null,
    ) {
    }

    public function index(): string
    {
        $limit = max(1, min((int) ($this->request->getGet('limit') ?? 50), 200));
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $executions = $this->service()->recent($limit);

        if ($status !== '') {
            $executions = array_values(array_filter(
                $executions,
                static fn (array $execution): bool => ($execution['status'] ?? null) === $status
            ));
        }

        $statusCounts = array_count_values(array_map(
            static fn (array $execution): string => (string) ($execution['status'] ?? 'unknown'),
            $executions
        ));

        return view('Modules\Workflow\Views\Runtime\index', [
            'title' => 'Runtime Inspector',
            'executions' => $executions,
            'filters' => [
                'status' => $status,
                'limit' => $limit,
            ],
            'kpis' => [
                'Total' => count($executions),
                'Running' => $statusCounts['running'] ?? 0,
                'Completed' => $statusCounts['completed'] ?? 0,
                'Failed' => $statusCounts['failed'] ?? 0,
            ],
        ]);
    }

    public function show(int $executionId): string|ResponseInterface
    {
        $execution = $this->service()->execution($executionId);

        if (!$execution) {
            return redirect()
                ->to(site_url('admin/workflows/runtime'))
                ->with('error', 'La ejecución solicitada no existe.');
        }

        return view('Modules\Workflow\Views\Runtime\show', [
            'title' => 'Ejecución #' . $executionId,
            'execution' => $execution,
        ]);
    }

    public function apiIndex(): ResponseInterface
    {
        $limit = (int) ($this->request->getGet('limit') ?? 50);

        return $this->response->setJSON([
            'data' => $this->service()->recent($limit),
        ]);
    }

    public function apiShow(int $executionId): ResponseInterface
    {
        $execution = $this->service()->execution($executionId);

        if (!$execution) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['message' => 'La ejecución solicitada no existe.']);
        }

        return $this->response->setJSON(['data' => $execution]);
    }

    public function timeline(int $executionId): ResponseInterface
    {
        return $this->response->setJSON([
            'data' => $this->service()->timeline($executionId),
        ]);
    }

    private function service(): RuntimeInspectorQueryService
    {
        return $this->inspector ?? service('runtimeInspectorQuery');
    }
}
