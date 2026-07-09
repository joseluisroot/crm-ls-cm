<?php

namespace Modules\Core\Events;

use Modules\Core\Contracts\CoreEventInterface;
use Modules\Core\DTO\IncomingInteractionDTO;

class CitizenMessageReceived implements CoreEventInterface
{
    public function __construct(
        public IncomingInteractionDTO $interaction
    ) {
    }

    public function name(): string
    {
        return 'CitizenMessageReceived';
    }

    public function payload(): array
    {
        return [
            'channel' => $this->interaction->channel,
            'external_user_id' => $this->interaction->externalUserId,
            'external_message_id' => $this->interaction->externalMessageId,
            'text' => $this->interaction->text,
            'payload' => $this->interaction->payload,
            'message_type' => $this->interaction->messageType,
            'provider_timestamp' => $this->interaction->providerTimestamp,
        ];
    }
}