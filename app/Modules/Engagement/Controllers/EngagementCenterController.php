<?php

namespace Modules\Engagement\Controllers;

use App\Controllers\BaseController;
use Modules\Engagement\Services\EngagementCenterQueryService;

class EngagementCenterController extends BaseController
{
    public function index()
    {
        $status = trim((string) $this->request->getGet('status'));
        $search = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page') ?: 1));
        $perPage = (int) ($this->request->getGet('per_page') ?: 25);
        $service = new EngagementCenterQueryService();
        $result = $service->paginateComments($status !== '' ? $status : null, $search, $page, $perPage);

        return view('Modules\Engagement\Views\index', [
            'title' => 'Public Engagement Center',
            'summary' => $service->summary(),
            'comments' => $result['items'],
            'pagination' => $result,
            'reactions' => $service->reactions(12),
            'participants' => $service->participants(10),
            'reactionBreakdown' => $service->reactionBreakdown(),
            'status' => $status,
            'search' => $search,
            'perPage' => $result['perPage'],
        ]);
    }

    public function participants()
    {
        $search = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page') ?: 1));
        $perPage = (int) ($this->request->getGet('per_page') ?: 25);
        $service = new EngagementCenterQueryService();
        $result = $service->paginateParticipants($search, $page, $perPage);

        return view('Modules\Engagement\Views\participants', [
            'title' => 'Participación ciudadana',
            'participants' => $result['items'],
            'pagination' => $result,
            'reactionBreakdown' => $service->reactionBreakdown(),
            'search' => $search,
            'perPage' => $result['perPage'],
        ]);
    }
}
