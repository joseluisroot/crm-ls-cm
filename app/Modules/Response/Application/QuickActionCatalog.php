<?php

declare(strict_types=1);

namespace Modules\Response\Application;

final class QuickActionCatalog
{
    public function all(): array
    {
        return [
            ['command' => '/servir', 'label' => 'Cortesía pública', 'intent' => 'PUBLIC_ACKNOWLEDGEMENT', 'channels' => ['FACEBOOK'], 'body' => 'Muchas gracias por sus palabras, {nombre}. Estamos para servirles.'],
            ['command' => '/contactar', 'label' => 'Seguimiento del equipo', 'intent' => 'PUBLIC_TO_PRIVATE', 'channels' => ['FACEBOOK'], 'body' => 'Gracias por escribirnos, {nombre}. Nuestro equipo estará contactándote para dar seguimiento a tu caso y solicitar la información necesaria.'],
            ['command' => '/escribenos', 'label' => 'Solicitar mensaje privado', 'intent' => 'REQUEST_PRIVATE_MESSAGE', 'channels' => ['FACEBOOK'], 'body' => 'Gracias por escribirnos, {nombre}. Para poder darte seguimiento y proteger tus datos, por favor envíanos un mensaje privado.'],
            ['command' => '/recibido', 'label' => 'Solicitud recibida', 'intent' => 'ACKNOWLEDGEMENT', 'channels' => ['FACEBOOK', 'MESSENGER'], 'body' => 'Hola {nombre}, gracias por escribirnos. Hemos recibido tu mensaje y ya estamos revisando la información para darte seguimiento.'],
            ['command' => '/seguimiento', 'label' => 'En seguimiento', 'intent' => 'FOLLOW_UP', 'channels' => ['FACEBOOK', 'MESSENGER'], 'body' => 'Hola {nombre}, queremos informarte que tu solicitud continúa en seguimiento. Te compartiremos una actualización tan pronto tengamos más información.'],
            ['command' => '/retomar', 'label' => 'Retomar conversación', 'intent' => 'HUMAN_FOLLOW_UP', 'channels' => ['MESSENGER'], 'body' => 'Hola {nombre}, queremos continuar dando seguimiento a tu atención. ¿Podrías compartirnos la información solicitada?'],
            ['command' => '/ayuda-flujo', 'label' => 'Intervención humana', 'intent' => 'HUMAN_HANDOFF', 'channels' => ['MESSENGER'], 'body' => 'Hola {nombre}, estamos para ayudarte. Cuéntanos brevemente qué necesitas y un miembro de nuestro equipo dará seguimiento.'],
            ['command' => '/informacion', 'label' => 'Solicitar información', 'intent' => 'REQUEST_INFORMATION', 'channels' => ['FACEBOOK', 'MESSENGER'], 'body' => 'Hola {nombre}, gracias por contactarnos. Para poder orientarte mejor, ¿podrías compartirnos un poco más de información sobre tu solicitud?'],
            ['command' => '/cerrar', 'label' => 'Cierre cordial', 'intent' => 'CLOSURE', 'channels' => ['FACEBOOK', 'MESSENGER'], 'body' => 'Hola {nombre}, esperamos que la información brindada haya sido de ayuda. Seguimos a tu disposición. Gracias por comunicarte con nosotros.'],
        ];
    }

    public function forChannel(string $channel): array
    {
        $channel = strtoupper(trim($channel));

        return array_values(array_filter(
            $this->all(),
            static fn (array $action): bool => in_array($channel, $action['channels'], true),
        ));
    }

    public function personalize(array $action, ?string $name): array
    {
        $action['body'] = str_replace('{nombre}', trim((string) $name) ?: 'estimado ciudadano', $action['body']);
        return $action;
    }
}
