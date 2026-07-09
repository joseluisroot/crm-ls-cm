<?php

namespace Modules\Flow\Workflows;

use Modules\Flow\DTO\FlowResponseDTO;
use Modules\Flow\Support\FlowStates;

class SimpleCategoryWorkflow
{
    public function canRun(array $conversation, string $category): bool
    {
        return in_array($category, [
            'saludos-felicitaciones',
            'solicitud-apoyo',
            'propuesta-ciudadana',
            'informacion-actividades',
            'otro',
        ], true);
    }

    public function run(string $category): FlowResponseDTO
    {
        $text = match ($category) {
            'saludos-felicitaciones' => "Gracias por tu saludo. Nos alegra mucho recibir tu mensaje. ❤️\n\nTu apoyo y tus palabras también forman parte de este proceso.",
            'solicitud-apoyo' => "Gracias por escribirnos. 🙏\n\nPor favor cuéntanos qué tipo de apoyo o gestión necesitas para poder registrar tu solicitud correctamente.",
            'propuesta-ciudadana' => "Gracias por compartir tu idea. 💡\n\nPor favor descríbenos tu propuesta para que nuestro equipo pueda revisarla.",
            'informacion-actividades' => "Gracias por tu interés. 📅\n\nNuestro equipo podrá compartirte información sobre próximas actividades.",
            default => "Gracias por escribirnos. ✍️\n\nPor favor déjanos tu mensaje y nuestro equipo lo revisará.",
        };

        return new FlowResponseDTO(
            text: $text,
            nextState: FlowStates::ATTENDING
        );
    }
}