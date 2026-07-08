<?php

namespace Modules\ConversationEngine\DTO;

class IncomingMessageDTO
{
    public function __construct(
        public string $channel,
        public string $externalUserId,
        public ?string $text,
        public string $messageType = 'text',
        public ?array $rawPayload = null
    ) {
    }
}