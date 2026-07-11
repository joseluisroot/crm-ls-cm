<?php

namespace Modules\ConversationEngine\Services;

use Modules\Citizens\Models\CitizenModel;
use Modules\ConversationEngine\DTO\IncomingMessageDTO;
use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;
use Modules\CaseEngine\Services\CaseEngineService;
use Modules\Flow\Services\FlowEngineService;

class ConversationEngineService
{
    public function handleIncomingMessage(IncomingMessageDTO $dto): array
    {
        $citizen = $this->findOrCreateCitizen($dto);
        $conversation = $this->findOrCreateConversation((int) $citizen['id'], $dto->channel);

        $categorySlug = $this->detectInitialCategory($dto->text, $dto->payload);

        if ($dto->externalMessageId) {
            $existingMessage = (new MessageModel())
                ->where('external_message_id', $dto->externalMessageId)
                ->first();

            if ($existingMessage) {
                log_message('info', 'Mensaje duplicado ignorado: ' . $dto->externalMessageId);

                return [
                    'citizen' => $citizen,
                    'conversation' => $conversation,
                    'message_id' => $existingMessage['id'],
                    'outbound_message_id' => null,
                    'case_id' => null,
                    'category' => 'duplicate',
                ];
            }
        }

        $messageId = (new MessageModel())->insert([
            'conversation_id' => $conversation['id'],
            'direction'      => 'inbound',
            'message_type'    => $dto->messageType,
            'external_message_id' => $dto->externalMessageId,
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

        $workflowConfig = config('Workflow');

        if (
            !$workflowConfig->dynamicEngineEnabled
            && $caseEngine->shouldCreateCase($categorySlug)
        ) {
            $caseId = $caseEngine->createCaseFromMessage(
                citizenId: (int) $citizen['id'],
                categorySlug: $categorySlug,
                messageText: $dto->text ?? ''
            );
        }

        $flowResult = (new \Modules\Flow\Services\FlowAdapterService())
            ->handle([
                'citizen' => $citizen,
                'conversation' => $conversation,
                'message_id' => $messageId,
                'case_id' => $caseId,
                'category' => $categorySlug,
                'text' => $dto->text,
                'payload' => $dto->payload,
            ]);

        $outboundMessageId = $flowResult->outboundMessageId;

        return [
            'citizen'             => $citizen,
            'conversation'        => $conversation,
            'message_id'          => $messageId,
            'outbound_message_id' => $outboundMessageId,
            'case_id'             => $caseId,
            'category'            => $categorySlug,
            'flow_engine' => $flowResult->engine,
            'flow_completed' => $flowResult->completed,
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
            'state'           => 'NEW',
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);

        return $conversationModel->find($id);
    }

    private function detectInitialCategory(?string $text, ?string $payload): string
    {
        $payload = trim((string) $payload);

        if ($payload !== '') {
            return match ($payload) {
                'OPTION_GREETING'   => 'saludos-felicitaciones',
                'OPTION_NEED'       => 'necesidad-comunitaria',
                'OPTION_SUPPORT'    => 'solicitud-apoyo',
                'OPTION_PROPOSAL'   => 'propuesta-ciudadana',
                'OPTION_ACTIVITIES' => 'informacion-actividades',
                'OPTION_OTHER'      => 'otro',
                default             => 'pending',
            };
        }

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
}