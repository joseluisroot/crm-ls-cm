<?php

declare(strict_types=1);

namespace Tests\Unit\Response;

use Modules\Response\Infrastructure\FacebookCommentResponseAdapter;
use Modules\Response\Infrastructure\MessengerResponseAdapter;
use PHPUnit\Framework\TestCase;

final class ResponseChannelAdaptersTest extends TestCase
{
    public function testAdaptersOnlySupportTheirOwnChannel(): void
    {
        $facebook = new FacebookCommentResponseAdapter();
        $messenger = new MessengerResponseAdapter();

        self::assertTrue($facebook->supports('FACEBOOK'));
        self::assertFalse($facebook->supports('MESSENGER'));
        self::assertTrue($messenger->supports('messenger'));
        self::assertFalse($messenger->supports('FACEBOOK'));
    }
}
