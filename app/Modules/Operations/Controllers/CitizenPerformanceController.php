<?php

declare(strict_types=1);

namespace Modules\Operations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Authorization\Application\TeamScopeService;
use Modules\Operations\Application\CitizenPerformanceQueryService;

final class CitizenPerformanceController extends BaseController
{
    public function index()
    {
        if (! can('analytics.view') && ! can('analytics.team')) {
            throw PageNotFoundException::forPageNotFound('Centro de desempeño no disponible para este perfil.');
        }

        $global = can('analytics.view');
        $scopeUserIds = $global ? null : (new TeamScopeService())->userIdsInScope($this->currentUserId());
        $data = (new CitizenPerformanceQueryService())->dashboard($scopeUserIds);

        return view('Modules\Operations\Views\performance', [
            'title' => 'Citizen Performance Center',
            'scopeLabel' => $global ? 'Operación global' : 'Mi equipo',
            'summary' => $data['summary'],
            'operators' => $data['operators'],
        ]);
    }

    private function currentUserId(): int
    {
        return (int) session()->get('admin_user_id');
    }
}
