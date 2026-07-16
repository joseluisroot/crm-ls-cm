<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$buildPageUrl = static function (int $targetPage) use ($filters): string {
    return site_url('admin/citizens') . '?' . http_build_query([
        'q' => $filters['q'],
        'status' => $filters['status'],
        'per_page' => $filters['per_page'],
        'page' => $targetPage,
    ]);
};
$firstVisiblePage = max(1, $page - 2);
$lastVisiblePage = min($pageCount, $page + 2);
?>

<section class="ciac-card mb-6">
    <div class="ciac-card__body">
        <form method="get" action="<?= site_url('admin/citizens') ?>" class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_220px_160px_auto] lg:items-end">
            <div>
                <label for="citizen-search" class="ciac-label">Buscar ciudadano</label>
                <input id="citizen-search" name="q" value="<?= esc($filters['q']) ?>" class="ciac-field" placeholder="Nombre, municipio, comunidad, teléfono o correo">
            </div>
            <div>
                <label for="citizen-status" class="ciac-label">Estado</label>
                <input id="citizen-status" name="status" value="<?= esc($filters['status']) ?>" class="ciac-field" placeholder="Todos">
            </div>
            <div>
                <label for="citizen-per-page" class="ciac-label">Registros</label>
                <select id="citizen-per-page" name="per_page" class="ciac-select">
                    <?php foreach ([10, 20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= (int) $filters['per_page'] === $size ? 'selected' : '' ?>><?= $size ?> por página</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ciac-actions lg:justify-end">
                <button class="ciac-btn ciac-btn--primary" data-loading="Buscando ciudadanos...">Buscar</button>
                <a href="<?= site_url('admin/citizens') ?>" class="ciac-btn ciac-btn--outline">Limpiar</a>
            </div>
        </form>
    </div>
</section>

<section class="ciac-card overflow-hidden">
    <header class="ciac-card__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="ciac-page-eyebrow">Directorio ciudadano</p>
            <h2 class="ciac-card__title mt-2">Ciudadanos registrados</h2>
            <p class="ciac-card__subtitle">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> registros.</p>
        </div>
        <span class="ciac-badge ciac-badge--primary"><?= $total ?> ciudadanos</span>
    </header>

    <?php if (empty($citizens)): ?>
        <div class="ciac-empty-state">
            <div class="ciac-empty-state__icon">👥</div>
            <h3 class="ciac-empty-state__title">No encontramos ciudadanos</h3>
            <p class="ciac-empty-state__description">Ajusta los filtros o espera nuevas interacciones desde los canales conectados.</p>
            <?php if ($filters['q'] !== '' || $filters['status'] !== ''): ?>
                <a href="<?= site_url('admin/citizens') ?>" class="ciac-btn ciac-btn--outline mt-5">Limpiar filtros</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="ciac-table min-w-[820px]">
                <thead>
                <tr>
                    <th>Ciudadano</th>
                    <th>Municipio</th>
                    <th>Comunidad</th>
                    <th>Estado</th>
                    <th class="text-right">Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($citizens as $citizen): ?>
                    <tr>
                        <td>
                            <div class="font-extrabold text-slate-900"><?= esc($citizen['name']) ?></div>
                            <div class="mt-1 text-xs text-slate-400"><?= esc($citizen['email'] ?? $citizen['phone'] ?? 'Sin contacto registrado') ?></div>
                        </td>
                        <td><?= esc($citizen['municipality'] ?? '-') ?></td>
                        <td><?= esc($citizen['community'] ?? '-') ?></td>
                        <td><span class="ciac-badge ciac-badge--neutral"><?= esc($citizen['status']) ?></span></td>
                        <td class="text-right">
                            <a href="<?= site_url('admin/citizens/' . $citizen['id']) ?>" class="ciac-btn ciac-btn--ghost ciac-btn--sm">Ver perfil →</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageCount > 1): ?>
            <footer class="ciac-card__footer flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-500">Página <?= $page ?> de <?= $pageCount ?></p>
                <nav class="flex flex-wrap items-center gap-2" aria-label="Paginación de ciudadanos">
                    <?php if ($page > 1): ?>
                        <a href="<?= esc($buildPageUrl($page - 1)) ?>" class="ciac-btn ciac-btn--outline ciac-btn--sm">← Anterior</a>
                    <?php endif; ?>
                    <?php for ($current = $firstVisiblePage; $current <= $lastVisiblePage; $current++): ?>
                        <a href="<?= esc($buildPageUrl($current)) ?>" class="ciac-btn ciac-btn--sm <?= $current === $page ? 'ciac-btn--primary' : 'ciac-btn--outline' ?>" aria-current="<?= $current === $page ? 'page' : 'false' ?>"><?= $current ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $pageCount): ?>
                        <a href="<?= esc($buildPageUrl($page + 1)) ?>" class="ciac-btn ciac-btn--outline ciac-btn--sm">Siguiente →</a>
                    <?php endif; ?>
                </nav>
            </footer>
        <?php endif; ?>
    <?php endif; ?>
</section>

<script>
document.getElementById('citizen-per-page')?.addEventListener('change', (event) => {
    event.target.form?.requestSubmit();
});
</script>

<?= $this->endSection() ?>
