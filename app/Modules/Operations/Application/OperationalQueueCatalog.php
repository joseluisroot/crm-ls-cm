<?php

declare(strict_types=1);

namespace Modules\Operations\Application;

final class OperationalQueueCatalog
{
    /** @return array<string, array{label:string, description:string, codes:string[], tone:string}> */
    public function all(): array
    {
        return [
            'PENDING' => [
                'label' => 'Pendientes',
                'description' => 'Atenciones nuevas o todavía sin iniciar.',
                'codes' => ['NEW'],
                'tone' => 'red',
            ],
            'ACTIVE' => [
                'label' => 'En marcha',
                'description' => 'Atenciones asignadas que requieren trabajo del equipo.',
                'codes' => ['ASSIGNED', 'IN_PROGRESS', 'INVESTIGATING', 'DRAFT', 'PENDING_APPROVAL'],
                'tone' => 'blue',
            ],
            'WAITING' => [
                'label' => 'Esperando ciudadano',
                'description' => 'La institución ya actuó y espera información o respuesta.',
                'codes' => ['WAITING_CITIZEN', 'WAITING_INTERNAL', 'WAITING_THIRD_PARTY'],
                'tone' => 'amber',
            ],
            'COMPLETED' => [
                'label' => 'Completadas',
                'description' => 'Atenciones resueltas o cerradas, disponibles para consulta.',
                'codes' => ['RESPONDED', 'RESOLVED', 'CLOSED'],
                'tone' => 'green',
            ],
            'CANCELLED' => [
                'label' => 'Canceladas',
                'description' => 'Atenciones canceladas, duplicadas o que no proceden.',
                'codes' => ['CANCELLED', 'DUPLICATE', 'NOT_APPLICABLE'],
                'tone' => 'slate',
            ],
        ];
    }

    /** @return string[] */
    public function codesFor(?string $group): array
    {
        $group = strtoupper(trim((string) $group));
        return $this->all()[$group]['codes'] ?? [];
    }

    public function normalize(?string $group): string
    {
        $group = strtoupper(trim((string) $group));
        return isset($this->all()[$group]) ? $group : 'PENDING';
    }
}
