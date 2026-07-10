<?php

namespace Modules\Analytics\DTO;

class AnalyticsDashboardDTO
{
    public function __construct(
        public array $citizens,
        public array $conversations,
        public array $messages,
        public array $cases,
        public array $distribution,
        public array $trends,
        public array $indices,
    ) {
    }

    public function toArray(): array
    {
        return [
            'citizens'      => $this->citizens,
            'conversations' => $this->conversations,
            'messages'      => $this->messages,
            'cases'         => $this->cases,
            'distribution'  => $this->distribution,
            'trends'        => $this->trends,
            'indices'       => $this->indices,
        ];
    }
}