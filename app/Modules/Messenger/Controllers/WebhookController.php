<?php

namespace Modules\Messenger\Controllers;

use App\Controllers\BaseController;
use Modules\Engagement\Services\PublicEngagementProcessor;
use Modules\Messenger\Models\WebhookEventModel;
use Modules\Messenger\Services\MessengerWebhookProcessor;
use Throwable;

class WebhookController extends BaseController
{
    public function verify()
    {
        $mode = $this->request->getGet('hub_mode') ?? $this->request->getGet('hub.mode');
        $token = $this->request->getGet('hub_verify_token') ?? $this->request->getGet('hub.verify_token');
        $challenge = $this->request->getGet('hub_challenge') ?? $this->request->getGet('hub.challenge');

        if ($mode === 'subscribe' && $token === env('MESSENGER_VERIFY_TOKEN')) {
            return $this->response->setStatusCode(200)->setBody($challenge);
        }

        return $this->response->setStatusCode(403)->setBody('Invalid verify token');
    }

    public function receive()
    {
        $payload = $this->request->getJSON(true);

        if (!$payload) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON',
            ]);
        }

        if (($payload['object'] ?? null) !== 'page') {
            return $this->response->setStatusCode(200)->setJSON([
                'status' => 'ignored',
            ]);
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['messaging'] ?? [] as $event) {
                $this->storeAndProcessEvent($event);
            }

            $pageId = (string) ($entry['id'] ?? '');

            foreach ($entry['changes'] ?? [] as $change) {
                $this->processPageChange($pageId, $change);
            }
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'ok',
        ]);
    }

    private function storeAndProcessEvent(array $event): void
    {
        $senderId = $event['sender']['id'] ?? null;
        $recipientId = $event['recipient']['id'] ?? null;

        $eventType = 'unknown';

        if (isset($event['message'])) {
            $eventType = 'message';
        }

        if (isset($event['postback'])) {
            $eventType = 'postback';
        }

        $eventModel = new WebhookEventModel();

        $eventId = $eventModel->insert([
            'platform' => 'facebook',
            'event_type' => $eventType,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'raw_payload' => json_encode($event),
            'processed' => 0,
        ]);

        if (!$senderId) {
            return;
        }

        $processor = new MessengerWebhookProcessor();

        if ($eventType === 'message') {
            $processor->processIncomingMessage($senderId, $event);
        }

        if ($eventType === 'postback') {
            $processor->processPostback($senderId, $event);
        }

        $eventModel->update($eventId, [
            'processed' => 1,
            //'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function processPageChange(string $pageId, array $change): void
    {
        try {
            (new PublicEngagementProcessor())->process($pageId, $change);
        } catch (Throwable $error) {
            log_message(
                'error',
                'Public Engagement webhook error: ' . $error->getMessage()
            );
        }
    }
}
