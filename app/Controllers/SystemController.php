<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use Config\Services;
use Throwable;

class SystemController extends Controller
{
    /**
     * Nombre de la tabla utilizada por CodeIgniter
     * para registrar las migraciones ejecutadas.
     */
    private string $migrationTable = 'migrations';

    /**
     * Valida el token de acceso.
     *
     * Se recomienda enviar el token mediante el encabezado:
     *
     * X-System-Token: TOKEN_CONFIGURADO
     *
     * Por compatibilidad también acepta:
     *
     * ?token=TOKEN_CONFIGURADO
     */
    private function validateToken(): ?ResponseInterface
    {
        $configuredToken = trim((string) env('SYSTEM_MIGRATION_TOKEN'));

        if ($configuredToken === '') {
            log_message(
                'critical',
                'SYSTEM_MIGRATION_TOKEN no está configurado.'
            );

            return $this->renderError(
                'Configuración inválida',
                'El token del sistema no está configurado.',
                500
            );
        }

        $receivedToken = trim(
            (string) (
            $this->request->getHeaderLine('X-System-Token')
                ?: $this->request->getGet('token')
            )
        );

        if (
            $receivedToken === ''
            || ! hash_equals($configuredToken, $receivedToken)
        ) {
            log_message(
                'warning',
                'Intento no autorizado de ejecutar una acción del sistema. IP: {ip}',
                [
                    'ip' => $this->request->getIPAddress(),
                ]
            );

            return $this->renderError(
                'Acceso no autorizado',
                'El token proporcionado no es válido.',
                403
            );
        }

        return null;
    }

    /**
     * Ejecuta todas las migraciones pendientes.
     */
    public function migrate(): ResponseInterface
    {
        if ($response = $this->validateToken()) {
            return $response;
        }

        $startTime = microtime(true);
        $db        = Database::connect();

        try {
            $before = $this->getExecutedMigrations($db);

            $migrationRunner = Services::migrations();

            /*
             * latest() devuelve false cuando ocurre un error.
             * Cuando no hay migraciones pendientes puede devolver true
             * sin registrar nuevos elementos.
             */
            $result = $migrationRunner->latest();

            if ($result === false) {
                throw new \RuntimeException(
                    'CodeIgniter no pudo completar las migraciones.'
                );
            }

            $after = $this->getExecutedMigrations($db);

            $executedNow = $this->findNewMigrations($before, $after);

            $elapsedTime = microtime(true) - $startTime;

            log_message(
                'info',
                'Migraciones ejecutadas. Nuevas: {count}. Duración: {time} segundos. IP: {ip}',
                [
                    'count' => count($executedNow),
                    'time'  => number_format($elapsedTime, 4),
                    'ip'    => $this->request->getIPAddress(),
                ]
            );

            return $this->renderMigrationReport(
                before: $before,
                after: $after,
                executedNow: $executedNow,
                executionTime: $elapsedTime,
                db: $db
            );
        } catch (Throwable $e) {
            $elapsedTime = microtime(true) - $startTime;

            log_message(
                'error',
                'Error ejecutando migraciones: {message}. Archivo: {file}. Línea: {line}',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );

            return $this->renderException(
                title: 'Error ejecutando migraciones',
                exception: $e,
                executionTime: $elapsedTime
            );
        }
    }

