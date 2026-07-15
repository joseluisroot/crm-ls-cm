<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

use CodeIgniter\Database\BaseConnection;
use InvalidArgumentException;

final class WorkItemNotesService
{
    public function __construct(private readonly ?BaseConnection $db = null) {}

    /** @return array<int, array<string, mixed>> */
    public function forWorkItem(int $workItemId): array
    {
        if ($workItemId <= 0 || ! $this->connection()->tableExists('work_item_notes')) return [];

        return $this->connection()->table('work_item_notes n')
            ->select('n.*, u.name AS author_name')
            ->join('admin_users u', 'u.id = n.author_user_id')
            ->where('n.work_item_id', $workItemId)
            ->orderBy('n.id', 'DESC')
            ->limit(50)
            ->get()->getResultArray();
    }

    public function add(int $workItemId, ?int $caseId, int $authorUserId, string $type, string $body): int
    {
        $body = trim($body);
        $type = strtoupper(trim($type));
        $allowed = ['GENERAL', 'CALL', 'VISIT', 'FOLLOW_UP', 'DOCUMENT'];

        if ($workItemId <= 0 || $authorUserId <= 0) throw new InvalidArgumentException('La atención y el autor son obligatorios.');
        if ($body === '' || mb_strlen($body) > 5000) throw new InvalidArgumentException('La nota debe contener entre 1 y 5000 caracteres.');
        if (! in_array($type, $allowed, true)) $type = 'GENERAL';

        $now = date('Y-m-d H:i:s');
        $db = $this->connection();
        $db->table('work_item_notes')->insert([
            'work_item_id' => $workItemId,
            'case_id' => $caseId ?: null,
            'author_user_id' => $authorUserId,
            'note_type' => $type,
            'body' => $body,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $noteId = (int) $db->insertID();

        if ($db->tableExists('system_events')) {
            $db->table('system_events')->insert([
                'entity_type' => 'work_item',
                'entity_id' => $workItemId,
                'event_name' => 'INTERNAL_NOTE_ADDED',
                'payload_json' => json_encode(['note_id' => $noteId, 'note_type' => $type, 'author_user_id' => $authorUserId], JSON_UNESCAPED_UNICODE),
                'published_at' => $now,
            ]);
        }

        return $noteId;
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? db_connect();
    }
}
