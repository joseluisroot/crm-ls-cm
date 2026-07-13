<?php

declare(strict_types=1);

namespace Modules\Response\Controllers;

use App\Controllers\BaseController;
use Modules\Response\Application\ResponseDraftService;
use Throwable;

final class ResponseDraftController extends BaseController
{
    public function save(int $workItemId)
    {
        try {
            (new ResponseDraftService(db_connect()))->save(
                workItemId: $workItemId,
                userId: session()->get('admin_user_id') ? (int) session()->get('admin_user_id') : null,
                channel: (string) $this->request->getPost('channel'),
                body: (string) $this->request->getPost('body'),
            );

            return redirect()->back()->with('success', 'Borrador guardado correctamente.');
        } catch (Throwable $error) {
            log_message('error', 'Response draft save failed: ' . $error->getMessage());
            return redirect()->back()->with('error', $error->getMessage());
        }
    }
}
