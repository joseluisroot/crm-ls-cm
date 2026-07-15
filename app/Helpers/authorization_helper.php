<?php

declare(strict_types=1);

use Modules\Authorization\Application\AuthorizationService;

if (! function_exists('can')) {
    function can(string $permission): bool
    {
        $userId = (int) session()->get('admin_user_id');

        return (new AuthorizationService())->can($userId, $permission);
    }
}

if (! function_exists('cannot')) {
    function cannot(string $permission): bool
    {
        return ! can($permission);
    }
}
