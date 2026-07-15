<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Navigation;

use Modules\Authorization\Application\AuthorizationService;

final class NavigationBuilder
{
    public function __construct(private readonly ?AuthorizationService $authorization = null) {}

    public function build(int $userId): array
    {
        $authorization = $this->authorization ?? new AuthorizationService();
        $permissions = $authorization->permissionsForUser($userId);
        $roles = $authorization->rolesForUser($userId);
        $can = static fn (string ...$required): bool => count(array_intersect($required, $permissions)) > 0;
        $isOwnView = $can('operations.view_own') && ! $can('operations.view_team', 'operations.view');
        $isTeamView = $can('operations.view_team') && ! $can('operations.view');
        $groups = [];

        $this->appendGroup($groups, 'Inicio', [
            $this->item('🏠', $isOwnView ? 'Mi trabajo' : 'Dashboard', $isOwnView ? 'admin/my-work' : 'admin', $isOwnView ? 'admin/my-work' : 'admin', false, $can('dashboard.view')),
        ]);

        $this->appendGroup($groups, $isOwnView ? 'Mi trabajo' : ($isTeamView ? 'Mi equipo' : 'Citizen Operations'), [
            $this->item('📥', $isOwnView ? 'Mis atenciones' : ($isTeamView ? 'Atenciones del equipo' : 'Operations Queue'), 'admin/operations', 'admin/operations', true, $can('operations.view', 'operations.view_team', 'operations.view_own')),
            $this->item('📢', 'Publication Center', 'admin/publications', 'admin/publications', true, $can('publications.view')),
            $this->item('🌍', 'Public Engagement', 'admin/engagement', 'admin/engagement', false, $can('engagement.view')),
            $this->item('👥', 'Citizen Participation', 'admin/engagement/participants', 'admin/engagement/participants', false, $can('engagement.view')),
        ]);

        $caseOwn = $can('cases.view_own') && ! $can('cases.view_team', 'cases.view');
        $caseTeam = $can('cases.view_team') && ! $can('cases.view');
        $this->appendGroup($groups, 'Atención ciudadana', [
            $this->item('👤', 'Citizen Center', 'admin/citizens', 'admin/citizens', true, $can('citizens.view')),
            $this->item('💬', 'Conversation Center', 'admin/conversations', 'admin/conversations', true, $can('conversations.view')),
            $this->item('📁', $caseOwn ? 'Mis casos' : ($caseTeam ? 'Casos del equipo' : 'Case Management'), $caseOwn ? 'admin/my-cases' : 'admin/cases', $caseOwn ? 'admin/my-cases' : 'admin/cases', true, $can('cases.view', 'cases.view_team', 'cases.view_own')),
            $this->item('🔔', 'Notification Center', 'admin/notifications', 'admin/notifications', true, $can('notifications.view')),
        ]);

        $this->appendGroup($groups, 'Inteligencia', [
            $this->item('📊', 'Intelligence Center', 'admin/analytics', 'admin/analytics', true, $can('analytics.view', 'analytics.team')),
            $this->item('⏱️', 'Citizen Performance', 'admin/performance', 'admin/performance', true, $can('analytics.view', 'analytics.team')),
        ]);
        $this->appendGroup($groups, 'Observabilidad', [
            $this->item('🔁', 'Replay Center', 'admin/integration/events', 'admin/integration/events', true, $can('replay.view')),
            $this->item('💙', 'Channel Events', 'admin/messenger/events', 'admin/messenger/events', true, $can('integration.view')),
        ]);
        $this->appendGroup($groups, 'Automatización', [
            $this->item('⚙️', 'Workflow Designer', 'admin/workflows', 'admin/workflows', false, $can('workflow.view')),
            $this->item('🧪', 'Workflow Simulator', 'admin/workflows/simulator', 'admin/workflows/simulator', true, $can('workflow.view')),
            $this->item('▶', 'Runtime Inspector', 'admin/workflows/runtime', 'admin/workflows/runtime', true, $can('workflow.view')),
        ]);
        $this->appendGroup($groups, 'Administración', [
            $this->item('🔐', 'Usuarios y accesos', 'admin/access/users', 'admin/access/users', true, $can('authorization.manage')),
            $this->item('🛡️', 'Roles y permisos', 'admin/access/roles', 'admin/access/roles', true, $can('authorization.manage')),
            $this->item('👥', 'Equipos de atención', 'admin/access/teams', 'admin/access/teams', true, $can('teams.manage')),
        ]);

        return ['profile' => $this->profile($roles), 'groups' => $groups];
    }

    private function appendGroup(array &$groups, string $label, array $items): void
    {
        $items = array_values(array_filter($items));
        if ($items !== []) $groups[] = ['label' => $label, 'items' => $items];
    }

    private function item(string $icon, string $label, string $url, string $activePath, bool $prefix, bool $visible): ?array
    {
        return $visible ? compact('icon', 'label', 'url', 'activePath', 'prefix') : null;
    }

    private function profile(array $roles): array
    {
        foreach (['ADMINISTRATOR' => ['Administrador', 'pink'], 'COORDINATOR' => ['Coordinador', 'violet'], 'SUPERVISOR' => ['Supervisor', 'emerald'], 'OPERATOR' => ['Operador', 'blue'], 'AUDITOR' => ['Auditor', 'amber'], 'DEVELOPER' => ['Soporte técnico', 'cyan']] as $role => [$label, $accent]) {
            if (in_array($role, $roles, true)) return ['label' => $label, 'accent' => $accent];
        }
        return ['label' => 'Usuario', 'accent' => 'slate'];
    }
}
