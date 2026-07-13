<?php

namespace Modules\Citizen\Domain\ValueObjects;

use InvalidArgumentException;

final class IdentityConfidence
{
    public const EXACT = 100;
    public const HIGH = 90;
    public const MEDIUM = 70;
    public const LOW = 40;

    public function __construct(private readonly int $value)
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException('Identity confidence must be between 0 and 100.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
