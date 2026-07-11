<?php

namespace Modules\Workflow\DTO;

class WorkflowValidationResultDTO
{
    public function __construct(
        public array $errors = [],
        public array $warnings = [],
        public array $information = [],
    ) {
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function addError(string $code, string $message, array $context = []): void
    {
        $this->errors[] = [
            'code' => $code,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function addWarning(string $code, string $message, array $context = []): void
    {
        $this->warnings[] = [
            'code' => $code,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function addInformation(string $code, string $message, array $context = []): void
    {
        $this->information[] = [
            'code' => $code,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'information' => $this->information,
        ];
    }
}