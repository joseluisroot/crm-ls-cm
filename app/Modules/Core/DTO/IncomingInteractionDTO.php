<?php

namespace Modules\Core\DTO;

class IncomingInteractionDTO
{
    public function __construct(

        // Canal
        public string $channel,

        // Usuario externo
        public string $externalUserId,

        // Identificador único del mensaje
        public ?string $externalMessageId = null,

        // Texto recibido
        public ?string $text = null,

        // Payload (Quick Reply, Botón, etc.)
        public ?string $payload = null,

        // Tipo
        public string $messageType = 'text',

        // Archivos
        public array $attachments = [],

        // Datos originales
        public array $rawPayload = [],

        // Fecha del proveedor
        public ?int $providerTimestamp = null,

    ) {
    }
}