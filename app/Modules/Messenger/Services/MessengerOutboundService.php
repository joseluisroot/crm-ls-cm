<?php

namespace Modules\Messenger\Services;

use Modules\Conversations\Models\MessageModel;

class MessengerOutboundService
{
    public function sendSuggestedReply(string $recipientId, int $messageId): bool
    {
        $messageModel = new MessageModel();
        $message = $messageModel->find($messageId);

        if (!$message) {
            return false;
        }

        $rawPayload = [];

        if (!empty($message['raw_payload'])) {
            $rawPayload = json_decode($message['raw_payload'], true) ?: [];
        }

        $quickReplies = $rawPayload['quick_replies'] ?? [];

        if (!empty($quickReplies)) {
            $sent = (new MessengerService())->sendQuickReplies(
                $recipientId,
                $message['body'],
                $quickReplies
            );
        } else {
            $sent = (new MessengerService())->sendTextMessage(
                $recipientId,
                $message['body']
            );
        }

        if ($sent) {
            $messageModel->update($messageId, [
                'sent_status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
                'delivery_error' => null,
            ]);

            return true;
        }

        $messageModel->update($messageId, [
            'sent_status' => 'failed',
            'delivery_error' => 'No se pudo enviar el mensaje a Messenger.',
        ]);

        return false;
    }
}