<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CaseModel;
use App\Models\CitizenModel;
use App\Models\ConversationModel;
use CodeIgniter\HTTP\ResponseInterface;

class CitizensController extends BaseController
{
    public function index()
    {
        $model = new CitizenModel();

        $data = [
            'title' => 'Ciudadanos',
            'citizens' => $model->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $model->pager,
        ];

        return view('admin/citizens/index', $data);
    }

    public function show($id)
    {
        $citizenModel = new CitizenModel();
        $conversationModel = new ConversationModel();
        $caseModel = new CaseModel();

        $citizen = $citizenModel->find($id);

        if (!$citizen) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Ciudadano no encontrado');
        }

        $data = [
            'title' => 'Perfil ciudadano',
            'citizen' => $citizen,
            'conversations' => $conversationModel->where('citizen_id', $id)->findAll(),
            'cases' => $caseModel->where('citizen_id', $id)->findAll(),
        ];

        return view('admin/citizens/show', $data);
    }
}
