<?php

namespace Modules\Core\Services;

use Modules\Core\DTO\IncomingInteractionDTO;
use Modules\Core\Events\CitizenMessageReceived;

class CoreEngine
{
    public function handleIncomingInteraction(IncomingInteractionDTO $interaction): array
    {
        $dispatcher = new EventDispatcher();

        $dispatcher->listen('CitizenMessageReceived', function (CitizenMessageReceived $event) {
            return (new \Modules\ConversationEngine\Services\ConversationEngineService())
                ->handleIncomingMessage(
                    new \Modules\ConversationEngine\DTO\IncomingMessageDTO(
                        channel: $event->interaction->channel,
                        externalUserId: $event->interaction->externalUserId,
                        text: $event->interaction->text,
                        messageType: $event->interaction->messageType,
                        rawPayload: $event->interaction->rawPayload,
                        payload: $event->interaction->payload,
                        externalMessageId: $event->interaction->externalMessageId
                    )
                );
        });

        $event = new CitizenMessageReceived($interaction);

        return [
            'event' => $event->name(),
            'payload' => $event->payload(),
            'results' => $dispatcher->dispatch($event),
        ];
    }
}