    /**
     * Ejecuta un seeder específico.
     *
     * Ejemplo:
     *
     * /system/seed/UsersSeeder
     */
    public function seed(string $seederName = ''): ResponseInterface
    {
        if ($response = $this->validateToken()) {
            return $response;
        }

        $startTime = microtime(true);

        try {
            $seederName = trim($seederName);

            if ($seederName === '') {
                return $this->renderError(
                    'Seeder inválido',
                    'Debe indicar el nombre del seeder.',
                    400
                );
            }

            if (! $this->isValidSeederName($seederName)) {
                return $this->renderError(
                    'Seeder inválido',
                    'El nombre del seeder contiene caracteres no permitidos.',
                    400
                );
            }

            $seeder = Database::seeder();
            $seeder->call($seederName);

            $elapsedTime = microtime(true) - $startTime;

            log_message(
                'info',
                'Seeder ejecutado: {seeder}. Duración: {time} segundos. IP: {ip}',
                [
                    'seeder' => $seederName,
                    'time'   => number_format($elapsedTime, 4),
                    'ip'     => $this->request->getIPAddress(),
                ]
            );

            return $this->renderSeederReport(
                seederName: $seederName,
                executionTime: $elapsedTime
            );
        } catch (Throwable $e) {
            $elapsedTime = microtime(true) - $startTime;

            log_message(
                'error',
                'Error ejecutando seeder {seeder}: {message}',
                [
                    'seeder'  => $seederName,
                    'message' => $e->getMessage(),
                ]
            );

            return $this->renderException(
                title: 'Error ejecutando seeder',
                exception: $e,
                executionTime: $elapsedTime,
                extraData: [
                    'Seeder solicitado' => $seederName,
                ]
            );
        }
    }

    /**
     * Consulta las migraciones registradas en la base de datos.
     */
    private function getExecutedMigrations(BaseConnection $db): array
    {
        if (! $db->tableExists($this->migrationTable)) {
            return [];
        }

        return $db
            ->table($this->migrationTable)
            ->orderBy('batch', 'ASC')
            ->orderBy('version', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Compara el estado anterior y posterior para determinar
     * cuáles migraciones fueron ejecutadas en esta solicitud.
     */
    private function findNewMigrations(array $before, array $after): array
    {
        $existingKeys = [];

        foreach ($before as $migration) {
            $existingKeys[$this->makeMigrationKey($migration)] = true;
        }

        return array_values(
            array_filter(
                $after,
                fn (array $migration): bool =>
                ! isset($existingKeys[$this->makeMigrationKey($migration)])
            )
        );
    }

    /**
     * Genera una llave única para comparar migraciones.
     */
    private function makeMigrationKey(array $migration): string
    {
        return implode('|', [
            $migration['version'] ?? '',
            $migration['class'] ?? '',
            $migration['group'] ?? '',
            $migration['namespace'] ?? '',
            $migration['batch'] ?? '',
        ]);
    }

    /**
     * Evita nombres maliciosos o rutas arbitrarias.
     *
     * Permite:
     *
     * UsersSeeder
     * App\Database\Seeds\UsersSeeder
     */
    private function isValidSeederName(string $seederName): bool
    {
        return preg_match(
                '/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/',
                $seederName
            ) === 1;
    }

    /**
     * Renderiza el reporte de migraciones.
     */
    private function renderMigrationReport(
        array $before,
        array $after,
        array $executedNow,
        float $executionTime,
        BaseConnection $db
    ): ResponseInterface {
        $databaseName = $this->getDatabaseName($db);
        $driverName   = $db->DBDriver ?? 'Desconocido';
        $environment  = defined('ENVIRONMENT')
            ? ENVIRONMENT
            : 'Desconocido';

        $statusMessage = count($executedNow) > 0
            ? 'Migraciones ejecutadas correctamente.'
            : 'La base de datos ya estaba actualizada. No había migraciones pendientes.';

        $content = '
            <div class="alert success">
                <strong>' . esc($statusMessage) . '</strong>
            </div>

            <div class="summary-grid">
                ' . $this->summaryCard(
                'Migraciones anteriores',
                (string) count($before)
            ) . '

                ' . $this->summaryCard(
                'Ejecutadas ahora',
                (string) count($executedNow)
            ) . '

                ' . $this->summaryCard(
                'Total registradas',
                (string) count($after)
            ) . '

