<?php

namespace Modules\Workflow\Support;

class WorkflowActions
{
    public const CREATE_CASE = 'create_case';
    public const SEND_NOTIFICATION = 'send_notification';
    public const ASSIGN_CASE = 'assign_case';
    public const CLOSE_CONVERSATION = 'close_conversation';

    public static function all(): array
    {
        return [
            self::CREATE_CASE,
            self::SEND_NOTIFICATION,
            self::ASSIGN_CASE,
            self::CLOSE_CONVERSATION,
        ];
    }

    public static function implemented(): array
    {
        return [
            self::CREATE_CASE,
        ];
    }
}