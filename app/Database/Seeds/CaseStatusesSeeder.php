<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CaseStatusesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Nuevo', 'slug' => 'nuevo'],
            ['name' => 'En revisión', 'slug' => 'en-revision'],
            ['name' => 'Asignado', 'slug' => 'asignado'],
            ['name' => 'En proceso', 'slug' => 'en-proceso'],
            ['name' => 'Resuelto', 'slug' => 'resuelto'],
            ['name' => 'Cerrado', 'slug' => 'cerrado'],
        ];

        $this->db->table('case_statuses')->insertBatch($data);
    }
}
