<?php

namespace Modules\Notification\Controllers;

use App\Controllers\BaseController;
use Modules\Notification\Models\NotificationModel;

class NotificationsController extends BaseController
{
    public function index()
    {
        $query = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $channel = trim((string) $this->request->getGet('channel'));
        $perPage = (int) $this->request->getGet('per_page');
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $model = new NotificationModel();

        if ($query !== '') {
            $model->groupStart()
                ->like('subject', $query)
                ->orLike('body', $query)
                ->orLike('recipient_id', $query)
                ->groupEnd();
        }

        if ($status !== '') {
            $model->where('status', $status);
        }

        if ($channel !== '') {
            $model->where('channel', $channel);
        }

        $total = $model->countAllResults(false);
        $pageCount = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pageCount);

        return view('Modules\Notification\Views\index', [
            'title' => 'Notificaciones',
            'notifications' => $model->orderBy('created_at', 'DESC')->paginate($perPage, 'default', $page),
            'filters' => [
                'q' => $query,
                'status' => $status,
                'channel' => $channel,
                'per_page' => $perPage,
            ],
            'total' => $total,
            'page' => $page,
            'pageCount' => $pageCount,
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ]);
    }

    public function markAsRead($id)
    {
        $notificationId = (int) $id;
        $model = new NotificationModel();

        if (! $model->find($notificationId)) {
            return redirect()->back()->with('error', 'La notificación seleccionada no existe.');
        }

        $model->update($notificationId, [
            'status' => 'read',
            'read_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }
}
