<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$buildPageUrl = static function (int $targetPage) use ($filters): string {
    return site_url('admin/my-cases') . '?' . http_build_query([
        'q' => $filters['q'],
        'status' => $filters['status'],
        'priority' => $filters['priority'],
        'per_page' => $filters['per_page'],
        'page' => $targetPage,
    ]);
};
$firstVisiblePage = max(1, $page - 2);
$lastVisiblePage = min($pageCount, $page + 2);
$priorityClasses = [
    'high' => 'ciac-badge--danger',
    'urgent' => 'ciac-badge--danger',
    'medium' => 'ciac-badge--warning',
    'normal' => 'ciac-badge--info',
    'low' => 'ciac-badge--neutral',
];
?>

<section class="ciac-card mb-6">
    <div class="ciac-card__body">
        <form method="get" action="<?= site_url('admin/my-cases') ?>" class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_200px_180px_150px_auto] xl:items-end">
            <div>
                <label for="case-search" class="ciac-label">Buscar caso</label>
                <input id="case-search" name="q" value="<?= esc($filters['q']) ?>" class="ciac-field" placeholder="Código, título, ciudadano o categoría">
            </div>
            <div>
                <label for="case-status" class="ciac-label">Estado</label>
                <select id="case-status" name="status" class="ciac-select">
                    <option value="">Todos</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= esc($status['slug']) ?>" <?= $filters['status'] === $status['slug'] ? 'selected' : '' ?>><?= esc($status['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="case-priority" class="ciac-label">Prioridad</label>
                <select id="case-priority" name="priority" class="ciac-select">
                    <option value="">Todas</option>
                    <?php foreach (['urgent' => 'Urgente', 'high' => 'Alta', 'medium' => 'Media', 'normal' => 'Normal', 'low' => 'Baja'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $filters['priority'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="case-per-page" class="ciac-label">Registros</label>
                <select id="case-per-page" name="per_page" class="ciac-select">
                    <?php foreach ([10, 20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= (int) $filters['per_page'] === $size ? 'selected' : '' ?>><?= $size ?> por página</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ciac-actions xl:justify-end">
                <button class="ciac-btn ciac-btn--primary" data-loading="Buscando casos...">Buscar</button>
                <a href="<?= site_url('admin/my-cases') ?>" class="ciac-btn ciac-btn--outline">Limpiar</a>
            </div>
        </form>
    </div>
</section>

<section class="ciac-card overflow-hidden">
    <header class="ciac-card__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="ciac-page-eyebrow">Bandeja personal</p>
            <h2 class="ciac-card__title mt-2">Mis casos asignados</h2>
            <p class="ciac-card__subtitle">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> casos.</p>
        </div>
        <span class="ciac-badge ciac-badge--primary"><?= $total ?> casos</span>
    </header>

    <?php if (empty($cases)): ?>
        <div class="ciac-empty-state">
            <div class="ciac-empty-state__icon">✅</div>
            <h3 class="ciac-empty-state__title">No tienes casos pendientes con estos filtros</h3>
            <p class="ciac-empty-state__description">Los nuevos casos asignados aparecerán aquí. También puedes limpiar los filtros para revisar toda tu bandeja.</p>
            <a href="<?= site_url('admin/my-cases') ?>" class="ciac-btn ciac-btn--outline mt-5">Limpiar filtros</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="ciac-table min-w-[980px]">
                <thead><tr><th>Caso</th><th>Ciudadano</th><th>Categoría</th><th>Prioridad</th><th>Estado</th><th>Actualizado</th><th class="text-right">Acción</th></tr></thead>
                <tbody>
                <?php foreach ($cases as $case): ?>
                    <?php $priority = strtolower((string) ($case['priority'] ?? 'normal')); ?>
                    <tr>
                        <td>
                            <div class="font-extrabold text-slate-900"><?= esc($case['title']) ?></div>
                            <div class="mt-1 text-xs text-slate-400"><?= esc($case['public_code'] ?? '#' . $case['id']) ?></div>
                        </td>
                        <td><?= esc($case['citizen_name']) ?></td>
                        <td><?= esc($case['category_name'] ?? 'Sin clasificar') ?></td>
                        <td><span class="ciac-badge <?= $priorityClasses[$priority] ?? 'ciac-badge--neutral' ?>"><?= esc(ucfirst($priority)) ?></span></td>
                        <td><span class="ciac-badge ciac-badge--neutral"><?= esc($case['status_name']) ?></span></td>
                        <td><?= esc($case['updated_at'] ?? $case['created_at'] ?? '-') ?></td>
                        <td class="text-right"><a href="<?= site_url('admin/cases/' . $case['id']) ?>" class="ciac-btn ciac-btn--primary ciac-btn--sm">Gestionar →</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageCount > 1): ?>
            <footer class="ciac-card__footer flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-500">Página <?= $page ?> de <?= $pageCount ?></p>
                <nav class="flex flex-wrap items-center gap-2" aria-label="Paginación de mis casos">
                    <?php if ($page > 1): ?><a href="<?= esc($buildPageUrl($page - 1)) ?>" class="ciac-btn ciac-btn--outline ciac-btn--sm">← Anterior</a><?php endif; ?>
                    <?php for ($current = $firstVisiblePage; $current <= $lastVisiblePage; $current++): ?>
                        <a href="<?= esc($buildPageUrl($current)) ?>" class="ciac-btn ciac-btn--sm <?= $current === $page ? 'ciac-btn--primary' : 'ciac-btn--outline' ?>" aria-current="<?= $current === $page ? 'page' : 'false' ?>"><?= $current ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $pageCount): ?><a href="<?= esc($buildPageUrl($page + 1)) ?>" class="ciac-btn ciac-btn--outline ciac-btn--sm">Siguiente →</a><?php endif; ?>
                </nav>
            </footer>
        <?php endif; ?>
    <?php endif; ?>
</section>

<script>
document.getElementById('case-per-page')?.addEventListener('change', (event) => event.target.form?.requestSubmit());
</script>

<?= $this->endSection() ?>
