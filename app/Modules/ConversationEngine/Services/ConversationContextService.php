<?php

namespace Modules\ConversationEngine\Services;

use Modules\Conversations\Models\ConversationContextModel;

class ConversationContextService
{
    public function put(int $conversationId, string $key,  mixed $value): void {

        $model = new ConversationContextModel();

        $existing = $model

            ->where('conversation_id', $conversationId)

            ->where('context_key', $key)

            ->first();

        if ($existing) {

            $model->update($existing['id'], [

                'context_value' => json_encode($value),

            ]);

            return;
        }

        $model->insert([

            'conversation_id' => $conversationId,

            'context_key' => $key,

            'context_value' => json_encode($value),

        ]);
    }

    public function get(int $conversationId, string $key): mixed {

        $row = (new ConversationContextModel())

            ->where('conversation_id', $conversationId)

            ->where('context_key', $key)

            ->first();

        if (!$row) {

            return null;

        }

        return json_decode($row['context_value'], true);
    }

    public function all(int $conversationId): array {

        $rows = (new ConversationContextModel())

            ->where('conversation_id', $conversationId)

            ->findAll();

        $context = [];

        foreach ($rows as $row) {

            $context[$row['context_key']] =
                json_decode($row['context_value'], true);

        }

        return $context;
    }
}