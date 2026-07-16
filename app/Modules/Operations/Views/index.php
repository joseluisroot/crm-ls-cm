<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$queueCounts = [
    'PENDING' => $summary['pending'],
    'ACTIVE' => $summary['active'],
    'WAITING' => $summary['waiting'],
    'COMPLETED' => $summary['completed'],
    'CANCELLED' => $summary['cancelled'],
];

$toneClasses = [
    'red' => 'border-red-200 bg-red-50 text-red-800',
    'blue' => 'border-blue-200 bg-blue-50 text-blue-800',
    'amber' => 'border-amber-200 bg-amber-50 text-amber-800',
    'green' => 'border-green-200 bg-green-50 text-green-800',
    'slate' => 'border-slate-200 bg-slate-50 text-slate-700',
];

$total = (int) ($pagination['total'] ?? count($items));
$currentPage = (int) ($pagination['page'] ?? 1);
$currentPerPage = (int) ($pagination['perPage'] ?? $perPage ?? 25);
$from = $total === 0 ? 0 : (($currentPage - 1) * $currentPerPage) + 1;
$to = min($currentPage * $currentPerPage, $total);
?>

<div class="ciac-page-header xl:flex-row xl:items-center xl:justify-between">
    <div>
        <p class="ciac-page-eyebrow">Operational Work Queues</p>
        <h1 class="ciac-page-title mt-2"><?= esc($title) ?></h1>
        <p class="ciac-page-description">Lo pendiente permanece visible; lo completado sale de la bandeja activa sin perder trazabilidad.</p>
    </div>

    <?php if (can('operations.view')): ?>
        <form method="post" action="<?= site_url('admin/operations/import-facebook-comments') ?>"
              data-confirm="¿Sincronizar comentarios de Facebook?"
              data-confirm-text="Se importarán los comentarios pendientes a Citizen Operations."
              data-loading="Sincronizando comentarios...">
            <?= csrf_field() ?>
            <button
                type="submit"
                formaction="<?= site_url('admin/operations/import-facebook-comments') ?>"
                formmethod="post"
                class="ciac-btn ciac-btn--secondary">
                Sincronizar comentarios
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-2 xl:grid-cols-5">
    <?php foreach ($queues as $code => $definition): ?>
        <?php $active = $queue === $code; ?>
        <a href="<?= site_url('admin/operations?' . http_build_query(['queue' => $code])) ?>"
           class="ciac-card ciac-card--interactive p-5 <?= $active ? 'ring-2 ring-pink-500 shadow-md ' : '' ?><?= $toneClasses[$definition['tone']] ?? $toneClasses['slate'] ?>"
           aria-current="<?= $active ? 'page' : 'false' ?>">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-black"><?= esc($definition['label']) ?></p>
                    <p class="mt-1 text-xs opacity-75"><?= esc($definition['description']) ?></p>
                </div>
                <span class="text-3xl font-black"><?= esc($queueCounts[$code] ?? 0) ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<section class="ciac-card overflow-hidden">
    <header class="ciac-card__header">
        <div class="flex flex-col gap-5 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <p class="ciac-page-eyebrow">Bandeja operativa</p>
                <h2 class="ciac-card__title mt-2"><?= esc($queues[$queue]['label'] ?? 'Cola operacional') ?></h2>
                <p class="ciac-card__subtitle"><?= esc($queues[$queue]['description'] ?? '') ?></p>
                <p class="mt-2 text-xs font-semibold text-slate-400">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> atenciones.</p>
            </div>

            <form method="get" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5" data-loading="Aplicando filtros...">
                <input type="hidden" name="queue" value="<?= esc($queue) ?>">
                <div class="sm:col-span-2">
                    <label for="operations-search" class="ciac-label">Buscar</label>
                    <input id="operations-search" type="search" name="q" value="<?= esc($search) ?>" placeholder="Ciudadano, mensaje o responsable" class="ciac-field">
                </div>
                <div>
                    <label for="operations-status" class="ciac-label">Estado</label>
                    <select id="operations-status" name="status" class="ciac-select">
                        <option value="">Todos</option>
                        <?php foreach ($statuses as $option): ?>
                            <option value="<?= esc($option['code']) ?>" <?= $status === $option['code'] ? 'selected' : '' ?>><?= esc($option['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="operations-priority" class="ciac-label">Prioridad</label>
                    <select id="operations-priority" name="priority" class="ciac-select">
                        <option value="">Todas</option>
                        <?php foreach ($priorities as $option): ?>
                            <option value="<?= esc($option['code']) ?>" <?= $priority === $option['code'] ? 'selected' : '' ?>><?= esc($option['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="operations-per-page" class="ciac-label">Registros</label>
                    <div class="flex gap-2">
                        <select id="operations-per-page" name="per_page" class="ciac-select">
                            <?php foreach ([10, 25, 50, 100] as $size): ?>
                                <option value="<?= $size ?>" <?= $perPage === $size ? 'selected' : '' ?>><?= $size ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="ciac-btn ciac-btn--primary">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </header>

    <?php if (empty($items)): ?>
        <div class="ciac-empty-state">
            <div class="ciac-empty-state__icon">📭</div>
            <h3 class="ciac-empty-state__title">No hay atenciones en esta bandeja</h3>
            <p class="ciac-empty-state__description">Prueba modificando la búsqueda o los filtros. Los nuevos registros aparecerán automáticamente cuando entren en esta etapa.</p>
            <a href="<?= site_url('admin/operations?' . http_build_query(['queue' => $queue])) ?>" class="ciac-btn ciac-btn--outline mt-5">Limpiar filtros</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="ciac-table min-w-[980px]">
                <thead>
                <tr>
                    <th>Atención</th>
                    <th>Estado</th>
                    <th>Responsable</th>
                    <th>Fecha</th>
                    <th class="text-right">Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <?php
                    $priorityClass = match ($item['priority']) {
                        'CRITICAL' => 'ciac-badge--danger',
                        'HIGH' => 'ciac-badge--warning',
                        'LOW' => 'ciac-badge--neutral',
                        default => 'ciac-badge--info',
                    };
                    ?>
                    <tr>
                        <td>
                            <div class="mb-2 flex flex-wrap gap-2">
                                <span class="ciac-badge ciac-badge--primary"><?= esc($item['channel']) ?></span>
                                <span class="ciac-badge <?= $priorityClass ?>"><?= esc($item['priority_name']) ?></span>
                            </div>
                            <p class="font-black text-slate-900"><?= esc($item['author_name'] ?: $item['title']) ?></p>
                            <p class="mt-1 max-w-2xl text-sm text-slate-500 line-clamp-2"><?= esc($item['comment_message'] ?: $item['summary']) ?></p>
                            <p class="mt-2 text-xs text-slate-400">Work Item #<?= esc($item['id']) ?></p>
                        </td>
                        <td><span class="ciac-badge ciac-badge--neutral"><?= esc($item['status_name']) ?></span></td>
                        <td class="font-semibold text-slate-700"><?= esc($item['assigned_user_name'] ?: 'Sin asignar') ?></td>
                        <td class="whitespace-nowrap text-slate-500"><?= esc($item['commented_at'] ?: $item['opened_at'] ?: $item['created_at']) ?></td>
                        <td class="text-right"><a href="<?= site_url('admin/operations/' . $item['id']) ?>" class="ciac-btn ciac-btn--primary ciac-btn--sm">Abrir atención →</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= view('Modules\Shared\Views\components\pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</section>

<script>
document.getElementById('operations-per-page')?.addEventListener('change', (event) => {
    event.target.form?.requestSubmit();
});
</script>

<?= $this->endSection() ?>