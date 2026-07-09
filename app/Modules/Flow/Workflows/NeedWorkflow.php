<?php

namespace Modules\Flow\Workflows;

use Modules\Flow\DTO\FlowResponseDTO;
use Modules\Flow\Support\FlowStates;
use Modules\ConversationEngine\Services\ConversationContextService;

class NeedWorkflow
{
    public function canRun(array $conversation, string $category): bool
    {
        return $category === 'necesidad-comunitaria'
            || in_array($conversation['state'] ?? '', [
                FlowStates::WAITING_MUNICIPALITY,
                FlowStates::WAITING_COMMUNITY,
                FlowStates::WAITING_DESCRIPTION,
            ], true);
    }

    public function run(array $conversation, ?string $text = null, string $category = 'necesidad-comunitaria'): FlowResponseDTO
    {
        $contextService = new ConversationContextService();
        $conversationId = (int) $conversation['id'];
        $state = $conversation['state'] ?? FlowStates::NEW;

        if ($category === 'necesidad-comunitaria' && $state === FlowStates::WAITING_CATEGORY) {
            $contextService->put($conversationId, 'category', 'necesidad-comunitaria');

            return new FlowResponseDTO(
                text: "Gracias por confiar en nosotros. 🙏\n\nPara registrar tu reporte, primero indícanos:\n\n📍 ¿En qué municipio ocurre la situación?",
                nextState: FlowStates::WAITING_MUNICIPALITY
            );
        }

        if ($state === FlowStates::WAITING_MUNICIPALITY) {
            $contextService->put($conversationId, 'municipality', trim((string) $text));

            return new FlowResponseDTO(
                text: "Gracias. Ahora ayúdanos con otro dato:\n\n🏘️ ¿En qué comunidad, colonia, cantón o caserío ocurre?",
                nextState: FlowStates::WAITING_COMMUNITY
            );
        }

        if ($state === FlowStates::WAITING_COMMUNITY) {
            $contextService->put($conversationId, 'community', trim((string) $text));

            return new FlowResponseDTO(
                text: "Perfecto. Ahora cuéntanos con tus palabras:\n\n📝 ¿Qué está ocurriendo y cómo afecta a tu comunidad?",
                nextState: FlowStates::WAITING_DESCRIPTION
            );
        }

        if ($state === FlowStates::WAITING_DESCRIPTION) {
            $contextService->put($conversationId, 'description', trim((string) $text));

            return new FlowResponseDTO(
                text: "❤️ Gracias por compartirnos tu situación.\n\nTu mensaje ya quedó registrado para seguimiento ciudadano.\n\nNuestro equipo revisará la información y la incorporará al proceso de atención.\n\nSabemos que detrás de cada reporte hay personas, familias y comunidades que desean ser escuchadas.\n\n¿Deseas hacer algo más?",
                quickReplies: [
                    ['title' => 'Reportar otra necesidad', 'payload' => 'OPTION_NEED'],
                    ['title' => 'Compartir propuesta', 'payload' => 'OPTION_PROPOSAL'],
                    ['title' => 'Finalizar por ahora', 'payload' => 'OPTION_FINISH'],
                ],
                nextState: FlowStates::CASE_READY
            );
        }

        return new FlowResponseDTO(
            text: "Para poder ayudarte mejor, por favor compártenos la información solicitada.",
            nextState: $state
        );
    }
}