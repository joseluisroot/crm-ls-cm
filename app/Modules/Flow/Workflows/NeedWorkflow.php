<?php

namespace Modules\Flow\Workflows;

use Modules\Flow\DTO\FlowResponseDTO;
use Modules\Flow\Support\FlowStates;

class NeedWorkflow
{
    public function canRun(array $conversation, string $category): bool
    {
        return $category === 'necesidad-comunitaria';
    }

    public function run(): FlowResponseDTO
    {
        return new FlowResponseDTO(
            text: "Gracias por confiar en nosotros. 🙏\n\nAcabamos de registrar que deseas reportar una necesidad de tu comunidad.\n\nPara poder darle seguimiento adecuado, por favor indícanos:\n\n📍 Municipio\n📍 Comunidad o colonia\n📝 Qué está ocurriendo\n\nMientras más información nos compartas, mejor podremos gestionar tu caso.",
            nextState: FlowStates::WAITING_DESCRIPTION
        );
    }
}