                ' . $this->summaryCard(
                'Tiempo',
                number_format($executionTime, 4) . ' s'
            ) . '
            </div>

            <h2>Información de ejecución</h2>

            <div class="details">
                <div>
                    <span>Ambiente</span>
                    <strong>' . esc($environment) . '</strong>
                </div>

                <div>
                    <span>Base de datos</span>
                    <strong>' . esc($databaseName) . '</strong>
                </div>

                <div>
                    <span>Driver</span>
                    <strong>' . esc((string) $driverName) . '</strong>
                </div>

                <div>
                    <span>Fecha y hora</span>
                    <strong>' . esc(date('Y-m-d H:i:s')) . '</strong>
                </div>

                <div>
                    <span>IP solicitante</span>
                    <strong>' . esc($this->request->getIPAddress()) . '</strong>
                </div>

                <div>
                    <span>Versión PHP</span>
                    <strong>' . esc(PHP_VERSION) . '</strong>
                </div>

                <div>
                    <span>Versión CodeIgniter</span>
                    <strong>' . esc(\CodeIgniter\CodeIgniter::CI_VERSION) . '</strong>
                </div>

                <div>
                    <span>Memoria máxima utilizada</span>
                    <strong>' . esc($this->formatBytes(memory_get_peak_usage(true))) . '</strong>
                </div>
            </div>

            <h2>Migraciones ejecutadas en esta solicitud</h2>
            ' . $this->renderMigrationTable(
                $executedNow,
                'No se ejecutaron nuevas migraciones.'
            ) . '

            <h2>Historial completo de migraciones</h2>
            ' . $this->renderMigrationTable(
                $after,
                'Todavía no hay migraciones registradas.'
            ) . '
        ';

        return $this->renderPage(
            title: 'Reporte de migraciones',
            content: $content,
            statusCode: 200
        );
    }

    /**
     * Renderiza la tabla HTML de migraciones.
     */
    private function renderMigrationTable(
        array $migrations,
        string $emptyMessage
    ): string {
        if ($migrations === []) {
            return '
                <div class="empty-state">
                    ' . esc($emptyMessage) . '
                </div>
            ';
        }

        $rows = '';

        foreach ($migrations as $index => $migration) {
            $rows .= '
                <tr>
                    <td>' . esc((string) ($index + 1)) . '</td>
                    <td>
                        <code>' . esc((string) ($migration['version'] ?? 'N/D')) . '</code>
                    </td>
                    <td>' . esc((string) ($migration['class'] ?? 'N/D')) . '</td>
                    <td>' . esc((string) ($migration['namespace'] ?? 'N/D')) . '</td>
                    <td>' . esc((string) ($migration['group'] ?? 'default')) . '</td>
                    <td>
                        <span class="batch">
                            ' . esc((string) ($migration['batch'] ?? 'N/D')) . '
                        </span>
                    </td>
                    <td>' . esc(
                    $this->formatMigrationTime($migration['time'] ?? null)
                ) . '</td>
                </tr>
            ';
        }

        return '
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Versión</th>
                            <th>Clase</th>
                            <th>Namespace</th>
                            <th>Grupo</th>
                            <th>Batch</th>
                            <th>Ejecutada</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $rows . '
                    </tbody>
                </table>
            </div>
        ';
    }

    /**
     * Renderiza el reporte del seeder.
     */
    private function renderSeederReport(
        string $seederName,
        float $executionTime
    ): ResponseInterface {
        $content = '
            <div class="alert success">
                <strong>Seeder ejecutado correctamente.</strong>
            </div>

            <div class="summary-grid">
                ' . $this->summaryCard(
                'Seeder',
                esc($seederName)
            ) . '

                ' . $this->summaryCard(
                'Tiempo',
                number_format($executionTime, 4) . ' s'
            ) . '

                ' . $this->summaryCard(
                'Fecha',
                date('Y-m-d H:i:s')
            ) . '

                ' . $this->summaryCard(
                'Memoria máxima',
                $this->formatBytes(memory_get_peak_usage(true))
            ) . '
            </div>

            <h2>Información de ejecución</h2>

            <div class="details">
                <div>
                    <span>Seeder ejecutado</span>
                    <strong>' . esc($seederName) . '</strong>
                </div>

                <div>
                    <span>Ambiente</span>
                    <strong>' . esc(
                defined('ENVIRONMENT')
                    ? ENVIRONMENT
                    : 'Desconocido'
            ) . '</strong>
                </div>

                <div>
                    <span>IP solicitante</span>
                    <strong>' . esc($this->request->getIPAddress()) . '</strong>
                </div>

                <div>
                    <span>Versión PHP</span>
                    <strong>' . esc(PHP_VERSION) . '</strong>
                </div>
            </div>

            <div class="alert warning">
                <strong>Importante:</strong>
                CodeIgniter no registra automáticamente un historial de seeders.
                Si el seeder no está diseñado para ser idempotente, ejecutarlo
                varias veces podría duplicar información.
            </div>
        ';

        return $this->renderPage(
            title: 'Reporte de seeder',
            content: $content,
            statusCode: 200
        );
    }

    /**
     * Renderiza una excepción.
     */
    private function renderException(
        string $title,
        Throwable $exception,
        float $executionTime,
        array $extraData = []
    ): ResponseInterface {
        $showDetails = ENVIRONMENT !== 'production';

        $details = [
            'Mensaje'          => $exception->getMessage(),
            'Tiempo transcurrido' => number_format($executionTime, 4) . ' s',
        ];

        if ($showDetails) {
            $details['Archivo'] = $exception->getFile();
            $details['Línea']   = (string) $exception->getLine();
            $details['Tipo']    = get_class($exception);
        }

        $details = array_merge($extraData, $details);

        $detailHtml = '';

        foreach ($details as $label => $value) {
            $detailHtml .= '
                <div>
                    <span>' . esc($label) . '</span>
                    <strong>' . esc((string) $value) . '</strong>
                </div>
            ';
        }

        $content = '
            <div class="alert danger">
                <strong>' . esc($title) . '</strong>
                <p>
                    La operación no pudo completarse.
                    Revise el archivo de logs para obtener más información.
                </p>
            </div>

            <div class="details">
                ' . $detailHtml . '
            </div>
        ';

        return $this->renderPage(
            title: $title,
            content: $content,
            statusCode: 500
        );
    }

    /**
     * Renderiza una respuesta de error simple.
     */
    private function renderError(
        string $title,
        string $message,
        int $statusCode
    ): ResponseInterface {
        $content = '
            <div class="alert danger">
                <strong>' . esc($title) . '</strong>
                <p>' . esc($message) . '</p>
            </div>
        ';

        return $this->renderPage(
            title: $title,
            content: $content,
            statusCode: $statusCode
        );
    }

    /**
     * Genera una tarjeta de resumen.
     */
    private function summaryCard(string $label, string $value): string
    {
        return '
            <div class="summary-card">
                <span>' . esc($label) . '</span>
                <strong>' . $value . '</strong>
            </div>
        ';
    }

    /**
     * Plantilla general del reporte.
     */
    private function renderPage(
        string $title,
        string $content,
        int $statusCode
    ): ResponseInterface {
        $html = '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <title>' . esc($title) . '</title>

            <style>
                :root {
                    color-scheme: light;
                    font-family:
                        Inter,
                        ui-sans-serif,
                        system-ui,
                        -apple-system,
                        BlinkMacSystemFont,
                        "Segoe UI",
                        sans-serif;
                }

                * {
                    box-sizing: border-box;
                }

                body {
                    margin: 0;
                    padding: 32px 20px;
                    background: #f3f4f6;
                    color: #111827;
                }

                .container {
                    width: 100%;
                    max-width: 1300px;
                    margin: 0 auto;
                }

                .header {
                    margin-bottom: 24px;
                }

                .header h1 {
                    margin: 0 0 8px;
                    font-size: 30px;
                }

                .header p {
                    margin: 0;
                    color: #6b7280;
                }

                h2 {
                    margin-top: 32px;
                    margin-bottom: 14px;
                    font-size: 20px;
                }

                .alert {
                    padding: 16px 18px;
                    margin-bottom: 22px;
                    border: 1px solid transparent;
                    border-radius: 10px;
                    line-height: 1.5;
                }

                .alert p {
                    margin: 6px 0 0;
                }

                .alert.success {
                    color: #166534;
                    background: #dcfce7;
                    border-color: #86efac;
                }

                .alert.warning {
                    color: #854d0e;
                    background: #fef9c3;
                    border-color: #fde047;
                    margin-top: 24px;
                }

                .alert.danger {
                    color: #991b1b;
                    background: #fee2e2;
                    border-color: #fca5a5;
                }

                .summary-grid {
                    display: grid;
                    grid-template-columns:
                        repeat(auto-fit, minmax(190px, 1fr));
                    gap: 16px;
                }

                .summary-card {
                    padding: 20px;
                    background: #ffffff;
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }

                .summary-card span {
                    display: block;
                    margin-bottom: 8px;
                    color: #6b7280;
                    font-size: 13px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                }

                .summary-card strong {
                    display: block;
                    overflow-wrap: anywhere;
                    font-size: 22px;
                }

                .details {
                    display: grid;
                    grid-template-columns:
                        repeat(auto-fit, minmax(240px, 1fr));
                    gap: 1px;
                    overflow: hidden;
                    border: 1px solid #e5e7eb;
                    border-radius: 10px;
                    background: #e5e7eb;
                }

                .details div {
                    padding: 15px;
                    background: #ffffff;
                }

                .details span {
                    display: block;
                    margin-bottom: 5px;
                    color: #6b7280;
                    font-size: 13px;
                }

                .details strong {
                    display: block;
                    overflow-wrap: anywhere;
                }

                .table-container {
                    overflow-x: auto;
                    background: #ffffff;
                    border: 1px solid #e5e7eb;
                    border-radius: 10px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 14px;
                }

                th,
                td {
                    padding: 12px 14px;
                    border-bottom: 1px solid #e5e7eb;
                    text-align: left;
                    white-space: nowrap;
                }

                th {
                    color: #374151;
                    background: #f9fafb;
                    font-size: 12px;
                    text-transform: uppercase;
                    letter-spacing: 0.03em;
                }

                tr:last-child td {
                    border-bottom: 0;
                }

                tr:hover td {
                    background: #f9fafb;
                }

                code {
                    padding: 3px 6px;
                    border-radius: 5px;
                    background: #f3f4f6;
                    font-family: Consolas, monospace;
                }

                .batch {
                    display: inline-flex;
                    min-width: 28px;
                    height: 28px;
                    align-items: center;
                    justify-content: center;
                    padding: 0 8px;
                    border-radius: 999px;
                    color: #1e3a8a;
                    background: #dbeafe;
                    font-weight: 700;
                }

                .empty-state {
                    padding: 26px;
                    border: 1px dashed #d1d5db;
                    border-radius: 10px;
                    color: #6b7280;
                    background: #ffffff;
                    text-align: center;
                }

                .footer {
                    margin-top: 32px;
                    color: #9ca3af;
                    font-size: 12px;
                    text-align: center;
                }

                @media (max-width: 640px) {
                    body {
                        padding: 18px 12px;
                    }

                    .header h1 {
                        font-size: 24px;
                    }

                    th,
                    td {
                        padding: 10px;
                    }
                }
            </style>
        </head>

        <body>
            <main class="container">
                <header class="header">
                    <h1>' . esc($title) . '</h1>
                    <p>
                        Herramienta de mantenimiento y diagnóstico del sistema.
                    </p>
                </header>

                ' . $content . '

                <footer class="footer">
                    Generado el ' . esc(date('Y-m-d H:i:s')) . '
                </footer>
            </main>
        </body>
        </html>';

        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('text/html', 'UTF-8')
            ->setBody($html);
    }

    /**
     * Obtiene el nombre de la base de datos sin exponer
     * usuario ni contraseña.
     */
    private function getDatabaseName(BaseConnection $db): string
    {
        return isset($db->database) && $db->database !== ''
            ? (string) $db->database
            : 'No disponible';
    }

    /**
     * Convierte el timestamp de una migración a fecha legible.
     */
    private function formatMigrationTime(mixed $time): string
    {
        if ($time === null || $time === '') {
            return 'N/D';
        }

        if (is_numeric($time)) {
            return date('Y-m-d H:i:s', (int) $time);
        }

        return (string) $time;
    }

    /**
     * Convierte bytes a una unidad legible.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }

        return number_format($value, 2) . ' ' . $units[$index];
    }
}