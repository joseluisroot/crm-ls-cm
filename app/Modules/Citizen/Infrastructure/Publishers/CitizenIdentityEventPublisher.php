<?php

namespace Modules\Citizen\Infrastructure\Publishers;

final class CitizenIdentityEventPublisher
{
    public function created(int $citizenId, array $payload): void
    {
        service('eventEngine')->emit(
            name: 'citizen.identity.created',
            module: 'citizen',
            payload: $payload,
            entityType: 'citizen',
            entityId: $citizenId,
        );
    }

    public function resolved(int $citizenId, array $payload): void
    {
        service('eventEngine')->emit(
            name: 'citizen.identity.resolved',
            module: 'citizen',
            payload: $payload,
            entityType: 'citizen',
            entityId: $citizenId,
        );
    }
}
