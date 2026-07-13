<?php

declare(strict_types=1);

namespace Tests\Unit\Publication;

use Modules\Citizen\Application\CitizenResolverService;
use Modules\Publication\Application\ResolvePublicationParticipantsService;
use PHPUnit\Framework\TestCase;

final class ResolvePublicationParticipantsServiceTest extends TestCase
{
    public function testCandidatesOnlyContainUnresolvedParticipantsWithExternalId(): void
    {
        $resolver = $this->createMock(CitizenResolverService::class);
        $service = new ResolvePublicationParticipantsService($resolver);

        $candidates = $service->candidates([
            [
                'external_id' => 'facebook-1',
                'name' => 'Ana',
                'comments_count' => 2,
                'reactions_count' => 1,
                'identity_resolved' => false,
            ],
            [
                'external_id' => 'facebook-2',
                'name' => 'Luis',
                'citizen_id' => 15,
                'identity_resolved' => true,
            ],
            [
                'external_id' => '',
                'name' => 'Sin identificador',
            ],
            [
                'external_id' => 'facebook-1',
                'name' => 'Ana duplicada',
                'comments_count' => 3,
                'reactions_count' => 4,
            ],
        ]);

        self::assertCount(1, $candidates);
        self::assertSame('facebook-1', $candidates[0]['external_id']);
        self::assertSame('Ana duplicada', $candidates[0]['name']);
        self::assertSame(3, $candidates[0]['comments_count']);
        self::assertSame(4, $candidates[0]['reactions_count']);
    }
}
