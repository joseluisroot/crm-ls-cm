<?php

namespace App\Controllers\Webhooks;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class MessengerController extends BaseController
{
    public function verify()
    {
        $mode = $this->request->getGet('hub_mode') ?? $this->request->getGet('hub.mode');
        $token = $this->request->getGet('hub_verify_token') ?? $this->request->getGet('hub.verify_token');
        $challenge = $this->request->getGet('hub_challenge') ?? $this->request->getGet('hub.challenge');

        if ($mode === 'subscribe' && $token === env('MESSENGER_VERIFY_TOKEN')) {
            return $this->response
                ->setStatusCode(200)
                ->setBody($challenge);
        }

        return $this->response
            ->setStatusCode(403)
            ->setBody('Invalid verify token');
    }

    public function receive()
    {
        $payload = $this->request->getJSON(true);

        if (!$payload) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON payload',
            ]);
        }

        log_message('info', 'Messenger webhook payload: ' . json_encode($payload));

        if (($payload['object'] ?? null) !== 'page') {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'ignored',
            ]);
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['messaging'] ?? [] as $event) {
                $this->processMessagingEvent($event);
            }
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'ok',
        ]);
    }

    private function processMessagingEvent(array $event): void
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

        $webhookEventModel = new WebhookEventModel();

        $webhookEventModel->insert([
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

        if ($eventType === 'message') {
            $this->handleIncomingMessage($senderId, $event);
        }

        if ($eventType === 'postback') {
            $this->handlePostback($senderId, $event);
        }
    }

    private function handleIncomingMessage(string $senderId, array $event): void
    {
        $message = $event['message'] ?? [];

        if (isset($message['is_echo']) && $message['is_echo'] === true) {
            return;
        }

        $text = $message['text'] ?? '[Mensaje sin texto]';

        $citizen = $this->findOrCreateCitizen($senderId);
        $conversation = $this->findOrCreateConversation((int) $citizen['id']);

        $messageModel = new MessageModel();

        $messageModel->insert([
            'conversation_id' => $conversation['id'],
            'direction' => 'inbound',
            'message_type' => isset($message['attachments']) ? 'attachment' : 'text',
            'body' => $text,
            'raw_payload' => json_encode($event),
            'sentiment' => 'pending',
            'category' => 'pending',
            'priority' => 'normal',
        ]);

        (new ConversationModel())
            ->update($conversation['id'], [
                'last_message_at' => date('Y-m-d H:i:s'),
            ]);

        $this->sendWelcomeMenu($senderId);
    }

    private function handlePostback(string $senderId, array $event): void
    {
        $payload = $event['postback']['payload'] ?? null;
        $title = $event['postback']['title'] ?? null;

        $citizen = $this->findOrCreateCitizen($senderId);
        $conversation = $this->findOrCreateConversation((int) $citizen['id']);

        (new MessageModel())->insert([
            'conversation_id' => $conversation['id'],
            'direction' => 'inbound',
            'message_type' => 'postback',
            'body' => $title ?: $payload,
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
        $message = "Hola 👋 Gracias por comunicarte con Lupita Serrano.\n\n"
            . "Para atenderte mejor, responde con una opción:\n\n"
            . "1️⃣ Saludos o felicitaciones\n"
            . "2️⃣ Reportar una necesidad de tu comunidad\n"
            . "3️⃣ Solicitar apoyo o gestión\n"
            . "4️⃣ Proponer una idea o proyecto\n"
            . "5️⃣ Información sobre actividades\n"
            . "6️⃣ Otro tema\n\n"
            . "Tu mensaje será revisado por nuestro equipo.";

        (new MessengerService())->sendTextMessage($senderId, $message);
    }
}
