<?php

namespace Modules\Core\Event\DTO;

use CodeIgniter\I18n\Time;
use InvalidArgumentException;

final readonly class SystemEvent
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $module,
        public array $payload = [],
        public array $metadata = [],
        public ?string $entityType = null,
        public int|string|null $entityId = null,
        public ?string $correlationId = null,
        public ?string $causationId = null,
        public int $version = 1,
        public ?int $publishedBy = null,
        public ?string $publishedAt = null,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', $this->name)) {
            throw new InvalidArgumentException('Event name must use dot notation, for example workflow.node.completed.');
        }

        if ($this->version < 1) {
            throw new InvalidArgumentException('Event version must be greater than zero.');
        }
    }

    public static function create(
        string $name,
        string $module,
        array $payload = [],
        array $metadata = [],
        ?string $entityType = null,
        int|string|null $entityId = null,
        ?string $correlationId = null,
        ?string $causationId = null,
        int $version = 1,
        ?int $publishedBy = null,
    ): self {
        return new self(
            uuid: self::uuidV4(),
            name: $name,
            module: $module,
            payload: $payload,
            metadata: $metadata,
            entityType: $entityType,
            entityId: $entityId,
            correlationId: $correlationId,
            causationId: $causationId,
            version: $version,
            publishedBy: $publishedBy,
            publishedAt: Time::now()->toDateTimeString(),
        );
    }

    public function toArray(): array
    {
        return [
            'event_uuid'    => $this->uuid,
            'event_name'    => $this->name,
            'module'        => $this->module,
            'entity_type'   => $this->entityType,
            'entity_id'     => $this->entityId === null ? null : (string) $this->entityId,
            'correlation_id'=> $this->correlationId,
            'causation_id'  => $this->causationId,
            'payload_json'  => json_encode($this->payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'metadata_json' => json_encode($this->metadata, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'event_version' => $this->version,
            'published_by'  => $this->publishedBy,
            'published_at'  => $this->publishedAt ?? Time::now()->toDateTimeString(),
        ];
    }

    private static function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
