<?php

declare(strict_types=1);

namespace Modules\Authorization\Application;

final class OwnershipService
{
    public function __construct(
        private readonly ?AuthorizationService $authorization = null,
        private readonly ?TeamScopeService $teamScope = null,
    ) {
    }

    public function canAccessWorkItem(int $userId, int $workItemId): bool
    {
        if ($userId <= 0 || $workItemId <= 0) return false;

        $authorization = $this->authorization ?? new AuthorizationService();
        if ($authorization->can($userId, 'operations.view')) return true;

        $item = db_connect()->table('work_items')->select('assigned_user_id')->where('id', $workItemId)->get()->getRowArray();
        if (! $item) return false;
        $assignedUserId = (int) ($item['assigned_user_id'] ?? 0);

        if ($authorization->can($userId, 'operations.view_team')) {
            return $assignedUserId > 0 && ($this->teamScope ?? new TeamScopeService())->sharesTeam($userId, $assignedUserId);
        }

        return $authorization->can($userId, 'operations.view_own') && $assignedUserId === $userId;
    }

    public function canActOnWorkItem(int $userId, int $workItemId, string $permission): bool
    {
        $authorization = $this->authorization ?? new AuthorizationService();
        return $authorization->can($userId, $permission) && $this->canAccessWorkItem($userId, $workItemId);
    }

    public function canAccessCase(int $userId, int $caseId): bool
    {
        if ($userId <= 0 || $caseId <= 0) return false;

        $authorization = $this->authorization ?? new AuthorizationService();
        if ($authorization->can($userId, 'cases.view')) return true;

        $case = db_connect()->table('cases')->select('assigned_to')->where('id', $caseId)->get()->getRowArray();
        if (! $case) return false;
        $assignedUserId = (int) ($case['assigned_to'] ?? 0);

        if ($authorization->can($userId, 'cases.view_team')) {
            return $assignedUserId > 0 && ($this->teamScope ?? new TeamScopeService())->sharesTeam($userId, $assignedUserId);
        }

        return $authorization->can($userId, 'cases.view_own') && $assignedUserId === $userId;
    }

    public function canActOnCase(int $userId, int $caseId, string $permission): bool
    {
        $authorization = $this->authorization ?? new AuthorizationService();
        return $authorization->can($userId, $permission) && $this->canAccessCase($userId, $caseId);
    }
}
