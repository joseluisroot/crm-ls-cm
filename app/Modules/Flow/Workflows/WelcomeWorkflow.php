<?php

namespace Modules\Flow\Workflows;

use Modules\Flow\DTO\FlowResponseDTO;
use Modules\Flow\Support\FlowStates;

class WelcomeWorkflow
{
    public function canRun(array $conversation, string $category): bool
    {
        return ($conversation['state'] ?? FlowStates::NEW) === FlowStates::NEW;
    }

    public function run(): FlowResponseDTO
    {
        return new FlowResponseDTO(
            text: "👋 ¡Hola!\n\nGracias por escribir a Lupita Serrano.\n\nAntes que nada queremos decirte algo muy importante:\n\n❤️ Tu mensaje sí será leído.\n\nCada opinión, necesidad, propuesta o saludo que recibimos forma parte del compromiso de construir un mejor Cuscatlán Sur.\n\n¿Cómo podemos ayudarte hoy?",
            quickReplies: [
                ['title' => 'Reportar necesidad', 'payload' => 'OPTION_NEED'],
                ['title' => 'Solicitar apoyo', 'payload' => 'OPTION_SUPPORT'],
                ['title' => 'Propuesta', 'payload' => 'OPTION_PROPOSAL'],
                ['title' => 'Actividades', 'payload' => 'OPTION_ACTIVITIES'],
                ['title' => 'Saludos', 'payload' => 'OPTION_GREETING'],
                ['title' => 'Otro', 'payload' => 'OPTION_OTHER'],
            ],
            nextState: FlowStates::WAITING_CATEGORY
        );
    }
}