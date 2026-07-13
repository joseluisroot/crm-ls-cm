<?php

namespace Tests\Unit\Citizen;

use InvalidArgumentException;
use Modules\Citizen\Domain\Entities\SocialIdentity;
use Modules\Citizen\Domain\ValueObjects\ActorType;
use Modules\Citizen\Domain\ValueObjects\IdentityChannel;
use Modules\Citizen\Domain\ValueObjects\IdentityConfidence;
use PHPUnit\Framework\TestCase;

final class SocialIdentityTest extends TestCase
{
    public function testItSerializesAValidIdentity(): void
    {
        $identity = new SocialIdentity(
            uuid: 'a03cc574-2026-4e5b-8599-836fc407d8b5',
            citizenId: 10,
            channel: new IdentityChannel(IdentityChannel::FACEBOOK),
            externalId: 'facebook-user-1',
            displayName: 'Juan Pérez',
            actorType: new ActorType(ActorType::CITIZEN),
            confidence: new IdentityConfidence(IdentityConfidence::EXACT),
            metadata: ['source' => 'comment'],
        );

        $data = $identity->toArray();

        self::assertSame(10, $data['citizen_id']);
        self::assertSame('FACEBOOK', $data['channel']);
        self::assertSame('facebook-user-1', $data['external_id']);
        self::assertSame(100, $data['confidence']);
    }

    public function testItRejectsAnInvalidChannel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IdentityChannel('UNKNOWN');
    }

    public function testItRejectsConfidenceOutsideRange(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IdentityConfidence(101);
    }
}
