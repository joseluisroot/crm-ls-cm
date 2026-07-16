<?php

namespace Modules\Cases\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Assignment\Exceptions\AssignmentException;
use Modules\Assignment\Services\AssignmentEngineService;
use Modules\Auth\Models\AdminUserModel;
use Modules\Authorization\Application\OwnershipService;
use Modules\Authorization\Application\TeamScopeService;
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
        $builder = $caseModel->select('cases.*, citizens.name as citizen_name, categories.name as category_name, case_statuses.name as status_name')
            ->join('citizens', 'citizens.id = cases.citizen_id')
            ->join('categories', 'categories.id = cases.category_id', 'left')
            ->join('case_statuses', 'case_statuses.id = cases.status_id');

        $title = 'Casos';
        if (cannot('cases.view')) {
            $ids = can('cases.view_team') ? (new TeamScopeService())->userIdsInScope($this->currentUserId()) : [$this->currentUserId()];
            $builder->whereIn('cases.assigned_to', $ids);
            $title = can('cases.view_team') ? 'Casos de mi equipo' : 'Mis casos';
        }

        return view('Modules\Cases\Views\index', ['title' => $title, 'cases' => $builder->orderBy('cases.created_at', 'DESC')->paginate(20), 'pager' => $caseModel->pager]);
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
        $assignedTo = can('cases.assign') ? (int) $this->request->getPost('assigned_to') : $this->currentUserId();
        if ($assignedTo > 0 && can('cases.assign') && cannot('cases.view') && ! in_array($assignedTo, (new TeamScopeService())->userIdsInScope($this->currentUserId()), true)) {
            throw PageNotFoundException::forPageNotFound('Responsable fuera del alcance de tu equipo.');
        }

        (new CaseModel())->insert([
            'citizen_id' => $this->request->getPost('citizen_id'), 'category_id' => $this->request->getPost('category_id'),
            'status_id' => $this->request->getPost('status_id'), 'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'), 'priority' => $this->request->getPost('priority'),
            'sentiment' => $this->request->getPost('sentiment'), 'assigned_to' => $assignedTo > 0 ? $assignedTo : null,
        ]);
        return redirect()->to(can('cases.view') ? '/admin/cases' : '/admin/my-cases')->with('success', 'Caso creado correctamente.');
    }

    public function show($id)
    {
        $this->requireAccess((int) $id);
        $case = (new CaseModel())->select('cases.*, citizens.name as citizen_name, categories.name as category_name, case_statuses.name as status_name')
            ->join('citizens', 'citizens.id = cases.citizen_id')->join('categories', 'categories.id = cases.category_id', 'left')
            ->join('case_statuses', 'case_statuses.id = cases.status_id')->where('cases.id', $id)->first();
        if (! $case) throw PageNotFoundException::forPageNotFound('Caso no encontrado');

        return view('Modules\Cases\Views\show', [
            'title' => 'Detalle del caso', 'case' => $case,
            'history' => (new CaseHistoryModel())->where('case_id', $id)->orderBy('created_at', 'ASC')->findAll(),
            'statuses' => (new CaseStatusModel())->orderBy('id', 'ASC')->findAll(),
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function changeStatus($id)
    {
        $this->requireAction((int) $id, 'cases.update');
        $statusId = (int) $this->request->getPost('status_id');
        if ($statusId <= 0) return redirect()->back()->with('error', 'Selecciona un estado válido.');
        (new CaseLifecycleService())->changeStatus(caseId: (int) $id, statusId: $statusId, description: 'Estado actualizado desde el panel administrativo.', performedBy: session()->get('admin_user_name') ?? 'admin');
        return redirect()->back()->with('success', 'Estado del caso actualizado.');
    }

    public function assign($id)
    {
        $userId = (int) $this->request->getPost('assigned_user_id');
        if ($userId <= 0) return redirect()->back()->with('error', 'Selecciona un responsable válido.');
        if (cannot('cases.view') && ! in_array($userId, (new TeamScopeService())->userIdsInScope($this->currentUserId()), true)) throw PageNotFoundException::forPageNotFound('Responsable fuera del alcance de tu equipo.');
        try {
            (new AssignmentEngineService())->assignCase(caseId: (int) $id, userId: $userId, performedByUserId: $this->currentUserId());
            return redirect()->back()->with('success', 'Caso asignado correctamente.');
        } catch (AssignmentException $e) { return redirect()->back()->with('error', $e->getMessage()); }
        catch (\Throwable $e) { log_message('error', 'Error asignando caso: ' . $e->getMessage()); return redirect()->back()->with('error', 'Ocurrió un error al asignar el caso.'); }
    }

    public function unassign($id)
    {
        try {
            (new AssignmentEngineService())->unassignCase(caseId: (int) $id, performedByUserId: $this->currentUserId());
            return redirect()->back()->with('success', 'Asignación retirada correctamente.');
        } catch (AssignmentException $e) { return redirect()->back()->with('error', $e->getMessage()); }
        catch (\Throwable $e) { log_message('error', 'Error retirando asignación: ' . $e->getMessage()); return redirect()->back()->with('error', 'No fue posible retirar la asignación.'); }
    }

    public function myCases()
    {
        $query = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $priority = trim((string) $this->request->getGet('priority'));
        $perPage = (int) $this->request->getGet('per_page');
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $model = (new CaseModel())
            ->select('cases.*, case_statuses.name AS status_name, case_statuses.slug AS status_slug, categories.name AS category_name, citizens.name AS citizen_name')
            ->join('case_statuses', 'case_statuses.id = cases.status_id')
            ->join('categories', 'categories.id = cases.category_id', 'left')
            ->join('citizens', 'citizens.id = cases.citizen_id')
            ->where('cases.assigned_user_id', $this->currentUserId());

        if ($query !== '') {
            $model->groupStart()->like('cases.title', $query)->orLike('cases.public_code', $query)->orLike('citizens.name', $query)->orLike('categories.name', $query)->groupEnd();
        }
        if ($status !== '') $model->where('case_statuses.slug', $status);
        if ($priority !== '') $model->where('cases.priority', $priority);

        $total = $model->countAllResults(false);
        $pageCount = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pageCount);

        return view('Modules\Cases\Views\my_cases', [
            'title' => 'Mis casos asignados',
            'cases' => $model->orderBy('cases.updated_at', 'DESC')->paginate($perPage, 'default', $page),
            'statuses' => (new CaseStatusModel())->orderBy('id', 'ASC')->findAll(),
            'filters' => ['q' => $query, 'status' => $status, 'priority' => $priority, 'per_page' => $perPage],
            'total' => $total,
            'page' => $page,
            'pageCount' => $pageCount,
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ]);
    }

    private function assignableUsers(): array
    {
        if (cannot('cases.assign')) return [];
        $model = (new AdminUserModel())->where('status', 'active');
        if (cannot('cases.view')) $model->whereIn('id', (new TeamScopeService())->userIdsInScope($this->currentUserId()));
        return $model->orderBy('name', 'ASC')->findAll();
    }

    private function requireAccess(int $caseId): void
    {
        if (! (new OwnershipService())->canAccessCase($this->currentUserId(), $caseId)) throw PageNotFoundException::forPageNotFound('Caso no encontrado o fuera de tu alcance.');
    }

    private function requireAction(int $caseId, string $permission): void
    {
        if (! (new OwnershipService())->canActOnCase($this->currentUserId(), $caseId, $permission)) throw PageNotFoundException::forPageNotFound('Caso no encontrado o acción no autorizada.');
    }

    private function currentUserId(): int { return (int) session()->get('admin_user_id'); }
}
