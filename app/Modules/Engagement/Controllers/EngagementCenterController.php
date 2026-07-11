<?php

namespace Modules\Engagement\Controllers;

use App\Controllers\BaseController;
use Modules\Engagement\Services\EngagementCenterQueryService;

class EngagementCenterController extends BaseController
{
    public function index()
    {
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $limit = (int) ($this->request->getGet('limit') ?? 50);
        $service = new EngagementCenterQueryService();

        return view('Modules\Engagement\Views\index', [
            'title' => 'Public Engagement Center',
            'summary' => $service->summary(),
            'comments' => $service->comments($status !== '' ? $status : null, $limit),
            'reactions' => $service->reactions(20),
            'participants' => $service->participants(15),
            'reactionBreakdown' => $service->reactionBreakdown(),
            'status' => $status,
            'limit' => $limit,
        ]);
    }

    public function participants()
    {
        $service = new EngagementCenterQueryService();

        return view('Modules\Engagement\Views\participants', [
            'title' => 'Participación ciudadana',
            'participants' => $service->participants(100),
            'reactionBreakdown' => $service->reactionBreakdown(),
        ]);
    }
}
