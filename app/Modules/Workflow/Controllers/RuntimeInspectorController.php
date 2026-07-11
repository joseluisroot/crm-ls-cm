<?php

namespace Modules\Workflow\Controllers;

use App\Controllers\BaseController;
use Modules\Workflow\Services\RuntimeInspectorQueryService;

class RuntimeInspectorController extends BaseController
{
    public function __construct(
        private readonly ?RuntimeInspectorQueryService $inspector = null,
    ) {
    }

    public function index()
    {
        $limit = (int) ($this->request->getGet('limit') ?? 50);

        return $this->response->setJSON([
            'data' => $this->service()->recent($limit),
        ]);
    }

    public function show(int $executionId)
    {
        $execution = $this->service()->execution($executionId);

        if (!$execution) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'message' => 'La ejecución solicitada no existe.',
                ]);
        }

        return $this->response->setJSON([
            'data' => $execution,
        ]);
    }

    public function timeline(int $executionId)
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
