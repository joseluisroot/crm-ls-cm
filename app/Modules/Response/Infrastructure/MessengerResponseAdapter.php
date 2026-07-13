<?php

declare(strict_types=1);

namespace Modules\Response\Infrastructure;

use Modules\Response\Domain\ResponseChannelAdapterInterface;
use RuntimeException;

final class MessengerResponseAdapter implements ResponseChannelAdapterInterface
{
    public function supports(string $channel): bool
    {
        return strtoupper($channel) === 'MESSENGER';
    }

    public function send(string $recipientExternalId, string $body): array
    {
        $token = (string) env('MESSENGER_PAGE_ACCESS_TOKEN');
        $version = (string) (env('MESSENGER_API_VERSION') ?: 'v23.0');
        if ($token === '') throw new RuntimeException('Messenger Page Access Token no configurado.');

        try {
            $response = service('curlrequest')->post(
                "https://graph.facebook.com/{$version}/me/messages",
                [
                    'query' => ['access_token' => $token],
                    'headers' => ['Content-Type' => 'application/json'],
                    'json' => [
                        'messaging_type' => 'RESPONSE',
                        'recipient' => ['id' => $recipientExternalId],
                        'message' => ['text' => $body],
                    ],
                    'http_errors' => false,
                ]
            );
            $payload = json_decode((string) $response->getBody(), true) ?: [];
            $externalId = (string) ($payload['message_id'] ?? '');
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300 || $externalId === '') {
                throw new RuntimeException((string) ($payload['error']['message'] ?? 'Meta rechazó el mensaje privado.'));
            }
            return ['external_response_id' => $externalId, 'provider_response' => $payload];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RuntimeException('No fue posible enviar el mensaje Messenger: ' . $e->getMessage(), 0, $e);
        }
    }
}
