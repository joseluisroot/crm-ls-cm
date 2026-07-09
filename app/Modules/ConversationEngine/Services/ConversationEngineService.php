<?php

namespace Modules\ConversationEngine\Services;

use Modules\Citizens\Models\CitizenModel;
use Modules\ConversationEngine\DTO\IncomingMessageDTO;
use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;
use Modules\CaseEngine\Services\CaseEngineService;

class ConversationEngineService
{
    public function handleIncomingMessage(IncomingMessageDTO $dto): array
    {
        $citizen = $this->findOrCreateCitizen($dto);
        $conversation = $this->findOrCreateConversation((int) $citizen['id'], $dto->channel);

        $categorySlug = $this->detectInitialCategory($dto->text);

        // Mensaje recibido del ciudadano
        $messageId = (new MessageModel())->insert([
            'conversation_id' => $conversation['id'],
            'direction'      => 'inbound',
            'message_type'    => $dto->messageType,
            'body'            => $dto->text ?? '[Mensaje sin texto]',
            'raw_payload'     => json_encode($dto->rawPayload),
            'sentiment'       => 'pending',
            'category'        => $categorySlug,
            'priority'        => 'normal',
        ]);

        (new ConversationModel())->update($conversation['id'], [
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);

        $caseId = null;
        $caseEngine = new CaseEngineService();

        if ($caseEngine->shouldCreateCase($categorySlug)) {
            $caseId = $caseEngine->createCaseFromMessage(
                citizenId: (int) $citizen['id'],
                categorySlug: $categorySlug,
                messageText: $dto->text ?? ''
            );
        }

        $outboundMessageId = null;
        $suggestedReply = $this->getSuggestedReply($dto->text);

        // Mensaje de respuesta del sistema
        if ($suggestedReply) {
            $outboundMessageId = (new MessageModel())->insert([
                'conversation_id' => $conversation['id'],
                'direction'      => 'outbound',
                'message_type'    => 'text',
                'body'            => $suggestedReply,
                'raw_payload'     => json_encode([
                    'generated_by' => 'conversation_engine',
                    'auto_reply'   => true,
                    'sent'         => false,
                ]),
                'sentiment'      => 'system',
                'category'       => $categorySlug,
                'priority'       => 'normal',
                'sent_status'    => 'suggested',
                'sent_at'        => null,
                'delivery_error' => null,
            ]);
        }

        return [
            'citizen'             => $citizen,
            'conversation'        => $conversation,
            'message_id'          => $messageId,
            'outbound_message_id' => $outboundMessageId,
            'case_id'             => $caseId,
            'category'            => $categorySlug,
            'suggested_reply'     => $suggestedReply,
        ];
    }

    private function findOrCreateCitizen(IncomingMessageDTO $dto): array
    {
        $citizenModel = new CitizenModel();

        $citizen = $citizenModel
            ->where('facebook_id', $dto->externalUserId)
            ->first();

        if ($citizen) {
            return $citizen;
        }

        $id = $citizenModel->insert([
            'facebook_id' => $dto->externalUserId,
            'name'        => 'Usuario ' . ucfirst($dto->channel) . ' ' . substr($dto->externalUserId, -6),
            'status'      => 'active',
        ]);

        return $citizenModel->find($id);
    }

    private function findOrCreateConversation(int $citizenId, string $channel): array
    {
        $conversationModel = new ConversationModel();

        $conversation = $conversationModel
            ->where('citizen_id', $citizenId)
            ->where('channel', $channel)
            ->where('status', 'open')
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $id = $conversationModel->insert([
            'citizen_id'      => $citizenId,
            'channel'         => $channel,
            'status'          => 'open',
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);

        return $conversationModel->find($id);
    }

    private function detectInitialCategory(?string $text): string
    {
        $text = trim((string) $text);

        return match ($text) {
            '1' => 'saludos-felicitaciones',
            '2' => 'necesidad-comunitaria',
            '3' => 'solicitud-apoyo',
            '4' => 'propuesta-ciudadana',
            '5' => 'informacion-actividades',
            '6' => 'otro',
            default => 'pending',
        };
    }

    private function getSuggestedReply(?string $text): ?string
    {
        $text = trim((string) $text);

        return match ($text) {
            '1' => 'Gracias por tu saludo. Nos alegra mucho recibir tu mensaje.',
            '2' => 'Gracias por reportar una necesidad de tu comunidad. Por favor indícanos el municipio, comunidad y una breve descripción.',
            '3' => 'Gracias por escribirnos. Por favor cuéntanos qué tipo de apoyo o gestión necesitas.',
            '4' => 'Gracias por compartir tu idea. Por favor descríbenos tu propuesta.',
            '5' => 'Gracias por tu interés. Nuestro equipo podrá compartirte información sobre próximas actividades.',
            '6' => 'Gracias por escribirnos. Por favor déjanos tu mensaje y nuestro equipo lo revisará.',
            default => null,
        };
    }
}