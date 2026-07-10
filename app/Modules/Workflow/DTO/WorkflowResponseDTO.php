<?php

namespace Modules\Workflow\DTO;

class WorkflowResponseDTO
{
    public function __construct(
        public ?string $text = null,
        public array $quickReplies = [],
        public ?int $executionId = null,
        public ?string $currentNodeKey = null,
        public bool $completed = false,
        public ?int $caseId = null,
        public array $metadata = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'text'             => $this->text,
            'quick_replies'    => $this->quickReplies,
            'execution_id'     => $this->executionId,
            'current_node_key' => $this->currentNodeKey,
            'completed'        => $this->completed,
            'case_id'          => $this->caseId,
            'metadata'         => $this->metadata,
        ];
    }
}