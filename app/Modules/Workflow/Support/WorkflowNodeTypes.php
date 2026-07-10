<?php

namespace Modules\Workflow\Support;

class WorkflowNodeTypes
{
    public const MESSAGE = 'message';
    public const QUICK_REPLIES = 'quick_replies';
    public const CAPTURE_TEXT = 'capture_text';
    public const ACTION = 'action';
    public const DECISION = 'decision';
    public const END = 'end';

    public static function all(): array
    {
        return [
            self::MESSAGE,
            self::QUICK_REPLIES,
            self::CAPTURE_TEXT,
            self::ACTION,
            self::DECISION,
            self::END,
        ];
    }
}