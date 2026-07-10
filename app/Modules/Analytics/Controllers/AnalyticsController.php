<?php

namespace Modules\Analytics\Controllers;

use App\Controllers\BaseController;
use Modules\Analytics\Services\AnalyticsEngineService;

class AnalyticsController extends BaseController
{
    public function index()
    {
        $dashboard = (
        new AnalyticsEngineService()
        )->buildExecutiveDashboard();

        return view(
            'Modules\Analytics\Views\index',
            [
                'title' => 'Centro de Inteligencia',
                'analytics' => $dashboard->toArray(),
            ]
        );
    }

    public function data()
    {
        $dashboard = (
        new AnalyticsEngineService()
        )->buildExecutiveDashboard();

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $dashboard->toArray(),
            'generated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}