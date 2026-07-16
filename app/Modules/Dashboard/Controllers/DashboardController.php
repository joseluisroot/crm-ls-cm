<?php

namespace Modules\Dashboard\Controllers;

use App\Controllers\BaseController;
use Modules\Cases\Models\CaseModel;
use Modules\Citizens\Models\CitizenModel;
use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $today = date('Y-m-d 00:00:00');

        $data = [
            'title' => 'Dashboard',
            'totalCitizens' => (new CitizenModel())->countAllResults(),
            'citizensToday' => (new CitizenModel())->where('created_at >=', $today)->countAllResults(),
            'totalConversations' => (new ConversationModel())->countAllResults(),
            'totalMessages' => (new MessageModel())->countAllResults(),
            'messagesToday' => (new MessageModel())->where('created_at >=', $today)->countAllResults(),
            'openCases' => (new CaseModel())->where('closed_at', null)->countAllResults(),
            'highPriorityOpenCases' => (new CaseModel())
                ->where('closed_at', null)
                ->whereIn('priority', ['high', 'urgent'])
                ->countAllResults(),
            'unassignedOpenCases' => (new CaseModel())
                ->where('closed_at', null)
                ->groupStart()
                    ->groupStart()->where('assigned_to', null)->orWhere('assigned_to', 0)->groupEnd()
                    ->groupStart()->where('assigned_user_id', null)->orWhere('assigned_user_id', 0)->groupEnd()
                ->groupEnd()
                ->countAllResults(),
            'closedCasesToday' => (new CaseModel())->where('closed_at >=', $today)->countAllResults(),
        ];

        return view('Modules\Dashboard\Views\index', $data);
    }
}
