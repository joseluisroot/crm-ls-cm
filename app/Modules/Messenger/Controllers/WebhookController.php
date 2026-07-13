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
        $startedAt = microtime(true);
        $payload = $this->request->getJSON(true);

        if (! $payload) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON',
            ]);
        }

        $capture = service('integrationEventCapture');
        $envelope = $capture->capture(
            source: 'FACEBOOK',
            eventType: $this->detectEnvelopeType($payload),
            payload: $payload,
            headers: $this->request->headers(),
            externalEventId: $this->externalEventId($payload),
            endpoint: (string) $this->request->getUri(),
            requestIp: $this->request->getIPAddress(),
            signature: $this->request->getHeaderLine('X-Hub-Signature-256') ?: null,
        );

        $trace = [[
            'step' => 'integration_event_received',
            'at' => date(DATE_ATOM),
        ]];

        try {
            if (($payload['object'] ?? null) !== 'page') {
                $trace[] = ['step' => 'payload_ignored', 'reason' => 'object_not_page', 'at' => date(DATE_ATOM)];
                $capture->markProcessed((int) $envelope['id'], $startedAt, $trace);

                return $this->response->setStatusCode(200)->setJSON(['status' => 'ignored']);
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

            $trace[] = ['step' => 'webhook_processed', 'at' => date(DATE_ATOM)];
            $capture->markProcessed((int) $envelope['id'], $startedAt, $trace);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 'ok',
                'correlation_id' => $envelope['correlation_id'],
            ]);
        } catch (Throwable $error) {
            $trace[] = ['step' => 'webhook_failed', 'message' => $error->getMessage(), 'at' => date(DATE_ATOM)];
            $capture->markFailed((int) $envelope['id'], $startedAt, $error->getMessage(), $trace);
            log_message('error', 'Meta webhook failed [' . $envelope['correlation_id'] . ']: ' . $error->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'correlation_id' => $envelope['correlation_id'],
            ]);
        }
    }

    private function storeAndProcessEvent(array $event): void
    {
        $senderId = $event['sender']['id'] ?? null;
        $recipientId = $event['recipient']['id'] ?? null;
        $eventType = isset($event['message']) ? 'message' : (isset($event['postback']) ? 'postback' : 'unknown');
        $eventModel = new WebhookEventModel();

        $eventId = $eventModel->insert([
            'platform' => 'facebook',
            'event_type' => $eventType,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'raw_payload' => json_encode($event),
            'processed' => 0,
        ]);

        if (! $senderId) return;

        $processor = new MessengerWebhookProcessor();
        if ($eventType === 'message') $processor->processIncomingMessage($senderId, $event);
        if ($eventType === 'postback') $processor->processPostback($senderId, $event);

        $eventModel->update($eventId, ['processed' => 1]);
    }

    private function processPageChange(string $pageId, array $change): void
    {
        (new PublicEngagementProcessor())->process($pageId, $change);
    }

    private function detectEnvelopeType(array $payload): string
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            if (! empty($entry['messaging'])) return 'MESSAGING_WEBHOOK';
            if (! empty($entry['changes'])) return 'PAGE_CHANGE_WEBHOOK';
        }

        return 'META_WEBHOOK';
    }

    private function externalEventId(array $payload): ?string
    {
        $entry = $payload['entry'][0] ?? [];
        $event = $entry['messaging'][0] ?? [];
        $change = $entry['changes'][0]['value'] ?? [];

        return $event['message']['mid']
            ?? $event['postback']['mid']
            ?? $change['comment_id']
            ?? $change['post_id']
            ?? null;
    }
}
