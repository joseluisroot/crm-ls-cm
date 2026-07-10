<?php

namespace Modules\Assignment\Services;

use Modules\Assignment\Exceptions\AssignmentException;
use Modules\Auth\Models\AdminUserModel;
use Modules\Cases\Models\CaseModel;
use Modules\CaseEngine\Services\CaseLifecycleService;
use Modules\Notification\Services\NotificationService;

class AssignmentEngineService
{
    public function assignCase(
        int $caseId,
        int $userId,
        ?int $performedByUserId = null
    ): bool {
        $caseModel = new CaseModel();
        $userModel = new AdminUserModel();

        $case = $caseModel->find($caseId);

        if (!$case) {
            throw new AssignmentException(
                'El caso seleccionado no existe.'
            );
        }

        $assignedUser = $userModel
            ->where('id', $userId)
            ->where('status', 'active')
            ->first();

        if (!$assignedUser) {
            throw new AssignmentException(
                'El usuario responsable no existe o está inactivo.'
            );
        }

        $performedBy = 'system';

        if ($performedByUserId) {
            $performedByUser = $userModel->find($performedByUserId);

            if ($performedByUser) {
                $performedBy = $performedByUser['name'];
            }
        }

        $updated = $caseModel->update($caseId, [
            'assigned_user_id' => $assignedUser['id'],

            // Compatibilidad temporal con la columna antigua.
            'assigned_to' => $assignedUser['name'],
        ]);

        if (!$updated) {
            throw new AssignmentException(
                'No fue posible asignar el caso.'
            );
        }

        (new CaseLifecycleService())->addHistory(
            caseId: $caseId,
            event: 'case_assigned',
            description: sprintf(
                'Caso asignado a %s.',
                $assignedUser['name']
            ),
            performedBy: $performedBy
        );

        (new NotificationService())->createInternal(
            subject: 'Nuevo caso asignado',
            body: sprintf(
                'Se te asignó el caso %s.',
                $case['public_code'] ?? '#' . $caseId
            ),
            recipientId: (string) $assignedUser['id'],
            payload: [
                'case_id'          => $caseId,
                'event'            => 'case_assigned',
                'assigned_user_id' => $assignedUser['id'],
            ]
        );

        return true;
    }

    public function unassignCase(
        int $caseId,
        ?int $performedByUserId = null
    ): bool {
        $caseModel = new CaseModel();
        $userModel = new AdminUserModel();

        $case = $caseModel->find($caseId);

        if (!$case) {
            throw new AssignmentException(
                'El caso seleccionado no existe.'
            );
        }

        $performedBy = 'system';

        if ($performedByUserId) {
            $performedByUser = $userModel->find($performedByUserId);

            if ($performedByUser) {
                $performedBy = $performedByUser['name'];
            }
        }

        $updated = $caseModel->update($caseId, [
            'assigned_user_id' => null,
            'assigned_to'      => null,
        ]);

        if (!$updated) {
            throw new AssignmentException(
                'No fue posible retirar la asignación.'
            );
        }

        (new CaseLifecycleService())->addHistory(
            caseId: $caseId,
            event: 'case_unassigned',
            description: 'Se retiró el responsable asignado al caso.',
            performedBy: $performedBy
        );

        return true;
    }

    public function getUserCases(
        int $userId,
        ?string $status = null
    ): array {
        $caseModel = new CaseModel();

        $builder = $caseModel
            ->select([
                'cases.*',
                'case_statuses.name AS status_name',
                'case_statuses.slug AS status_slug',
                'categories.name AS category_name',
                'citizens.name AS citizen_name',
            ])
            ->join(
                'case_statuses',
                'case_statuses.id = cases.status_id'
            )
            ->join(
                'categories',
                'categories.id = cases.category_id',
                'left'
            )
            ->join(
                'citizens',
                'citizens.id = cases.citizen_id'
            )
            ->where(
                'cases.assigned_user_id',
                $userId
            );

        if ($status !== null && $status !== '') {
            $builder->where(
                'case_statuses.slug',
                $status
            );
        }

        return $builder
            ->orderBy('cases.updated_at', 'DESC')
            ->findAll();
    }
}