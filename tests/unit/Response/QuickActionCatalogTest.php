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

        $receivedAction = null;
        foreach ($actions as $action) {
            if (($action['command'] ?? null) === '/recibido') {
                $receivedAction = $action;
                break;
            }
        }

        self::assertNotNull($receivedAction, 'El catálogo debe conservar la acción /recibido.');

        $personalized = $catalog->personalize($receivedAction, 'Juan Pérez');
        self::assertStringContainsString('Juan Pérez', $personalized['body']);
        self::assertStringNotContainsString('{nombre}', $personalized['body']);
    }
}
