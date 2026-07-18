<?php

declare(strict_types=1);

namespace Tests\Unit\Messenger;

use Modules\Messenger\Security\MetaWebhookSignatureValidator;
use PHPUnit\Framework\TestCase;

final class MetaWebhookSignatureValidatorTest extends TestCase
{
    private const APP_SECRET = 'ciac-test-app-secret';

    public function testAcceptsValidSignatureForExactRawBody(): void
    {
        $body = '{"object":"page","entry":[]}';
        $validator = new MetaWebhookSignatureValidator(self::APP_SECRET);

        self::assertTrue($validator->isValid($body, $this->signatureFor($body)));
    }

    public function testRejectsMissingSignature(): void
    {
        $validator = new MetaWebhookSignatureValidator(self::APP_SECRET);

        self::assertFalse($validator->isValid('{"object":"page"}', ''));
    }

    public function testRejectsInvalidSignature(): void
    {
        $validator = new MetaWebhookSignatureValidator(self::APP_SECRET);

        self::assertFalse($validator->isValid(
            '{"object":"page"}',
            'sha256=' . str_repeat('0', 64),
        ));
    }

    public function testRejectsAlteredBody(): void
    {
        $originalBody = '{"object":"page","entry":[]}';
        $alteredBody = '{"object":"page","entry":[{"id":"altered"}]}';
        $validator = new MetaWebhookSignatureValidator(self::APP_SECRET);

        self::assertFalse($validator->isValid(
            $alteredBody,
            $this->signatureFor($originalBody),
        ));
    }

    public function testRejectsMalformedSignatureScheme(): void
    {
        $body = '{"object":"page"}';
        $validator = new MetaWebhookSignatureValidator(self::APP_SECRET);

        self::assertFalse($validator->isValid(
            $body,
            'sha1=' . hash_hmac('sha1', $body, self::APP_SECRET),
        ));
    }

    public function testFailsClosedWhenAppSecretIsEmpty(): void
    {
        $body = '{"object":"page"}';
        $validator = new MetaWebhookSignatureValidator('');

        self::assertFalse($validator->isConfigured());
        self::assertFalse($validator->isValid($body, $this->signatureFor($body)));
    }

    public function testImplementationUsesTimingSafeComparison(): void
    {
        $source = file_get_contents(
            APPPATH . 'Modules/Messenger/Security/MetaWebhookSignatureValidator.php',
        );

        self::assertIsString($source);
        self::assertStringContainsString('hash_equals(', $source);
    }

    private function signatureFor(string $body): string
    {
        return 'sha256=' . hash_hmac('sha256', $body, self::APP_SECRET);
    }
}
