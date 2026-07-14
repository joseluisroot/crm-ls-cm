<?php

declare(strict_types=1);

namespace Modules\Response\Application;

final class ResponseActionCatalog
{
    public function forAttention(array $item, array $capability): array
    {
        $channel = strtoupper((string) ($capability['channel'] ?? $item['channel'] ?? ''));
        $isFacebook = $channel === 'FACEBOOK';
        $isMessenger = $channel === 'MESSENGER';

        return [
            [
                'code' => 'PUBLIC_REPLY',
                'label' => 'Responder públicamente',
                'description' => 'Reconocer el comentario y confirmar que la atención fue recibida.',
                'enabled' => $isFacebook && (bool) ($capability['ready'] ?? false),
                'channel' => 'FACEBOOK',
                'recommended' => $isFacebook,
            ],
            [
                'code' => 'PRIVATE_FOLLOW_UP',
                'label' => $isMessenger ? 'Responder por Messenger' : 'Continuar por Messenger',
                'description' => 'Solicitar datos, aclarar el caso o continuar un flujo de atención privado.',
                'enabled' => $isMessenger && (bool) ($capability['ready'] ?? false),
                'channel' => 'MESSENGER',
                'recommended' => $isMessenger,
            ],
            [
                'code' => 'PUBLIC_THEN_PRIVATE',
                'label' => 'Responder y pasar a privado',
                'description' => 'Publicar una confirmación y continuar por Messenger cuando exista una conversación válida.',
                'enabled' => false,
                'channel' => 'FACEBOOK + MESSENGER',
                'recommended' => false,
                'reason' => 'Se habilitará cuando CIAC pueda verificar una conversación Messenger existente para este ciudadano.',
            ],
            [
                'code' => 'RESUME_FLOW',
                'label' => 'Retomar flujo',
                'description' => 'Intervenir cuando la persona no completó, no comprendió o abandonó el flujo de Messenger.',
                'enabled' => $isMessenger && (bool) ($capability['ready'] ?? false),
                'channel' => 'MESSENGER',
                'recommended' => false,
            ],
        ];
    }
}
