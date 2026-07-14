<?php

declare(strict_types=1);

namespace Modules\Response\Application;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

final class ResponseContextResolver
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    public function resolve(int $workItemId): array
    {
        $item = $this->db->table('work_items wi')
            ->select('wi.*, ot.code AS origin_type, ch.code AS channel')
            ->join('work_item_origin_types ot', 'ot.id = wi.origin_type_id')
            ->join('work_item_channels ch', 'ch.id = wi.channel_id')
            ->where('wi.id', $workItemId)
            ->get()->getRowArray();

        if (! $item) throw new RuntimeException('La atención no existe.');
        if (empty($item['assigned_user_id'])) throw new RuntimeException('Asigna un operador antes de responder.');

        $originType = strtoupper((string) $item['origin_type']);
        if ($originType === 'FACEBOOK_COMMENT') {
            $comment = $this->db->table('social_comments')
                ->select('external_comment_id')
                ->where('external_comment_id', $item['origin_id'])
                ->get()->getRowArray();
            if (! $comment) throw new RuntimeException('No se encontró el comentario original de Facebook.');
            return [
                'item' => $item,
                'channel' => 'FACEBOOK',
                'response_mode' => 'PUBLIC',
                'recipient_external_id' => (string) $comment['external_comment_id'],
            ];
        }

        if (in_array($originType, ['MESSENGER', 'MESSENGER_MESSAGE'], true)) {
            $message = $this->db->table('messages m')
                ->select('m.external_message_id, ci.facebook_id')
                ->join('conversations co', 'co.id = m.conversation_id')
                ->join('citizens ci', 'ci.id = co.citizen_id')
                ->groupStart()
                    ->where('m.external_message_id', $item['origin_id'])
                    ->orWhere('m.id', is_numeric((string) $item['origin_id']) ? (int) $item['origin_id'] : 0)
                ->groupEnd()
                ->get()->getRowArray();
            if (! $message || empty($message['facebook_id'])) {
                throw new RuntimeException('No se encontró el destinatario Messenger de esta atención.');
            }
            return [
                'item' => $item,
                'channel' => 'MESSENGER',
                'response_mode' => 'PRIVATE',
                'recipient_external_id' => (string) $message['facebook_id'],
            ];
        }

        throw new RuntimeException('El canal de esta atención todavía no admite respuestas desde CIAC.');
    }

    public function capability(int $workItemId): array
    {
        try {
            $context = $this->resolve($workItemId);
            return [
                'ready' => true,
                'channel' => $context['channel'],
                'response_mode' => $context['response_mode'],
                'reason' => null,
            ];
        } catch (RuntimeException $e) {
            return ['ready' => false, 'channel' => null, 'response_mode' => null, 'reason' => $e->getMessage()];
        }
    }
}