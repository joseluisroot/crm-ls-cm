<?php

namespace Modules\Notification\Controllers;

use App\Controllers\BaseController;
use Modules\Notification\Models\NotificationModel;

class NotificationsController extends BaseController
{
    public function index()
    {
        $notifications = (new NotificationModel())
            ->orderBy('created_at', 'DESC')
            ->findAll(50);

        return view('Modules\Notification\Views\index', [
            'title' => 'Notificaciones',
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        (new NotificationModel())->update((int) $id, [
            'status' => 'read',
            'read_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }
}