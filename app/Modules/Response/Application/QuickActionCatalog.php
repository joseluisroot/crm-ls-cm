<?php

declare(strict_types=1);

namespace Modules\Response\Application;

final class QuickActionCatalog
{
    public function all(): array
    {
        return [
            ['command' => '/recibido', 'label' => 'Solicitud recibida', 'body' => 'Hola {nombre}, gracias por escribirnos. Hemos recibido tu mensaje y ya estamos revisando la información para darte seguimiento.'],
            ['command' => '/agradecer', 'label' => 'Agradecimiento', 'body' => 'Hola {nombre}, muchas gracias por comunicarte y compartirnos tu comentario. Valoramos mucho tu participación.'],
            ['command' => '/seguimiento', 'label' => 'En seguimiento', 'body' => 'Hola {nombre}, queremos informarte que tu solicitud continúa en seguimiento. Te compartiremos una actualización tan pronto tengamos más información.'],
            ['command' => '/privado', 'label' => 'Continuar por privado', 'body' => 'Hola {nombre}, gracias por escribirnos. Para proteger tus datos y poder ayudarte mejor, continuaremos la atención por mensaje privado.'],
            ['command' => '/informacion', 'label' => 'Solicitar información', 'body' => 'Hola {nombre}, gracias por contactarnos. Para poder orientarte mejor, ¿podrías compartirnos un poco más de información sobre tu solicitud?'],
            ['command' => '/cerrar', 'label' => 'Cierre cordial', 'body' => 'Hola {nombre}, esperamos que la información brindada haya sido de ayuda. Seguimos a tu disposición. Gracias por comunicarte con nosotros.'],
        ];
    }

    public function personalize(array $action, ?string $name): array
    {
        $action['body'] = str_replace('{nombre}', trim((string) $name) ?: 'estimado ciudadano', $action['body']);
        return $action;
    }
}
