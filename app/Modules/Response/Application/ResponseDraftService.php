<?php

declare(strict_types=1);

namespace Modules\Response\Application;

use CodeIgniter\Database\BaseConnection;
use InvalidArgumentException;

final class ResponseDraftService
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    public function findForWorkItem(int $workItemId): ?array
    {
        $row = $this->db->table('response_drafts')
            ->where('work_item_id', $workItemId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function save(int $workItemId, ?int $userId, string $channel, string $body): void
    {
        $body = trim($body);
        if ($body === '') {
            throw new InvalidArgumentException('El borrador no puede estar vacío.');
        }

        if (mb_strlen($body) > 5000) {
            throw new InvalidArgumentException('La respuesta no puede superar 5000 caracteres.');
        }

        $now = date('Y-m-d H:i:s');
        $existing = $this->findForWorkItem($workItemId);
        $data = [
            'user_id' => $userId,
            'channel' => strtoupper(trim($channel ?: 'UNKNOWN')),
            'body' => $body,
            'status' => 'DRAFT',
            'updated_at' => $now,
        ];

        if ($existing) {
            $this->db->table('response_drafts')->where('id', $existing['id'])->update($data);
            return;
        }

        $data['work_item_id'] = $workItemId;
        $data['created_at'] = $now;
        $this->db->table('response_drafts')->insert($data);
    }
}
