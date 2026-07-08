<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;

class SystemController extends Controller
{
    private function validateToken()
    {
        $token = $this->request->getGet('token');

        if ($token !== env('SYSTEM_MIGRATION_TOKEN')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Acceso no autorizado.');
        }

        return null;
    }

    public function migrate()
    {
        if ($response = $this->validateToken()) {
            return $response;
        }

        try {
            $migrations = Services::migrations();
            $migrations->latest();

            return $this->response->setBody('Migraciones ejecutadas correctamente.');
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setBody('Error ejecutando migraciones: ' . $e->getMessage());
        }
    }

    public function seed($seederName)
    {
        if ($response = $this->validateToken()) {
            return $response;
        }

        try {
            $seeder = \Config\Database::seeder();
            $seeder->call($seederName);

            return $this->response->setBody('Seeder ejecutado correctamente: ' . esc($seederName));
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setBody('Error ejecutando seeder: ' . $e->getMessage());
        }
    }
}