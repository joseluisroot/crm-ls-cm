<?php

namespace Modules\Messenger\Services;

class MessengerService
{
    private string $pageAccessToken;
    private string $apiVersion;

    public function __construct()
    {
        $this->pageAccessToken = env('MESSENGER_PAGE_ACCESS_TOKEN');
        $this->apiVersion = env('MESSENGER_API_VERSION') ?: 'v23.0';
    }

    public function sendTextMessage(string $recipientId, string $message): bool
    {
        if (empty($this->pageAccessToken)) {
            log_message('error', 'Messenger Page Access Token no configurado.');
            return false;
        }

        $client = service('curlrequest');

        $url = "https://graph.facebook.com/{$this->apiVersion}/me/messages?access_token={$this->pageAccessToken}";

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'recipient' => [
                        'id' => $recipientId,
                    ],
                    'message' => [
                        'text' => $message,
                    ],
                ],
            ]);

            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
        } catch (\Throwable $e) {
            log_message('error', 'Error enviando mensaje Messenger: ' . $e->getMessage());
            return false;
        }
    }
}