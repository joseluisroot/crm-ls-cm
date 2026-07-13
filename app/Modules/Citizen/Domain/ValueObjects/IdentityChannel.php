<?php

namespace Modules\Citizen\Domain\ValueObjects;

use InvalidArgumentException;

final class IdentityChannel
{
    public const FACEBOOK = 'FACEBOOK';
    public const MESSENGER = 'MESSENGER';
    public const INSTAGRAM = 'INSTAGRAM';
    public const WHATSAPP = 'WHATSAPP';
    public const EMAIL = 'EMAIL';
    public const WEB = 'WEB';
    public const SYSTEM = 'SYSTEM';

    private const VALUES = [
        self::FACEBOOK,
        self::MESSENGER,
        self::INSTAGRAM,
        self::WHATSAPP,
        self::EMAIL,
        self::WEB,
        self::SYSTEM,
    ];

    public function __construct(private readonly string $value)
    {
        if (! in_array($value, self::VALUES, true)) {
            throw new InvalidArgumentException('Invalid identity channel: ' . $value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
