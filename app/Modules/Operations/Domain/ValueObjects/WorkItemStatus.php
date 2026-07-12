<?php

namespace Modules\Operations\Domain\ValueObjects;

use InvalidArgumentException;

final class WorkItemStatus
{
    public const NEW = 'NEW';
    public const PENDING = 'PENDING';
    public const ASSIGNED = 'ASSIGNED';
    public const IN_PROGRESS = 'IN_PROGRESS';
    public const WAITING_CITIZEN = 'WAITING_CITIZEN';
    public const WAITING_INTERNAL = 'WAITING_INTERNAL';
    public const CASE_CREATED = 'CASE_CREATED';
    public const RESOLVED = 'RESOLVED';
    public const CLOSED = 'CLOSED';
    public const ARCHIVED = 'ARCHIVED';

    private const VALUES = [
        self::NEW,
        self::PENDING,
        self::ASSIGNED,
        self::IN_PROGRESS,
        self::WAITING_CITIZEN,
        self::WAITING_INTERNAL,
        self::CASE_CREATED,
        self::RESOLVED,
        self::CLOSED,
        self::ARCHIVED,
    ];

    public function __construct(private readonly string $value)
    {
        if (! in_array($value, self::VALUES, true)) {
            throw new InvalidArgumentException('Invalid work item status: ' . $value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isTerminal(): bool
    {
        return in_array($this->value, [self::CLOSED, self::ARCHIVED], true);
    }
}
