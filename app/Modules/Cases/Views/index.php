<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$actionsHtml = can('cases.create')
    ? '<a href="' . site_url('admin/cases/create') . '" class="ciac-btn ciac-btn--primary">Crear caso</a>'
    : null;

$priorityTone = static function (?string $priority): string {
    return match (strtoupper((string) $priority)) {
        'CRITICAL' => 'ciac-badge--danger',
        'HIGH' => 'bg-orange-100 text-orange-800',
        'LOW' => 'ciac-badge--neutral',
        default => 'ciac-badge--info',
    };
};
?>

<?= view('Modules\Shared\Views\components\page_header', [
    'eyebrow' => 'Case Management Center',
    'title' => $title,
    'description' => 'Supervisa, prioriza y da seguimiento a los casos ciudadanos desde una sola bandeja.',
    'actionsHtml' => $actionsHtml,
]) ?>

<div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 xl:grid-cols-5">
    <?= view('Modules\Shared\Views\components\kpi_card', ['label' => 'Casos visibles', 'value' => $summary['total'], 'tone' => 'blue']) ?>
    <?= view('Modules\Shared\Views\components\kpi_card', ['label' => 'Abiertos', 'value' => $summary['open'], 'tone' => 'amber']) ?>
    <?= view('Modules\Shared\Views\components\kpi_card', ['label' => 'Alta prioridad', 'value' => $summary['high'], 'tone' => 'red']) ?>
    <?= view('Modules\Shared\Views\components\kpi_card', ['label' => 'Asignados a mí', 'value' => $summary['mine'], 'tone' => 'violet']) ?>
    <?= view('Modules\Shared\Views\components\kpi_card', ['label' => 'Sin responsable', 'value' => $summary['unassigned'], 'tone' => 'slate']) ?>
</div>

<section class="ciac-card overflow-hidden">
    <header class="ciac-card__header">
        <?= view('Modules\Shared\Views\components\section_header', [
            'eyebrow' => 'Bandeja de casos',
            'title' => 'Seguimiento operativo',
            'description' => 'Mostrando ' . $from . '–' . $to . ' de ' . $total . ' casos según los filtros aplicados.',
        ]) ?>

        <form method="get" class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6" data-loading="Aplicando filtros...">
            <div class="md:col-span-2 xl:col-span-2">
                <label for="cases-search" class="ciac-label">Buscar</label>
                <input id="cases-search" type="search" name="q" value="<?= esc($filters['q']) ?>" class="ciac-field" placeholder="Código, título, ciudadano, categoría o responsable">
            </div>

            <div>
                <label for="cases-status" class="ciac-label">Estado</label>
                <select id="cases-status" name="status" class="ciac-select">
                    <option value="">Todos</option>
                    <?php foreach ($statuses as $option): ?>
                        <option value="<?= esc($option['slug']) ?>" <?= $filters['status'] === $option['slug'] ? 'selected' : '' ?>><?= esc($option['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="cases-priority" class="ciac-label">Prioridad</label>
                <select id="cases-priority" name="priority" class="ciac-select">
                    <option value="">Todas</option>
                    <?php foreach (['LOW' => 'Baja', 'MEDIUM' => 'Media', 'HIGH' => 'Alta', 'CRITICAL' => 'Crítica'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= strtoupper($filters['priority']) === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="cases-assigned" class="ciac-label">Asignación</label>
                <select id="cases-assigned" name="assigned" class="ciac-select">
                    <option value="">Todos</option>
                    <option value="me" <?= $filters['assigned'] === 'me' ? 'selected' : '' ?>>Asignados a mí</option>
                    <option value="unassigned" <?= $filters['assigned'] === 'unassigned' ? 'selected' : '' ?>>Sin responsable</option>
                </select>
            </div>

            <div>
                <label for="cases-per-page" class="ciac-label">Registros</label>
                <select id="cases-per-page" name="per_page" class="ciac-select" onchange="this.form.submit()">
                    <?php foreach ([10, 20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= (int) $filters['per_page'] === $size ? 'selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex flex-wrap gap-2 md:col-span-2 xl:col-span-6 xl:justify-end">
                <?php if ($filters['q'] !== '' || $filters['status'] !== '' || $filters['priority'] !== '' || $filters['assigned'] !== ''): ?>
                    <a href="<?= site_url('admin/cases') ?>" class="ciac-btn ciac-btn--outline">Limpiar</a>
                <?php endif; ?>
                <button class="ciac-btn ciac-btn--primary">Aplicar filtros</button>
            </div>
        </form>
    </header>

    <?php if (empty($cases)): ?>
        <?= view('Modules\Shared\Views\components\empty_state', [
            'icon' => '📂',
            'title' => 'No se encontraron casos',
            'description' => 'Prueba modificando la búsqueda o los filtros seleccionados.',
            'actionHtml' => '<a href="' . site_url('admin/cases') . '" class="ciac-btn ciac-btn--outline">Limpiar filtros</a>',
        ]) ?>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="ciac-table min-w-[1100px]">
                <thead>
                <tr>
                    <th>Caso</th>
                    <th>Ciudadano</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Responsable</th>
                    <th>Actualizado</th>
                    <th class="text-right">Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($cases as $case): ?>
                    <tr>
                        <td>
                            <p class="font-black text-slate-900"><?= esc($case['title']) ?></p>
                            <p class="mt-1 text-xs font-semibold text-slate-400"><?= esc($case['public_code'] ?: 'Caso #' . $case['id']) ?></p>
                            <p class="mt-2 text-sm text-slate-500"><?= esc($case['category_name'] ?? 'Sin categoría') ?></p>
                        </td>
                        <td class="font-semibold text-slate-700"><?= esc($case['citizen_name']) ?></td>
                        <td><span class="ciac-badge ciac-badge--neutral"><?= esc($case['status_name']) ?></span></td>
                        <td><span class="ciac-badge <?= esc($priorityTone($case['priority']), 'attr') ?>"><?= esc(ucfirst(strtolower((string) $case['priority']))) ?></span></td>
                        <td class="text-sm font-semibold text-slate-700"><?= esc($case['assigned_user_name'] ?: 'Sin asignar') ?></td>
                        <td class="whitespace-nowrap text-sm text-slate-500"><?= esc($case['updated_at'] ?: $case['created_at']) ?></td>
                        <td class="text-right"><a href="<?= site_url('admin/cases/' . $case['id']) ?>" class="ciac-btn ciac-btn--secondary ciac-btn--sm">Abrir</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= view('Modules\Shared\Views\components\pagination', [
            'pagination' => ['total' => $total, 'page' => $page, 'perPage' => $filters['per_page'], 'pages' => $pageCount],
        ]) ?>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>
