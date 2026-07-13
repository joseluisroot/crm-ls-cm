<?php

namespace Modules\Citizen\Domain\ValueObjects;

use InvalidArgumentException;

final class ActorType
{
    public const CITIZEN = 'CITIZEN';
    public const INSTITUTION = 'INSTITUTION';
    public const OPERATOR = 'OPERATOR';
    public const SYSTEM = 'SYSTEM';
    public const AI = 'AI';

    private const VALUES = [
        self::CITIZEN,
        self::INSTITUTION,
        self::OPERATOR,
        self::SYSTEM,
        self::AI,
    ];

    public function __construct(private readonly string $value)
    {
        if (! in_array($value, self::VALUES, true)) {
            throw new InvalidArgumentException('Invalid actor type: ' . $value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
