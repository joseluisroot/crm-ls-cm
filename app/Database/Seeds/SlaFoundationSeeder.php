<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Modules\Operations\Application\SlaClockService;

final class SlaFoundationSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('work_item_sla') || ! $this->db->tableExists('work_items')) return;

        $service = new SlaClockService($this->db);
        $rows = $this->db->table('work_items')
            ->select('id, assigned_user_id, opened_at, created_at')
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $service->ensureStarted(
                (int) $row['id'],
                ! empty($row['assigned_user_id']) ? (int) $row['assigned_user_id'] : null,
                $row['opened_at'] ?: $row['created_at'] ?: null,
            );
        }
    }
}
