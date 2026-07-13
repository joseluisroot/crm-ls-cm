<?php

declare(strict_types=1);

namespace Modules\Citizen\Application\DTO;

final class CitizenCardDTO
{
    public function __construct(
        public readonly int $citizenId,
        public readonly string $name,
        public readonly ?string $primaryChannel,
        public readonly int $totalWorkItems,
        public readonly int $openWorkItems,
        public readonly int $totalCases,
        public readonly int $totalConversations,
        public readonly int $totalIdentities,
        public readonly ?string $lastActivity,
    ) {
    }
}
