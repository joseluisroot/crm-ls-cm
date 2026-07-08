<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Saludos y felicitaciones', 'slug' => 'saludos-felicitaciones'],
            ['name' => 'Necesidad comunitaria', 'slug' => 'necesidad-comunitaria'],
            ['name' => 'Solicitud de apoyo', 'slug' => 'solicitud-apoyo'],
            ['name' => 'Crítica constructiva', 'slug' => 'critica-constructiva'],
            ['name' => 'Mensaje negativo', 'slug' => 'mensaje-negativo'],
            ['name' => 'Invitación a evento', 'slug' => 'invitacion-evento'],
            ['name' => 'Propuesta ciudadana', 'slug' => 'propuesta-ciudadana'],
            ['name' => 'Otro', 'slug' => 'otro'],
        ];

        $this->db->table('categories')->insertBatch($data);
    }
}
