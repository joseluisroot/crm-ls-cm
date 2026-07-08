<?php

namespace Modules\Cases\Controllers;

use Modules\Cases\Models\CaseModel;
use Modules\Cases\Models\CategoryModel;
use Modules\Cases\Models\CaseStatusModel;
use Modules\Citizens\Models\CitizenModel;
use App\Controllers\BaseController;

class CasesController extends BaseController
{
    public function index()
    {
        $caseModel = new CaseModel();

        $cases = $caseModel
            ->select('cases.*, citizens.name as citizen_name, categories.name as category_name, case_statuses.name as status_name')
            ->join('citizens', 'citizens.id = cases.citizen_id')
            ->join('categories', 'categories.id = cases.category_id', 'left')
            ->join('case_statuses', 'case_statuses.id = cases.status_id')
            ->orderBy('cases.created_at', 'DESC')
            ->paginate(20);

        return view('Modules\Cases\Views\index', [
            'title' => 'Casos',
            'cases' => $cases,
            'pager' => $caseModel->pager,
        ]);
    }

    public function create()
    {
        return view('Modules\Cases\Views\create', [
            'title' => 'Crear caso',
            'citizens' => (new CitizenModel())->orderBy('name', 'ASC')->findAll(),
            'categories' => (new CategoryModel())->orderBy('name', 'ASC')->findAll(),
            'statuses' => (new CaseStatusModel())->orderBy('id', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        (new CaseModel())->insert([
            'citizen_id' => $this->request->getPost('citizen_id'),
            'category_id' => $this->request->getPost('category_id'),
            'status_id' => $this->request->getPost('status_id'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'priority' => $this->request->getPost('priority'),
            'sentiment' => $this->request->getPost('sentiment'),
            'assigned_to' => $this->request->getPost('assigned_to'),
        ]);

        return redirect()->to('/admin/cases')->with('success', 'Caso creado correctamente.');
    }

    public function show($id)
    {
        $case = (new CaseModel())
            ->select('cases.*, citizens.name as citizen_name, categories.name as category_name, case_statuses.name as status_name')
            ->join('citizens', 'citizens.id = cases.citizen_id')
            ->join('categories', 'categories.id = cases.category_id', 'left')
            ->join('case_statuses', 'case_statuses.id = cases.status_id')
            ->where('cases.id', $id)
            ->first();

        if (!$case) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Caso no encontrado');
        }

        return view('Modules\Cases\Views\show', [
            'title' => 'Detalle del caso',
            'case' => $case,
        ]);
    }
}
