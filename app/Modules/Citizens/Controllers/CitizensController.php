<?php

namespace Modules\Citizens\Controllers;

use App\Controllers\BaseController;
use Modules\Cases\Models\CaseModel;
use Modules\Citizens\Models\CitizenModel;
use Modules\Conversations\Models\ConversationModel;

class CitizensController extends BaseController
{
    public function index()
    {
        $model = new CitizenModel();

        return view('Modules\Citizens\Views\index', [
            'title' => 'Ciudadanos',
            'citizens' => $model->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $model->pager,
        ]);
    }

    public function show($id)
    {
        $citizen = (new CitizenModel())->find($id);

        if (!$citizen) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Ciudadano no encontrado');
        }

        return view('Modules\Citizens\Views\show', [
            'title' => 'Perfil ciudadano',
            'citizen' => $citizen,
            'conversations' => (new ConversationModel())->where('citizen_id', $id)->findAll(),
            'cases' => (new CaseModel())->where('citizen_id', $id)->findAll(),
        ]);
    }
}
