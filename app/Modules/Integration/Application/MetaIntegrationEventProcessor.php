<?php

declare(strict_types=1);

namespace Modules\Integration\Application;

use Modules\Engagement\Services\PublicEngagementProcessor;
use Modules\Messenger\Models\WebhookEventModel;
use Modules\Messenger\Services\MessengerWebhookProcessor;

final class MetaIntegrationEventProcessor
{
    public function process(array $payload): array
    {
        $trace = [];

        if (($payload['object'] ?? null) !== 'page') {
            return [['step' => 'payload_ignored', 'reason' => 'object_not_page', 'at' => date(DATE_ATOM)]];
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['messaging'] ?? [] as $event) {
                $this->processMessagingEvent($event);
                $trace[] = ['step' => 'messaging_event_processed', 'at' => date(DATE_ATOM)];
            }

            $pageId = (string) ($entry['id'] ?? '');
            foreach ($entry['changes'] ?? [] as $change) {
                (new PublicEngagementProcessor())->process($pageId, $change);
                $trace[] = ['step' => 'page_change_processed', 'page_id' => $pageId, 'at' => date(DATE_ATOM)];
            }
        }

        return $trace;
    }

    private function processMessagingEvent(array $event): void
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
            'raw_payload' => json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'processed' => 0,
        ]);

        if (! $senderId) {
            return;
        }

        $processor = new MessengerWebhookProcessor();
        if ($eventType === 'message') {
            $processor->processIncomingMessage($senderId, $event);
        }
        if ($eventType === 'postback') {
            $processor->processPostback($senderId, $event);
        }

        $eventModel->update($eventId, ['processed' => 1]);
    }
}
