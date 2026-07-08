<?php

namespace Modules\Messenger\Controllers;

use App\Controllers\BaseController;
use Modules\Messenger\Models\WebhookEventModel;

class MessengerEventsController extends BaseController
{
    public function index()
    {
        $model = new WebhookEventModel();

        return view('Modules\Messenger\Views\events', [
            'title' => 'Eventos Messenger',
            'events' => $model->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $model->pager,
        ]);
    }
}