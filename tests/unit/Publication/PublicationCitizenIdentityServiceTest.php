<?php

declare(strict_types=1);

namespace Tests\Unit\Publication;

use CodeIgniter\Test\CIUnitTestCase;
use Modules\Publication\Application\PublicationCitizenIdentityService;

final class PublicationCitizenIdentityServiceTest extends CIUnitTestCase
{
    public function testEmptyProfileDoesNotRequireDatabaseLookup(): void
    {
        $service = new PublicationCitizenIdentityService();

        $result = $service->enrich([
            'comments' => [],
            'participants' => [],
        ]);

        $this->assertSame([], $result['comments']);
        $this->assertSame([], $result['participants']);
        $this->assertSame(0, $result['identity_metrics']['identified_participants']);
        $this->assertSame(0, $result['identity_metrics']['unidentified_participants']);
    }
}
