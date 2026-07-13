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
        $citizenId = (int) $id;
        $citizen = (new CitizenModel())->find($citizenId);

        if (! $citizen) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Ciudadano no encontrado');
        }

        return view('Modules\Citizens\Views\show', [
            'title' => 'Perfil ciudadano',
            'citizen' => $citizen,
            'conversations' => (new ConversationModel())->where('citizen_id', $citizenId)->findAll(),
            'cases' => (new CaseModel())->where('citizen_id', $citizenId)->findAll(),
            'timeline' => service('citizenTimeline')->timeline($citizenId),
            'identities' => db_connect()->table('citizen_social_identities')
                ->where('citizen_id', $citizenId)
                ->where('is_active', 1)
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray(),
        ]);
    }
}
