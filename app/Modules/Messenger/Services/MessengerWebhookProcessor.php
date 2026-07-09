<?php

namespace Modules\Messenger\Services;

use Modules\Citizens\Models\CitizenModel;
use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;

class MessengerWebhookProcessor
{
    public function processIncomingMessage(string $senderId, array $event): void
    {
        $message = $event['message'] ?? [];

        if (($message['is_echo'] ?? false) === true) {
            return;
        }

        $text = $message['text'] ?? null;
        $payload = $message['quick_reply']['payload'] ?? null;

        $dto = new \Modules\ConversationEngine\DTO\IncomingMessageDTO(
            channel: 'messenger',
            externalUserId: $senderId,
            text: $text,
            messageType: isset($message['attachments']) ? 'attachment' : 'text',
            rawPayload: $event,
            payload: $payload
        );

        $result = (new \Modules\ConversationEngine\Services\ConversationEngineService())
            ->handleIncomingMessage($dto);

        if (!empty($result['outbound_message_id'])) {
            (new \Modules\Messenger\Services\MessengerOutboundService())
                ->sendSuggestedReply($senderId, (int) $result['outbound_message_id']);
        }

        if (!empty($result['suggested_reply'])) {
            // Por ahora solo guardamos la lógica.
            // Luego activaremos el envío real con MessengerService.
            log_message('info', 'Respuesta sugerida: ' . $result['suggested_reply']);
        }
    }

    public function processPostback(string $senderId, array $event): void
    {
        $payload = $event['postback']['payload'] ?? 'unknown';
        $title = $event['postback']['title'] ?? $payload;

        $citizen = $this->findOrCreateCitizen($senderId);
        $conversation = $this->findOrCreateConversation((int) $citizen['id']);

        (new MessageModel())->insert([
            'conversation_id' => $conversation['id'],
            'direction' => 'inbound',
            'message_type' => 'postback',
            'body' => $title,
            'raw_payload' => json_encode($event),
            'sentiment' => 'pending',
            'category' => $payload,
            'priority' => 'normal',
        ]);
    }

    private function findOrCreateCitizen(string $facebookId): array
    {
        $citizenModel = new CitizenModel();

        $citizen = $citizenModel
            ->where('facebook_id', $facebookId)
            ->first();

        if ($citizen) {
            return $citizen;
        }

        $id = $citizenModel->insert([
            'facebook_id' => $facebookId,
            'name' => 'Usuario Messenger ' . substr($facebookId, -6),
            'status' => 'active',
        ]);

        return $citizenModel->find($id);
    }

    private function findOrCreateConversation(int $citizenId): array
    {
        $conversationModel = new ConversationModel();

        $conversation = $conversationModel
            ->where('citizen_id', $citizenId)
            ->where('channel', 'messenger')
            ->where('status', 'open')
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $id = $conversationModel->insert([
            'citizen_id' => $citizenId,
            'channel' => 'messenger',
            'status' => 'open',
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);

        return $conversationModel->find($id);
    }

    private function sendWelcomeMenu(string $senderId): void
    {
        $text = "Hola 👋 Gracias por comunicarte con Lupita Serrano.\n\n"
            . "Para atenderte mejor, responde con una opción:\n\n"
            . "1️⃣ Saludos o felicitaciones\n"
            . "2️⃣ Reportar una necesidad de tu comunidad\n"
            . "3️⃣ Solicitar apoyo o gestión\n"
            . "4️⃣ Proponer una idea o proyecto\n"
            . "5️⃣ Información sobre actividades\n"
            . "6️⃣ Otro tema\n\n"
            . "Tu mensaje será revisado por nuestro equipo.";

        (new MessengerService())->sendTextMessage($senderId, $text);
    }
}