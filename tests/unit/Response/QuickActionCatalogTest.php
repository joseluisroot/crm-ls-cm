<?php

declare(strict_types=1);

namespace Tests\Unit\Response;

use Modules\Response\Application\QuickActionCatalog;
use PHPUnit\Framework\TestCase;

final class QuickActionCatalogTest extends TestCase
{
    public function testCatalogProvidesPublicAndPrivateCitizenCareCommands(): void
    {
        $catalog = new QuickActionCatalog();
        $actions = $catalog->all();
        $commands = array_column($actions, 'command');
        $intents = array_column($actions, 'intent');

        self::assertContains('/servir', $commands);
        self::assertContains('/contactar', $commands);
        self::assertContains('/escribenos', $commands);
        self::assertContains('/retomar', $commands);
        self::assertContains('/ayuda-flujo', $commands);
        self::assertContains('PUBLIC', $intents);
        self::assertContains('PRIVATE', $intents);

        $contactAction = $actions[array_search('/contactar', $commands, true)];
        $personalized = $catalog->personalize($contactAction, 'Juan Pérez');
        self::assertStringContainsString('Juan Pérez', $personalized['body']);
        self::assertStringNotContainsString('{nombre}', $personalized['body']);
    }
}