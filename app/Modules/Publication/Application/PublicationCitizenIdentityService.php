<?php

declare(strict_types=1);

namespace Modules\Publication\Application;

use CodeIgniter\Database\BaseConnection;
use Modules\Citizen\Domain\ValueObjects\IdentityChannel;

final class PublicationCitizenIdentityService
{
    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function enrich(array $profile): array
    {
        $externalIds = $this->collectExternalIds(
            $profile['comments'] ?? [],
            $profile['participants'] ?? [],
        );

        $identityMap = $this->identityMap($externalIds);

        $profile['comments'] = array_map(
            fn (array $comment): array => $this->enrichRecord($comment, 'author_external_id', $identityMap),
            $profile['comments'] ?? [],
        );

        $profile['participants'] = array_map(
            fn (array $participant): array => $this->enrichRecord($participant, 'external_id', $identityMap),
            $profile['participants'] ?? [],
        );

        $profile['identity_metrics'] = [
            'identified_participants' => count(array_filter(
                $profile['participants'],
                static fn (array $participant): bool => ! empty($participant['citizen_id']),
            )),
            'unidentified_participants' => count(array_filter(
                $profile['participants'],
                static fn (array $participant): bool => empty($participant['citizen_id']),
            )),
        ];

        return $profile;
    }

    private function collectExternalIds(array $comments, array $participants): array
    {
        $ids = [];

        foreach ($comments as $comment) {
            $externalId = trim((string) ($comment['author_external_id'] ?? ''));
            if ($externalId !== '') {
                $ids[$externalId] = true;
            }
        }

        foreach ($participants as $participant) {
            $externalId = trim((string) ($participant['external_id'] ?? ''));
            if ($externalId !== '') {
                $ids[$externalId] = true;
            }
        }

        return array_keys($ids);
    }

    private function identityMap(array $externalIds): array
    {
        if ($externalIds === []) {
            return [];
        }

        $rows = $this->connection()
            ->table('citizen_social_identities i')
            ->select('i.external_id, i.citizen_id, i.display_name, i.actor_type, i.confidence, c.name AS citizen_name')
            ->join('citizens c', 'c.id = i.citizen_id', 'left')
            ->where('i.channel', IdentityChannel::FACEBOOK)
            ->where('i.is_active', 1)
            ->whereIn('i.external_id', $externalIds)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['external_id']] = [
                'citizen_id' => (int) $row['citizen_id'],
                'citizen_name' => trim((string) ($row['citizen_name'] ?: $row['display_name'] ?: 'Ciudadano')),
                'identity_actor_type' => (string) ($row['actor_type'] ?? 'CITIZEN'),
                'identity_confidence' => (int) ($row['confidence'] ?? 0),
            ];
        }

        return $map;
    }

    private function enrichRecord(array $record, string $externalIdKey, array $identityMap): array
    {
        $externalId = trim((string) ($record[$externalIdKey] ?? ''));
        $identity = $externalId !== '' ? ($identityMap[$externalId] ?? null) : null;

        return [
            ...$record,
            'citizen_id' => $identity['citizen_id'] ?? null,
            'citizen_name' => $identity['citizen_name'] ?? null,
            'identity_actor_type' => $identity['identity_actor_type'] ?? null,
            'identity_confidence' => $identity['identity_confidence'] ?? null,
            'identity_resolved' => $identity !== null,
        ];
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}
