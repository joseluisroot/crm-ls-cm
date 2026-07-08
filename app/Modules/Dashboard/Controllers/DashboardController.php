<?php

namespace Modules\Dashboard\Controllers;

use App\Controllers\BaseController;
use Modules\Citizens\Models\CitizenModel;
use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;
use Modules\Cases\Models\CaseModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'totalCitizens' => (new CitizenModel())->countAllResults(),
            'totalConversations' => (new ConversationModel())->countAllResults(),
            'totalMessages' => (new MessageModel())->countAllResults(),
            'openCases' => (new CaseModel())->where('closed_at', null)->countAllResults(),
        ];

        return view('Modules\Dashboard\Views\index', $data);
    }
}