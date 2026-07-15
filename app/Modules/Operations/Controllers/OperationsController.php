<?php

namespace Modules\Operations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Authorization\Application\OwnershipService;
use Modules\Authorization\Application\TeamScopeService;
use Modules\Operations\Application\OperationalQueueCatalog;
use Modules\Operations\Application\SlaClockService;
use Modules\Response\Application\QuickActionCatalog;
use Modules\Response\Application\ResponseContextResolver;
use Modules\Response\Application\ResponseDraftService;
use Throwable;

final class OperationsController extends BaseController
{
    public function index()
    {
        $catalog = new OperationalQueueCatalog();
        $group = $catalog->normalize((string) $this->request->getGet('queue'));
        $status = trim((string) $this->request->getGet('status')) ?: null;
        $priority = trim((string) $this->request->getGet('priority')) ?: null;
        $search = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page') ?: 1));
        $perPage = (int) ($this->request->getGet('per_page') ?: 25);
        $query = service('operationsQueueQuery');
        $scopeUserIds = $this->scopeUserIds();
        $result = $query->paginate($group, $status, $priority, $search, $page, $perPage, $scopeUserIds);

        return view('Modules\Operations\Views\index', [
            'title' => $this->scopeTitle(),
            'summary' => $query->summary($scopeUserIds),
            'items' => $result['items'],
            'pagination' => $result,
            'queues' => $catalog->all(),
            'queue' => $group,
            'statuses' => $query->statuses(),
            'priorities' => $query->priorities(),
            'status' => $status,
            'priority' => $priority,
            'search' => $search,
            'perPage' => $result['perPage'],
        ]);
    }

    public function show(int $id)
    {
        $this->requireAccess($id);
        $query = service('operationsDetailQuery');
        $item = $query->find($id);
        if (! $item) throw PageNotFoundException::forPageNotFound('Work Item no encontrado.');

        $citizenCard = ! empty($item['citizen_id']) ? service('citizenCard')->get((int) $item['citizen_id']) : null;
        $catalog = new QuickActionCatalog();
        $authorName = $item['source']['author_name'] ?? $item['title'] ?? null;
        $channel = strtoupper((string) ($item['channel'] ?? ''));
        $quickActions = array_map(static fn (array $action): array => $catalog->personalize($action, $authorName), $catalog->forChannel($channel));
        $db = db_connect();

        return view('Modules\Operations\Views\show', [
            'title' => 'Atención #' . $id, 'item' => $item, 'citizenCard' => $citizenCard,
            'timeline' => $query->timeline($id), 'users' => $this->assignableUsers($query),
            'statuses' => $query->statuses(), 'priorities' => $query->priorities(),
            'responseDraft' => (new ResponseDraftService($db))->findForWorkItem($id),
            'responseCapability' => (new ResponseContextResolver($db))->capability($id),
            'responses' => $db->table('citizen_responses')->where('work_item_id', $id)->orderBy('id', 'DESC')->get()->getResultArray(),
            'quickActions' => $quickActions,
        ]);
    }

    public function importPending()
    {
        $count = service('facebookCommentWorkItemAdapter')->importPending(500);
        return redirect()->to(site_url('admin/operations?queue=PENDING'))->with('success', $count . ' comentarios fueron sincronizados con Citizen Operations.');
    }

    public function assign(int $id)
    {
        $userId = (int) $this->request->getPost('assigned_user_id');
        if ($userId <= 0) return redirect()->back()->with('error', 'Selecciona un responsable válido.');
        if (cannot('operations.view') && ! in_array($userId, (new TeamScopeService())->userIdsInScope($this->currentUserId()), true)) {
            throw PageNotFoundException::forPageNotFound('Responsable fuera del alcance de tu equipo.');
        }

        return $this->execute(function () use ($id, $userId): void {
            service('citizenOperations')->assign($id, $userId);
            (new SlaClockService())->assigned($id, $userId);
        }, 'Atención asignada.');
    }

    public function changeStatus(int $id)
    {
        $this->requireAction($id, 'operations.update');
        $status = strtoupper(trim((string) $this->request->getPost('status')));

        return $this->execute(function () use ($id, $status): void {
            service('citizenOperations')->changeStatus($id, $status);
            if (in_array($status, ['RESOLVED', 'CLOSED'], true)) {
                (new SlaClockService())->resolved($id, $this->currentUserId() ?: null);
            }
        }, 'Estado actualizado.');
    }

    public function changePriority(int $id) { $this->requireAction($id, 'operations.update'); return $this->execute(fn () => service('citizenOperations')->changePriority($id, strtoupper(trim((string) $this->request->getPost('priority')))), 'Prioridad actualizada.'); }

    public function markResponded(int $id)
    {
        $this->requireAction($id, 'operations.close');
        return $this->execute(function () use ($id): void {
            service('citizenOperations')->markResponded($id);
            (new SlaClockService())->firstResponse($id, $this->currentUserId() ?: null);
        }, 'Primera respuesta registrada.');
    }

    /** @return int[]|null */
    private function scopeUserIds(): ?array
    {
        if (can('operations.view')) return null;
        if (can('operations.view_team')) return (new TeamScopeService())->userIdsInScope($this->currentUserId());
        return [$this->currentUserId()];
    }

    private function scopeTitle(): string
    {
        if (can('operations.view')) return 'Citizen Operations';
        if (can('operations.view_team')) return 'Atenciones de mi equipo';
        return 'Mi trabajo';
    }

    private function assignableUsers(object $query): array
    {
        if (cannot('operations.assign')) return [];
        $users = $query->users();
        if (can('operations.view')) return $users;
        $allowed = (new TeamScopeService())->userIdsInScope($this->currentUserId());
        return array_values(array_filter($users, static fn (array $user): bool => in_array((int) ($user['id'] ?? 0), $allowed, true)));
    }

    private function requireAccess(int $id): void
    {
        if (! (new OwnershipService())->canAccessWorkItem($this->currentUserId(), $id)) throw PageNotFoundException::forPageNotFound('Atención no encontrada o fuera de tu alcance.');
    }

    private function requireAction(int $id, string $permission): void
    {
        if (! (new OwnershipService())->canActOnWorkItem($this->currentUserId(), $id, $permission)) throw PageNotFoundException::forPageNotFound('Atención no encontrada o acción no autorizada.');
    }

    private function currentUserId(): int { return (int) session()->get('admin_user_id'); }

    private function execute(callable $operation, string $success)
    {
        try { $operation(); return redirect()->back()->with('success', $success); }
        catch (Throwable $error) { log_message('error', 'Citizen Operations action failed: ' . $error->getMessage()); return redirect()->back()->with('error', $error->getMessage()); }
    }
}
