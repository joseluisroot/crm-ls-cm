<?php

declare(strict_types=1);

namespace Modules\Response\Infrastructure;

use Modules\Response\Domain\ResponseChannelAdapterInterface;
use RuntimeException;

final class FacebookCommentResponseAdapter implements ResponseChannelAdapterInterface
{
    public function supports(string $channel): bool
    {
        return strtoupper($channel) === 'FACEBOOK';
    }

    public function send(string $recipientExternalId, string $body): array
    {
        $token = (string) env('MESSENGER_PAGE_ACCESS_TOKEN');
        $version = (string) (env('MESSENGER_API_VERSION') ?: 'v23.0');
        if ($token === '') throw new RuntimeException('Meta Page Access Token no configurado.');

        try {
            $response = service('curlrequest')->post(
                "https://graph.facebook.com/{$version}/" . rawurlencode($recipientExternalId) . '/comments',
                ['query' => ['access_token' => $token], 'form_params' => ['message' => $body], 'http_errors' => false]
            );
            $payload = json_decode((string) $response->getBody(), true) ?: [];
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300 || empty($payload['id'])) {
                throw new RuntimeException((string) ($payload['error']['message'] ?? 'Meta rechazó la respuesta pública.'));
            }
            return ['external_response_id' => (string) $payload['id'], 'provider_response' => $payload];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RuntimeException('No fue posible responder el comentario: ' . $e->getMessage(), 0, $e);
        }
    }
}
