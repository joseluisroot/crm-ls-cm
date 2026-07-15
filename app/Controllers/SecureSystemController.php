<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

final class SecureSystemController extends SystemController
{
    private const TOKEN_HEADER = 'X-System-Token';

    public function migrate(): ResponseInterface
    {
        if ($response = $this->authorizeSystemAction()) {
            return $response;
        }

        return $this->withExecutionLock('migrate', fn (): ResponseInterface => parent::migrate());
    }

    public function seed(string $seederName = ''): ResponseInterface
    {
        if ($response = $this->authorizeSystemAction()) {
            return $response;
        }

        $seederName = trim($seederName);
        $allowedSeeders = $this->allowedSeeders();

        if ($seederName === '' || ! in_array($seederName, $allowedSeeders, true)) {
            log_message('warning', 'Intento de ejecutar un seeder no autorizado. Seeder: {seeder}. IP: {ip}', [
                'seeder' => $seederName,
                'ip' => $this->request->getIPAddress(),
            ]);

            return $this->securityError('Seeder no autorizado.', 403);
        }

        return $this->withExecutionLock(
            'seed-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $seederName),
            fn (): ResponseInterface => parent::seed($seederName)
        );
    }

    private function authorizeSystemAction(): ?ResponseInterface
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->securityError('Método no permitido.', 405);
        }

        if (! filter_var(env('SYSTEM_ACTIONS_ENABLED', false), FILTER_VALIDATE_BOOL)) {
            log_message('warning', 'Acción del sistema bloqueada porque SYSTEM_ACTIONS_ENABLED está deshabilitado. IP: {ip}', [
                'ip' => $this->request->getIPAddress(),
            ]);

            return $this->securityError('Las acciones administrativas del sistema están deshabilitadas.', 503);
        }

        if (! $this->request->isSecure()) {
            return $this->securityError('Se requiere una conexión HTTPS.', 400);
        }

        $configuredToken = trim((string) env('SYSTEM_MIGRATION_TOKEN'));
        $receivedToken = trim($this->request->getHeaderLine(self::TOKEN_HEADER));

        if ($configuredToken === '' || $receivedToken === '' || ! hash_equals($configuredToken, $receivedToken)) {
            log_message('warning', 'Intento no autorizado de ejecutar una acción del sistema. IP: {ip}', [
                'ip' => $this->request->getIPAddress(),
            ]);

            return $this->securityError('Acceso no autorizado.', 403);
        }

        $allowedIps = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('SYSTEM_ALLOWED_IPS', ''))
        )));

        if ($allowedIps !== [] && ! in_array($this->request->getIPAddress(), $allowedIps, true)) {
            log_message('warning', 'IP no autorizada para acciones del sistema: {ip}', [
                'ip' => $this->request->getIPAddress(),
            ]);

            return $this->securityError('Origen no autorizado.', 403);
        }

        return null;
    }

    private function allowedSeeders(): array
    {
        return array_values(array_unique(array_filter(array_map(
            'trim',
            explode(',', (string) env('SYSTEM_ALLOWED_SEEDERS', ''))
        ))));
    }

    private function withExecutionLock(string $action, callable $callback): ResponseInterface
    {
        $lockDirectory = WRITEPATH . 'locks';

        if (! is_dir($lockDirectory) && ! mkdir($lockDirectory, 0750, true) && ! is_dir($lockDirectory)) {
            return $this->securityError('No fue posible preparar el bloqueo de ejecución.', 500);
        }

        $lockPath = $lockDirectory . DIRECTORY_SEPARATOR . 'system-' . $action . '.lock';
        $handle = fopen($lockPath, 'c');

        if ($handle === false) {
            return $this->securityError('No fue posible crear el bloqueo de ejecución.', 500);
        }

        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return $this->securityError('Ya existe una acción del sistema en ejecución.', 409);
        }

        try {
            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function securityError(string $message, int $statusCode): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'success' => false,
                'message' => $message,
            ]);
    }
}
