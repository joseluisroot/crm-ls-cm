<?php

declare(strict_types=1);

namespace Modules\Response\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Modules\Authorization\Application\OwnershipService;
use Modules\Operations\Application\SlaClockService;
use Modules\Response\Application\ResponseContextResolver;
use Modules\Response\Application\ResponseDispatcher;
use Modules\Response\Application\ResponseDraftService;
use Modules\Response\Infrastructure\FacebookCommentResponseAdapter;
use Modules\Response\Infrastructure\MessengerResponseAdapter;
use Throwable;

final class ResponseDraftController extends BaseController
{
    public function save(int $workItemId)
    {
        $this->requireReplyAccess($workItemId);

        try {
            $userId = $this->currentUserId() ?: null;
            (new ResponseDraftService(db_connect()))->save(
                workItemId: $workItemId,
                userId: $userId,
                channel: (string) $this->request->getPost('channel'),
                body: (string) $this->request->getPost('body'),
            );
            (new SlaClockService())->firstDraft($workItemId, $userId);
            return redirect()->back()->with('success', 'Borrador guardado correctamente.');
        } catch (Throwable $error) {
            log_message('error', 'Response draft save failed: ' . $error->getMessage());
            return redirect()->back()->with('error', $error->getMessage());
        }
    }

    public function send(int $workItemId)
    {
        $this->requireReplyAccess($workItemId);

        try {
            $userId = $this->currentUserId() ?: null;
            $result = $this->dispatcher()->dispatch(
                $workItemId,
                $userId,
                (string) $this->request->getPost('body'),
            );
            (new SlaClockService())->firstResponse($workItemId, $userId);
            return redirect()->back()->with('success', sprintf('Respuesta enviada por %s. ID Meta: %s', $result['channel'], $result['external_response_id']));
        } catch (Throwable $error) {
            log_message('error', 'Citizen response dispatch failed: ' . $error->getMessage());
            return redirect()->back()->with('error', 'No se envió la respuesta: ' . $error->getMessage());
        }
    }

    private function requireReplyAccess(int $workItemId): void
    {
        if (! (new OwnershipService())->canActOnWorkItem($this->currentUserId(), $workItemId, 'operations.reply')) {
            throw PageNotFoundException::forPageNotFound('Atención no encontrada o fuera de tu alcance.');
        }
    }

    private function currentUserId(): int
    {
        return (int) session()->get('admin_user_id');
    }

    private function dispatcher(): ResponseDispatcher
    {
        $db = db_connect();
        return new ResponseDispatcher(
            $db,
            new ResponseContextResolver($db),
            new ResponseDraftService($db),
            service('citizenOperations'),
            [new FacebookCommentResponseAdapter(), new MessengerResponseAdapter()],
        );
    }
}
