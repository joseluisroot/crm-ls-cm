<?php

namespace Modules\CaseEngine\Services;

use Modules\Cases\Models\CaseModel;
use Modules\Cases\Models\CategoryModel;
use Modules\Cases\Models\CaseStatusModel;
use Modules\CaseEngine\Support\CaseCodeGenerator;

class CaseEngineService
{
    public function createCaseFromMessage(
        int $citizenId,
        string $categorySlug,
        string $messageText
    ): ?int {
        $category = (new CategoryModel())
            ->where('slug', $categorySlug)
            ->first();

        $status = (new CaseStatusModel())
            ->where('slug', 'nuevo')
            ->first();

        if (!$status) {
            log_message('error', 'No existe estado nuevo en case_statuses.');
            return null;
        }

        if ($category && $this->hasOpenCase($citizenId, (int) $category['id'])) {
            log_message('info', 'Caso duplicado evitado para ciudadano: ' . $citizenId);
            return null;
        }

        $caseId = (new CaseModel())->insert([
            'public_code' => (new CaseCodeGenerator())->generate(),
            'citizen_id'  => $citizenId,
            'category_id' => $category['id'] ?? null,
            'status_id'   => $status['id'],
            'title'       => $this->buildTitle($categorySlug),
            'description' => $messageText,
            'priority'    => $this->detectPriority($categorySlug),
            'sentiment'   => 'pending',
            'assigned_to' => null,
        ]);

        (new \Modules\CaseEngine\Services\CaseLifecycleService())
            ->registerCreated((int) $caseId);

        return $caseId ? (int) $caseId : null;
    }

    public function shouldCreateCase(?string $categorySlug): bool
    {
        return in_array($categorySlug, [
            'necesidad-comunitaria',
            'solicitud-apoyo',
            'propuesta-ciudadana',
        ], true);
    }

    private function hasOpenCase(int $citizenId, int $categoryId): bool
    {
        $openStatusIds = (new CaseStatusModel())
            ->whereIn('slug', ['nuevo', 'en-revision', 'asignado', 'en-proceso'])
            ->findColumn('id');

        if (!$openStatusIds) {
            return false;
        }

        return (new CaseModel())
                ->where('citizen_id', $citizenId)
                ->where('category_id', $categoryId)
                ->whereIn('status_id', $openStatusIds)
                ->countAllResults() > 0;
    }

    private function buildTitle(string $categorySlug): string
    {
        return match ($categorySlug) {
            'necesidad-comunitaria' => 'Nueva necesidad comunitaria',
            'solicitud-apoyo'       => 'Nueva solicitud de apoyo',
            'propuesta-ciudadana'   => 'Nueva propuesta ciudadana',
            default                 => 'Nuevo caso ciudadano',
        };
    }

    private function detectPriority(string $categorySlug): string
    {
        return match ($categorySlug) {
            'necesidad-comunitaria' => 'high',
            'solicitud-apoyo'       => 'high',
            'propuesta-ciudadana'   => 'normal',
            default                 => 'normal',
        };
    }
}