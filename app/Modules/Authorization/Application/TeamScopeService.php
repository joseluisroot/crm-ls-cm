<?php

declare(strict_types=1);

namespace Modules\Authorization\Application;

final class TeamScopeService
{
    /** @return int[] */
    public function teamIdsForUser(int $userId): array
    {
        if ($userId <= 0 || ! db_connect()->tableExists('team_members')) return [];

        $memberTeamIds = array_map('intval', array_column(
            db_connect()->table('team_members')
                ->select('team_id')
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->get()->getResultArray(),
            'team_id'
        ));

        $supervisedTeamIds = array_map('intval', array_column(
            db_connect()->table('teams')
                ->select('id')
                ->where('supervisor_user_id', $userId)
                ->where('is_active', 1)
                ->get()->getResultArray(),
            'id'
        ));

        return array_values(array_unique(array_merge($memberTeamIds, $supervisedTeamIds)));
    }

    /** @return int[] */
    public function userIdsInScope(int $userId): array
    {
        $teamIds = $this->teamIdsForUser($userId);
        if ($teamIds === []) return [$userId];

        $memberIds = array_map('intval', array_column(
            db_connect()->table('team_members')
                ->select('user_id')
                ->whereIn('team_id', $teamIds)
                ->where('is_active', 1)
                ->get()->getResultArray(),
            'user_id'
        ));

        return array_values(array_unique(array_merge([$userId], $memberIds)));
    }

    public function sharesTeam(int $viewerUserId, int $targetUserId): bool
    {
        return in_array($targetUserId, $this->userIdsInScope($viewerUserId), true);
    }
}
