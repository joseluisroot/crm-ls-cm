<?php

namespace Modules\Flow\Services;

use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;
use Modules\Flow\DTO\FlowResponseDTO;
use Modules\Flow\Support\FlowStates;

class FlowEngineService
{
    public function handle(array $context): ?int
    {
        $conversation = $context['conversation'];
        $category = $context['category'] ?? 'pending';
        $text = trim((string) ($context['text'] ?? ''));

        $state = $conversation['state'] ?? FlowStates::NEW;

        $response = $this->decideResponse($state, $category, $text);

        if (!$response || !$response->text) {
            return null;
        }

        $outboundMessageId = (new MessageModel())->insert([
            'conversation_id' => $conversation['id'],
            'direction'      => 'outbound',
            'message_type'    => 'text',
            'body'            => $response->text,
            'raw_payload'     => json_encode([
                'generated_by'  => 'flow_engine',
                'auto_reply'    => true,
                'quick_replies' => $response->quickReplies,
                'sent'          => false,
            ]),
            'sentiment'       => 'system',
            'category'        => $category,
            'priority'        => 'normal',
            'sent_status'     => 'suggested',
            'sent_at'         => null,
            'delivery_error'  => null,
        ]);

        (new ConversationModel())->update($conversation['id'], [
            'state' => $response->nextState,
            'welcomed_at' => $response->nextState === FlowStates::WAITING_CATEGORY
                ? date('Y-m-d H:i:s')
                : ($conversation['welcomed_at'] ?? null),
            'last_flow_payload' => $category,
        ]);

        return $outboundMessageId ? (int) $outboundMessageId : null;
    }

    private function decideResponse(string $state, string $category, string $text): ?FlowResponseDTO
    {
        if ($state === FlowStates::NEW || $state === '') {
            return $this->welcomeMenu();
        }

        if ($category === 'saludos-felicitaciones') {
            return new FlowResponseDTO(
                text: 'Gracias por tu saludo. Nos alegra mucho recibir tu mensaje. 💗',
                nextState: FlowStates::ATTENDING
            );
        }

        if ($category === 'necesidad-comunitaria') {
            return new FlowResponseDTO(
                text: "Gracias por reportar una necesidad de tu comunidad.\n\nPor favor indícanos:\n📍 Municipio\n📍 Comunidad\n📝 Descripción de la necesidad",
                nextState: FlowStates::WAITING_DESCRIPTION
            );
        }

        if ($category === 'solicitud-apoyo') {
            return new FlowResponseDTO(
                text: "Gracias por escribirnos.\n\nPor favor cuéntanos qué tipo de apoyo o gestión necesitas.",
                nextState: FlowStates::WAITING_DESCRIPTION
            );
        }

        if ($category === 'propuesta-ciudadana') {
            return new FlowResponseDTO(
                text: "Gracias por compartir tu idea.\n\nPor favor descríbenos tu propuesta para poder revisarla.",
                nextState: FlowStates::WAITING_DESCRIPTION
            );
        }

        if ($category === 'informacion-actividades') {
            return new FlowResponseDTO(
                text: 'Gracias por tu interés. Nuestro equipo podrá compartirte información sobre próximas actividades.',
                nextState: FlowStates::ATTENDING
            );
        }

        if ($category === 'otro') {
            return new FlowResponseDTO(
                text: 'Gracias por escribirnos. Por favor déjanos tu mensaje y nuestro equipo lo revisará.',
                nextState: FlowStates::ATTENDING
            );
        }

        return null;
    }

    private function welcomeMenu(): FlowResponseDTO
    {
        return new FlowResponseDTO(
            text: "Hola 👋 Gracias por comunicarte con Lupita Serrano.\n\nPara atenderte mejor, selecciona una opción:",
            quickReplies: [
                [
                    'title' => 'Saludos',
                    'payload' => 'OPTION_GREETING',
                ],
                [
                    'title' => 'Reportar necesidad',
                    'payload' => 'OPTION_NEED',
                ],
                [
                    'title' => 'Solicitar apoyo',
                    'payload' => 'OPTION_SUPPORT',
                ],
                [
                    'title' => 'Propuesta',
                    'payload' => 'OPTION_PROPOSAL',
                ],
                [
                    'title' => 'Actividades',
                    'payload' => 'OPTION_ACTIVITIES',
                ],
                [
                    'title' => 'Otro',
                    'payload' => 'OPTION_OTHER',
                ],
            ],
            nextState: FlowStates::WAITING_CATEGORY
        );
    }
}