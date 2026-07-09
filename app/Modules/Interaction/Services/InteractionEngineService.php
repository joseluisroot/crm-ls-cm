<?php

namespace Modules\Interaction\Services;

class InteractionEngineService
{
    public function buildQuickReplies(array $quickReplies): array
    {
        $formatted = [];

        foreach ($quickReplies as $reply) {
            $formatted[] = [
                'content_type' => 'text',
                'title'        => $reply['title'],
                'payload'      => $reply['payload'],
            ];
        }

        return $formatted;
    }
}