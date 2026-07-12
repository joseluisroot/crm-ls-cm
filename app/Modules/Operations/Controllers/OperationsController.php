<?php

namespace Modules\Operations\Controllers;

use App\Controllers\BaseController;

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

    public function importPending()
    {
        $count = service('facebookCommentWorkItemAdapter')->importPending(500);

        return redirect()->to(site_url('admin/operations'))
            ->with('success', $count . ' comentarios fueron sincronizados con Citizen Operations.');
    }
}
