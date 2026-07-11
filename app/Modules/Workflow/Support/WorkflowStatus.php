<?php

namespace Modules\Workflow\Support;

class WorkflowStatus
{
    public const DRAFT = 'draft';
    public const PUBLISHED = 'published';
    public const ARCHIVED = 'archived';

    public const EXECUTION_RUNNING = 'running';
    public const EXECUTION_COMPLETED = 'completed';
    public const EXECUTION_CANCELLED = 'cancelled';
    public const EXECUTION_FAILED = 'failed';
}