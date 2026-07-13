<?php

declare(strict_types=1);

namespace Tests\Unit\Response;

use Modules\Response\Application\QuickActionCatalog;
use PHPUnit\Framework\TestCase;

final class QuickActionCatalogTest extends TestCase
{
    public function testCatalogProvidesPersonalizedCommands(): void
    {
        $catalog = new QuickActionCatalog();
        $actions = $catalog->all();

        self::assertNotEmpty($actions);
        self::assertSame('/recibido', $actions[0]['command']);

        $personalized = $catalog->personalize($actions[0], 'Juan Pérez');
        self::assertStringContainsString('Juan Pérez', $personalized['body']);
        self::assertStringNotContainsString('{nombre}', $personalized['body']);
    }
}
