<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

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

        $data = [
            'title' => 'Casos',
            'cases' => $cases,
            'pager' => $caseModel->pager,
        ];

        return view('admin/cases/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Crear caso',
            'citizens' => (new CitizenModel())->orderBy('name', 'ASC')->findAll(),
            'categories' => (new CategoryModel())->orderBy('name', 'ASC')->findAll(),
            'statuses' => (new CaseStatusModel())->orderBy('id', 'ASC')->findAll(),
        ];

        return view('admin/cases/create', $data);
    }

    public function store()
    {
        $caseModel = new CaseModel();

        $caseModel->insert([
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
        $caseModel = new CaseModel();

        $case = $caseModel
            ->select('cases.*, citizens.name as citizen_name, categories.name as category_name, case_statuses.name as status_name')
            ->join('citizens', 'citizens.id = cases.citizen_id')
            ->join('categories', 'categories.id = cases.category_id', 'left')
            ->join('case_statuses', 'case_statuses.id = cases.status_id')
            ->where('cases.id', $id)
            ->first();

        if (!$case) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Caso no encontrado');
        }

        return view('admin/cases/show', [
            'title' => 'Detalle del caso',
            'case' => $case,
        ]);
    }
}
