<?php

namespace Modules\Cases\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Assignment\Exceptions\AssignmentException;
use Modules\Assignment\Services\AssignmentEngineService;
use Modules\Auth\Models\AdminUserModel;
use Modules\Authorization\Application\OwnershipService;
use Modules\CaseEngine\Services\CaseLifecycleService;
use Modules\Cases\Models\CaseHistoryModel;
use Modules\Cases\Models\CaseModel;
use Modules\Cases\Models\CaseStatusModel;
use Modules\Cases\Models\CategoryModel;
use Modules\Citizens\Models\CitizenModel;

class CasesController extends BaseController
{
    public function index()
    {
        $caseModel = new CaseModel();
        $builder = $caseModel
            ->select('cases.*, citizens.name as citizen_name, categories.name as category_name, case_statuses.name as status_name')
            ->join('citizens', 'citizens.id = cases.citizen_id')
            ->join('categories', 'categories.id = cases.category_id', 'left')
            ->join('case_statuses', 'case_statuses.id = cases.status_id');

        if (can('cases.view_own') && cannot('cases.view')) {
            $builder->where('cases.assigned_to', $this->currentUserId());
        }

        $cases = $builder->orderBy('cases.created_at', 'DESC')->paginate(20);

        return view('Modules\Cases\Views\index', [
            'title' => can('cases.view_own') && cannot('cases.view') ? 'Mis casos' : 'Casos',
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
        $assignedTo = can('cases.assign')
            ? (int) $this->request->getPost('assigned_to')
            : $this->currentUserId();

        (new CaseModel())->insert([
            'citizen_id' => $this->request->getPost('citizen_id'),
            'category_id' => $this->request->getPost('category_id'),
            'status_id' => $this->request->getPost('status_id'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'priority' => $this->request->getPost('priority'),
            'sentiment' => $this->request->getPost('sentiment'),
            'assigned_to' => $assignedTo > 0 ? $assignedTo : null,
        ]);

        return redirect()->to(can('cases.view') ? '/admin/cases' : '/admin/my-cases')->with('success', 'Caso creado correctamente.');
    }

    public function show($id)
    {
        $this->requireAccess((int) $id);

        $case = (new CaseModel())
            ->select('cases.*, citizens.name as citizen_name, categories.name as category_name, case_statuses.name as status_name')
            ->join('citizens', 'citizens.id = cases.citizen_id')
            ->join('categories', 'categories.id = cases.category_id', 'left')
            ->join('case_statuses', 'case_statuses.id = cases.status_id')
            ->where('cases.id', $id)
            ->first();

        if (! $case) {
            throw PageNotFoundException::forPageNotFound('Caso no encontrado');
        }

        return view('Modules\Cases\Views\show', [
            'title' => 'Detalle del caso',
            'case' => $case,
            'history' => (new CaseHistoryModel())->where('case_id', $id)->orderBy('created_at', 'ASC')->findAll(),
            'statuses' => (new CaseStatusModel())->orderBy('id', 'ASC')->findAll(),
            'assignableUsers' => can('cases.assign')
                ? (new AdminUserModel())->where('status', 'active')->orderBy('name', 'ASC')->findAll()
                : [],
        ]);
    }

    public function changeStatus($id)
    {
        $this->requireAction((int) $id, 'cases.update');
        $statusId = (int) $this->request->getPost('status_id');

        if ($statusId <= 0) {
            return redirect()->back()->with('error', 'Selecciona un estado válido.');
        }

        (new CaseLifecycleService())->changeStatus(
            caseId: (int) $id,
            statusId: $statusId,
            description: 'Estado actualizado desde el panel administrativo.',
            performedBy: session()->get('admin_user_name') ?? 'admin'
        );

        return redirect()->back()->with('success', 'Estado del caso actualizado.');
    }

    public function assign($id)
    {
        $userId = (int) $this->request->getPost('assigned_user_id');
        if ($userId <= 0) {
            return redirect()->back()->with('error', 'Selecciona un responsable válido.');
        }

        try {
            (new AssignmentEngineService())->assignCase(
                caseId: (int) $id,
                userId: $userId,
                performedByUserId: $this->currentUserId()
            );
            return redirect()->back()->with('success', 'Caso asignado correctamente.');
        } catch (AssignmentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Error asignando caso: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al asignar el caso.');
        }
    }

    public function unassign($id)
    {
        try {
            (new AssignmentEngineService())->unassignCase(
                caseId: (int) $id,
                performedByUserId: $this->currentUserId()
            );
            return redirect()->back()->with('success', 'Asignación retirada correctamente.');
        } catch (AssignmentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Error retirando asignación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No fue posible retirar la asignación.');
        }
    }

    public function myCases()
    {
        $cases = (new AssignmentEngineService())->getUserCases($this->currentUserId());

        return view('Modules\Cases\Views\my_cases', [
            'title' => 'Mis casos asignados',
            'cases' => $cases,
        ]);
    }

    private function requireAccess(int $caseId): void
    {
        if (! (new OwnershipService())->canAccessCase($this->currentUserId(), $caseId)) {
            throw PageNotFoundException::forPageNotFound('Caso no encontrado o fuera de tu alcance.');
        }
    }

    private function requireAction(int $caseId, string $permission): void
    {
        if (! (new OwnershipService())->canActOnCase($this->currentUserId(), $caseId, $permission)) {
            throw PageNotFoundException::forPageNotFound('Caso no encontrado o acción no autorizada.');
        }
    }

    private function currentUserId(): int
    {
        return (int) session()->get('admin_user_id');
    }
}
