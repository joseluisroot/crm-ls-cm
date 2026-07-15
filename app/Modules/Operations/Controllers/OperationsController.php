<?php

namespace Modules\Operations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Authorization\Application\OwnershipService;
use Modules\Response\Application\QuickActionCatalog;
use Modules\Response\Application\ResponseContextResolver;
use Modules\Response\Application\ResponseDraftService;
use Throwable;

final class OperationsController extends BaseController
{
    public function index()
    {
        $status = trim((string) $this->request->getGet('status')) ?: null;
        $priority = trim((string) $this->request->getGet('priority')) ?: null;
        $limit = (int) ($this->request->getGet('limit') ?: 100);
        $query = service('operationsQueueQuery');
        $items = $query->items($status, $priority, $limit);

        if (can('operations.view_own') && cannot('operations.view')) {
            $userId = $this->currentUserId();
            $allowedIds = array_map('intval', array_column(
                db_connect()->table('work_items')->select('id')->where('assigned_user_id', $userId)->get()->getResultArray(),
                'id'
            ));
            $items = array_values(array_filter($items, static fn (array $item): bool => in_array((int) ($item['id'] ?? 0), $allowedIds, true)));
        }

        return view('Modules\Operations\Views\index', [
            'title' => can('operations.view_own') && cannot('operations.view') ? 'Mis atenciones' : 'Citizen Operations',
            'summary' => $query->summary(),
            'items' => $items,
            'statuses' => $query->statuses(),
            'priorities' => $query->priorities(),
            'status' => $status,
            'priority' => $priority,
            'limit' => max(1, min($limit, 200)),
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
        $quickActions = array_map(
            static fn (array $action): array => $catalog->personalize($action, $authorName),
            $catalog->forChannel($channel),
        );
        $db = db_connect();

        return view('Modules\Operations\Views\show', [
            'title' => 'Atención #' . $id,
            'item' => $item,
            'citizenCard' => $citizenCard,
            'timeline' => $query->timeline($id),
            'users' => can('operations.assign') ? $query->users() : [],
            'statuses' => $query->statuses(),
            'priorities' => $query->priorities(),
            'responseDraft' => (new ResponseDraftService($db))->findForWorkItem($id),
            'responseCapability' => (new ResponseContextResolver($db))->capability($id),
            'responses' => $db->table('citizen_responses')->where('work_item_id', $id)->orderBy('id', 'DESC')->get()->getResultArray(),
            'quickActions' => $quickActions,
        ]);
    }

    public function importPending()
    {
        $count = service('facebookCommentWorkItemAdapter')->importPending(500);
        return redirect()->to(site_url('admin/operations'))->with('success', $count . ' comentarios fueron sincronizados con Citizen Operations.');
    }

    public function assign(int $id)
    {
        $userId = (int) $this->request->getPost('assigned_user_id');
        if ($userId <= 0) return redirect()->back()->with('error', 'Selecciona un responsable válido.');
        return $this->execute(fn () => service('citizenOperations')->assign($id, $userId), 'Atención asignada.');
    }

    public function changeStatus(int $id)
    {
        $this->requireAction($id, 'operations.update');
        return $this->execute(fn () => service('citizenOperations')->changeStatus($id, strtoupper(trim((string) $this->request->getPost('status')))), 'Estado actualizado.');
    }

    public function changePriority(int $id)
    {
        $this->requireAction($id, 'operations.update');
        return $this->execute(fn () => service('citizenOperations')->changePriority($id, strtoupper(trim((string) $this->request->getPost('priority')))), 'Prioridad actualizada.');
    }

    public function markResponded(int $id)
    {
        $this->requireAction($id, 'operations.close');
        return $this->execute(fn () => service('citizenOperations')->markResponded($id), 'Primera respuesta registrada.');
    }

    private function requireAccess(int $id): void
    {
        if (! (new OwnershipService())->canAccessWorkItem($this->currentUserId(), $id)) {
            throw PageNotFoundException::forPageNotFound('Atención no encontrada o fuera de tu alcance.');
        }
    }

    private function requireAction(int $id, string $permission): void
    {
        if (! (new OwnershipService())->canActOnWorkItem($this->currentUserId(), $id, $permission)) {
            throw PageNotFoundException::forPageNotFound('Atención no encontrada o acción no autorizada.');
        }
    }

    private function currentUserId(): int
    {
        return (int) session()->get('admin_user_id');
    }

    private function execute(callable $operation, string $success)
    {
        try {
            $operation();
            return redirect()->back()->with('success', $success);
        } catch (Throwable $error) {
            log_message('error', 'Citizen Operations action failed: ' . $error->getMessage());
            return redirect()->back()->with('error', $error->getMessage());
        }
    }
}
