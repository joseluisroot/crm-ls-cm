<?php

declare(strict_types=1);

namespace Modules\Authorization\Application;

final class OwnershipService
{
    public function __construct(
        private readonly ?AuthorizationService $authorization = null,
    ) {
    }

    public function canAccessWorkItem(int $userId, int $workItemId): bool
    {
        if ($userId <= 0 || $workItemId <= 0) {
            return false;
        }

        $authorization = $this->authorization ?? new AuthorizationService();
        if ($authorization->can($userId, 'operations.view')) {
            return true;
        }

        if (! $authorization->can($userId, 'operations.view_own')) {
            return false;
        }

        return db_connect()->table('work_items')
            ->where('id', $workItemId)
            ->where('assigned_user_id', $userId)
            ->countAllResults() > 0;
    }

    public function canActOnWorkItem(int $userId, int $workItemId, string $permission): bool
    {
        $authorization = $this->authorization ?? new AuthorizationService();

        return $authorization->can($userId, $permission)
            && $this->canAccessWorkItem($userId, $workItemId);
    }

    public function canAccessCase(int $userId, int $caseId): bool
    {
        if ($userId <= 0 || $caseId <= 0) {
            return false;
        }

        $authorization = $this->authorization ?? new AuthorizationService();
        if ($authorization->can($userId, 'cases.view')) {
            return true;
        }

        if (! $authorization->can($userId, 'cases.view_own')) {
            return false;
        }

        return db_connect()->table('cases')
            ->where('id', $caseId)
            ->where('assigned_to', $userId)
            ->countAllResults() > 0;
    }

    public function canActOnCase(int $userId, int $caseId, string $permission): bool
    {
        $authorization = $this->authorization ?? new AuthorizationService();

        return $authorization->can($userId, $permission)
            && $this->canAccessCase($userId, $caseId);
    }
}
