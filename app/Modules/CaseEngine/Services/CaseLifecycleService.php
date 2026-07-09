<?php

namespace Modules\CaseEngine\Services;

use Modules\Cases\Models\CaseHistoryModel;
use Modules\Cases\Models\CaseModel;

class CaseLifecycleService
{
    public function registerCreated(int $caseId, ?string $description = null): void
    {
        $this->addHistory(
            caseId: $caseId,
            event: 'case_created',
            description: $description ?? 'Caso creado automáticamente desde una conversación ciudadana.',
            performedBy: 'system'
        );
    }

    public function addHistory(
        int $caseId,
        string $event,
        ?string $description = null,
        ?string $performedBy = 'system'
    ): void {
        (new CaseHistoryModel())->insert([
            'case_id'      => $caseId,
            'event'        => $event,
            'description'  => $description,
            'performed_by' => $performedBy,
        ]);
    }

    public function changeStatus(
        int $caseId,
        int $statusId,
        ?string $description = null,
        ?string $performedBy = 'system'
    ): bool {
        $updated = (new CaseModel())->update($caseId, [
            'status_id' => $statusId,
        ]);

        if ($updated) {
            $this->addHistory(
                caseId: $caseId,
                event: 'status_changed',
                description: $description ?? 'Estado del caso actualizado.',
                performedBy: $performedBy
            );
        }

        return (bool) $updated;
    }

    public function assign(
        int $caseId,
        string $assignedTo,
        ?string $description = null,
        ?string $performedBy = 'system'
    ): bool {
        $updated = (new CaseModel())->update($caseId, [
            'assigned_to' => $assignedTo,
        ]);

        if ($updated) {
            $this->addHistory(
                caseId: $caseId,
                event: 'case_assigned',
                description: $description ?? 'Caso asignado a ' . $assignedTo . '.',
                performedBy: $performedBy
            );
        }

        return (bool) $updated;
    }
}