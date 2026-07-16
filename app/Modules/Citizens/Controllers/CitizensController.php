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
        $query = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $perPage = (int) $this->request->getGet('per_page');
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $model = new CitizenModel();

        if ($query !== '') {
            $model->groupStart()
                ->like('name', $query)
                ->orLike('municipality', $query)
                ->orLike('community', $query)
                ->orLike('phone', $query)
                ->orLike('email', $query)
                ->groupEnd();
        }

        if ($status !== '') {
            $model->where('status', $status);
        }

        $total = $model->countAllResults(false);
        $citizens = $model->orderBy('created_at', 'DESC')->paginate($perPage, 'default', $page);

        return view('Modules\Citizens\Views\index', [
            'title' => 'Ciudadanos',
            'citizens' => $citizens,
            'filters' => [
                'q' => $query,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'total' => $total,
            'page' => $page,
            'pageCount' => max(1, (int) ceil($total / $perPage)),
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
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
