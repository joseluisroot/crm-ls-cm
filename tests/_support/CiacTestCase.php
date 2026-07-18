<?php

declare(strict_types=1);

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;

abstract class CiacTestCase extends CIUnitTestCase
{
    /**
     * Prepares the minimum authenticated administrator session expected by
     * AdminAuthFilter and permission-aware requests.
     *
     * @param array<string, mixed> $overrides
     */
    protected function actingAsAdmin(array $overrides = []): static
    {
        session()->set(array_merge([
            'admin_logged_in' => true,
            'admin_user_id' => 1,
            'admin_user_name' => 'CIAC Test Administrator',
        ], $overrides));

        return $this;
    }

    /**
     * Returns the current CodeIgniter CSRF field as a request payload.
     *
     * @return array<string, string>
     */
    protected function csrfPayload(): array
    {
        return [csrf_token() => csrf_hash()];
    }

    protected function tearDown(): void
    {
        session()->destroy();

        parent::tearDown();
    }
}
