<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CaseModel;
use App\Models\CitizenModel;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        $citizenModel = new CitizenModel();
        $conversationModel = new ConversationModel();
        $messageModel = new MessageModel();
        $caseModel = new CaseModel();

        $data = [
            'title' => 'Dashboard',
            'totalCitizens' => $citizenModel->countAllResults(),
            'totalConversations' => $conversationModel->countAllResults(),
            'totalMessages' => $messageModel->countAllResults(),
            'openCases' => $caseModel->where('closed_at', null)->countAllResults(),
        ];

        return view('admin/dashboard/index', $data);
    }
}
