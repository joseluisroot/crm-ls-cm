<?php

namespace Modules\Operations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Response\Application\QuickActionCatalog;
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

        return view('Modules\Operations\Views\index', [
            'title' => 'Citizen Operations',
            'summary' => $query->summary(),
            'items' => $query->items($status, $priority, $limit),
            'statuses' => $query->statuses(),
            'priorities' => $query->priorities(),
            'status' => $status,
            'priority' => $priority,
            'limit' => max(1, min($limit, 200)),
        ]);
    }

    public function show(int $id)
    {
        $query = service('operationsDetailQuery');
        $item = $query->find($id);

        if (! $item) {
            throw PageNotFoundException::forPageNotFound('Work Item no encontrado.');
        }

        $citizenCard = null;
        if (! empty($item['citizen_id'])) {
            $citizenCard = service('citizenCard')->get((int) $item['citizen_id']);
        }

        $catalog = new QuickActionCatalog();
        $authorName = $item['source']['author_name'] ?? $item['title'] ?? null;
        $quickActions = array_map(
            static fn (array $action): array => $catalog->personalize($action, $authorName),
            $catalog->all(),
        );

        return view('Modules\Operations\Views\show', [
            'title' => 'Work Item #' . $id,
            'item' => $item,
            'citizenCard' => $citizenCard,
            'timeline' => $query->timeline($id),
            'users' => $query->users(),
            'statuses' => $query->statuses(),
            'priorities' => $query->priorities(),
            'responseDraft' => (new ResponseDraftService(db_connect()))->findForWorkItem($id),
            'quickActions' => $quickActions,
        ]);
    }

    public function importPending()
    {
        $count = service('facebookCommentWorkItemAdapter')->importPending(500);
        return redirect()->to(site_url('admin/operations'))
            ->with('success', $count . ' comentarios fueron sincronizados con Citizen Operations.');
    }

    public function assign(int $id)
    {
        $userId = (int) $this->request->getPost('assigned_user_id');
        if ($userId <= 0) return redirect()->back()->with('error', 'Selecciona un responsable válido.');
        return $this->execute(fn () => service('citizenOperations')->assign($id, $userId), 'Work Item asignado.');
    }

    public function changeStatus(int $id)
    {
        $status = strtoupper(trim((string) $this->request->getPost('status')));
        return $this->execute(fn () => service('citizenOperations')->changeStatus($id, $status), 'Estado actualizado.');
    }

    public function changePriority(int $id)
    {
        $priority = strtoupper(trim((string) $this->request->getPost('priority')));
        return $this->execute(fn () => service('citizenOperations')->changePriority($id, $priority), 'Prioridad actualizada.');
    }

    public function markResponded(int $id)
    {
        return $this->execute(fn () => service('citizenOperations')->markResponded($id), 'Primera respuesta registrada.');
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
