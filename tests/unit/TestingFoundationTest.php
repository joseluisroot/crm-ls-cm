<?php

declare(strict_types=1);

namespace Tests\Unit;

use Config\Filters;
use Tests\Support\CiacTestCase;

final class TestingFoundationTest extends CiacTestCase
{
    public function testApplicationRunsInTestingEnvironment(): void
    {
        $this->assertSame('testing', ENVIRONMENT);
    }

    public function testGlobalCsrfFilterRemainsEnabled(): void
    {
        $filters = config(Filters::class);
        $before = $filters->globals['before'] ?? [];

        $this->assertArrayHasKey('csrf', $before);
        $this->assertSame([
            'webhooks/messenger',
            'system/migrate',
            'system/seed/*',
        ], $before['csrf']['except'] ?? []);
    }

    public function testAdminSessionHelperProvidesExpectedKeys(): void
    {
        $this->actingAsAdmin(['admin_user_id' => 99]);

        $this->assertTrue((bool) session()->get('admin_logged_in'));
        $this->assertSame(99, (int) session()->get('admin_user_id'));
    }

    public function testCsrfHelperReturnsCurrentTokenPayload(): void
    {
        $payload = $this->csrfPayload();

        $this->assertArrayHasKey(csrf_token(), $payload);
        $this->assertSame(csrf_hash(), $payload[csrf_token()]);
    }
}
