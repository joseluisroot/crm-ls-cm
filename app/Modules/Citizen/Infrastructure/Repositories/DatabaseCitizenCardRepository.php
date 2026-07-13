<?php

declare(strict_types=1);

namespace Modules\Citizen\Infrastructure\Repositories;

use CodeIgniter\Database\BaseConnection;
use Modules\Citizen\Application\Contracts\CitizenCardRepositoryInterface;
use Modules\Citizen\Application\DTO\CitizenCardDTO;

final class DatabaseCitizenCardRepository implements CitizenCardRepositoryInterface
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function find(int $citizenId): ?CitizenCardDTO
    {
        $db = $this->connection();
        $citizen = $db->table('citizens')->where('id', $citizenId)->get()->getRowArray();

        if (! $citizen) {
            return null;
        }

        $workItems = $db->table('work_items wi')
            ->select("COUNT(*) AS total_work_items, SUM(CASE WHEN st.code NOT IN ('RESOLVED','CLOSED','ARCHIVED') THEN 1 ELSE 0 END) AS open_work_items, MAX(wi.updated_at) AS last_work_item_at", false)
            ->join('work_item_statuses st', 'st.id = wi.status_id')
            ->where('wi.citizen_id', $citizenId)
            ->get()
            ->getRowArray() ?: [];

        $cases = (int) ($db->table('cases')->where('citizen_id', $citizenId)->countAllResults());
        $conversations = (int) ($db->table('conversations')->where('citizen_id', $citizenId)->countAllResults());
        $identities = (int) ($db->table('citizen_social_identities')->where('citizen_id', $citizenId)->where('is_active', 1)->countAllResults());

        $primaryIdentity = $db->table('citizen_social_identities')
            ->select('channel, updated_at')
            ->where('citizen_id', $citizenId)
            ->where('is_active', 1)
            ->orderBy('updated_at', 'DESC')
            ->get(1)
            ->getRowArray();

        $lastActivity = $this->latestDate([
            $citizen['updated_at'] ?? null,
            $workItems['last_work_item_at'] ?? null,
            $primaryIdentity['updated_at'] ?? null,
        ]);

        return new CitizenCardDTO(
            citizenId: $citizenId,
            name: (string) ($citizen['name'] ?: 'Ciudadano #' . $citizenId),
            primaryChannel: isset($primaryIdentity['channel']) ? (string) $primaryIdentity['channel'] : null,
            totalWorkItems: (int) ($workItems['total_work_items'] ?? 0),
            openWorkItems: (int) ($workItems['open_work_items'] ?? 0),
            totalCases: $cases,
            totalConversations: $conversations,
            totalIdentities: $identities,
            lastActivity: $lastActivity,
        );
    }

    private function latestDate(array $dates): ?string
    {
        $dates = array_values(array_filter($dates, static fn ($value): bool => is_string($value) && $value !== ''));
        if ($dates === []) {
            return null;
        }

        usort($dates, static fn (string $left, string $right): int => strcmp($right, $left));

        return $dates[0];
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}
