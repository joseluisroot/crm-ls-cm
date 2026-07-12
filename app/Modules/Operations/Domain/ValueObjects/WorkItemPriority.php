<?php

namespace Modules\Operations\Domain\ValueObjects;

use InvalidArgumentException;

final class WorkItemPriority
{
    public const LOW = 'LOW';
    public const NORMAL = 'NORMAL';
    public const HIGH = 'HIGH';
    public const CRITICAL = 'CRITICAL';

    private const VALUES = [self::LOW, self::NORMAL, self::HIGH, self::CRITICAL];

    public function __construct(private readonly string $value)
    {
        if (! in_array($value, self::VALUES, true)) {
            throw new InvalidArgumentException('Invalid work item priority: ' . $value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
