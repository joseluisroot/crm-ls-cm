<?php

namespace Modules\Flow\DTO;

class FlowResponseDTO
{
    public function __construct(
        public ?string $text = null,
        public array $quickReplies = [],
        public ?string $nextState = null
    ) {
    }
}