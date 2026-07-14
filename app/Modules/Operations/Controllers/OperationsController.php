<?php

namespace Modules\Operations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
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
            'users' => $query->users(),
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
        return $this->execute(fn () => service('citizenOperations')->changeStatus($id, strtoupper(trim((string) $this->request->getPost('status')))), 'Estado actualizado.');
    }

    public function changePriority(int $id)
    {
        return $this->execute(fn () => service('citizenOperations')->changePriority($id, strtoupper(trim((string) $this->request->getPost('priority')))), 'Prioridad actualizada.');
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
