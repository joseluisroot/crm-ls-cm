<?php

namespace Modules\Flow\DTO;

class FlowAdapterResponseDTO
{
    public function __construct(
        public ?string $text = null,
        public array $quickReplies = [],
        public ?int $caseId = null,
        public ?int $outboundMessageId = null,
        public string $engine = 'legacy',
        public bool $completed = false,
        public array $metadata = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'quick_replies' => $this->quickReplies,
            'case_id' => $this->caseId,
            'outbound_message_id' => $this->outboundMessageId,
            'engine' => $this->engine,
            'completed' => $this->completed,
            'metadata' => $this->metadata,
        ];
    }
}