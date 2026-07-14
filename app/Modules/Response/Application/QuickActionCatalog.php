<?php

declare(strict_types=1);

namespace Modules\Response\Application;

final class QuickActionCatalog
{
    public function all(): array
    {
        return [
            ['command' => '/servir', 'label' => 'Agradecimiento público', 'intent' => 'PUBLIC', 'body' => 'Muchas gracias por sus palabras. Estamos para servirles.'],
            ['command' => '/contactar', 'label' => 'Confirmar seguimiento', 'intent' => 'PUBLIC', 'body' => 'Hola {nombre}, gracias por escribirnos. Nuestro equipo estará contactándote para dar seguimiento a tu caso y solicitar la información necesaria.'],
            ['command' => '/escribenos', 'label' => 'Invitar a Messenger', 'intent' => 'PUBLIC', 'body' => 'Hola {nombre}, gracias por escribirnos. Para poder darte seguimiento y proteger tus datos, por favor envíanos un mensaje privado.'],
            ['command' => '/retomar', 'label' => 'Retomar seguimiento', 'intent' => 'PRIVATE', 'body' => 'Hola {nombre}, queremos continuar dando seguimiento a tu atención. ¿Podrías compartirnos la información solicitada?'],
            ['command' => '/ayuda-flujo', 'label' => 'Asistencia humana', 'intent' => 'PRIVATE', 'body' => 'Hola {nombre}, estamos para ayudarte. Cuéntanos brevemente qué necesitas y un miembro de nuestro equipo dará seguimiento.'],
            ['command' => '/recibido', 'label' => 'Solicitud recibida', 'intent' => 'ANY', 'body' => 'Hola {nombre}, gracias por escribirnos. Hemos recibido tu mensaje y ya estamos revisando la información para darte seguimiento.'],
            ['command' => '/seguimiento', 'label' => 'En seguimiento', 'intent' => 'ANY', 'body' => 'Hola {nombre}, queremos informarte que tu solicitud continúa en seguimiento. Te compartiremos una actualización tan pronto tengamos más información.'],
            ['command' => '/informacion', 'label' => 'Solicitar información', 'intent' => 'PRIVATE', 'body' => 'Hola {nombre}, gracias por contactarnos. Para poder orientarte mejor, ¿podrías compartirnos un poco más de información sobre tu solicitud?'],
            ['command' => '/cerrar', 'label' => 'Cierre cordial', 'intent' => 'ANY', 'body' => 'Hola {nombre}, esperamos que la información brindada haya sido de ayuda. Seguimos a tu disposición. Gracias por comunicarte con nosotros.'],
        ];
    }

    public function personalize(array $action, ?string $name): array
    {
        $action['body'] = str_replace('{nombre}', trim((string) $name) ?: 'estimado ciudadano', $action['body']);
        return $action;
    }
}