<?php

namespace Modules\Citizen\Infrastructure\Repositories;

use Modules\Citizen\Domain\Entities\SocialIdentity;
use Modules\Citizen\Domain\Repositories\SocialIdentityRepositoryInterface;
use Modules\Citizen\Infrastructure\Models\SocialIdentityModel;
use RuntimeException;

final class DatabaseSocialIdentityRepository implements SocialIdentityRepositoryInterface
{
    public function __construct(private readonly ?SocialIdentityModel $model = null)
    {
    }

    public function findByChannelAndExternalId(string $channel, string $externalId): ?array
    {
        return $this->identityModel()
            ->where('channel', $channel)
            ->where('external_id', $externalId)
            ->where('is_active', 1)
            ->first() ?: null;
    }

    public function create(SocialIdentity $identity): int
    {
        $data = $identity->toArray();
        $data['metadata_json'] = json_encode(
            $data['metadata'] ?? [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '{}';
        unset($data['metadata']);

        $id = $this->identityModel()->insert($data, true);
        if (! $id) {
            throw new RuntimeException('Unable to persist social identity.');
        }

        return (int) $id;
    }

    private function identityModel(): SocialIdentityModel
    {
        return $this->model ?? new SocialIdentityModel();
    }
}
