<?php

declare(strict_types=1);

namespace Modules\Messenger\Security;

final class MetaWebhookSignatureValidator
{
    private const SIGNATURE_PREFIX = 'sha256=';

    public function __construct(private readonly string $appSecret)
    {
    }

    public function isConfigured(): bool
    {
        return trim($this->appSecret) !== '';
    }

    public function isValid(string $rawBody, string $providedSignature): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $providedSignature = strtolower(trim($providedSignature));

        if (! preg_match('/^sha256=[a-f0-9]{64}$/', $providedSignature)) {
            return false;
        }

        $expectedSignature = self::SIGNATURE_PREFIX . hash_hmac(
            'sha256',
            $rawBody,
            $this->appSecret,
        );

        return hash_equals($expectedSignature, $providedSignature);
    }
}
