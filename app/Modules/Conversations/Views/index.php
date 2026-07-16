<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$buildPageUrl = static function (int $targetPage) use ($filters): string {
    return site_url('admin/conversations') . '?' . http_build_query([
        'q' => $filters['q'],
        'channel' => $filters['channel'],
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
        <form method="get" action="<?= site_url('admin/conversations') ?>" class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_180px_180px_150px_auto] xl:items-end">
            <div>
                <label for="conversation-search" class="ciac-label">Buscar conversación</label>
                <input id="conversation-search" name="q" value="<?= esc($filters['q']) ?>" class="ciac-field" placeholder="Ciudadano, municipio, comunidad, canal o estado">
            </div>
            <div>
                <label for="conversation-channel" class="ciac-label">Canal</label>
                <input id="conversation-channel" name="channel" value="<?= esc($filters['channel']) ?>" class="ciac-field" placeholder="Todos">
            </div>
            <div>
                <label for="conversation-status" class="ciac-label">Estado</label>
                <input id="conversation-status" name="status" value="<?= esc($filters['status']) ?>" class="ciac-field" placeholder="Todos">
            </div>
            <div>
                <label for="conversation-per-page" class="ciac-label">Registros</label>
                <select id="conversation-per-page" name="per_page" class="ciac-select">
                    <?php foreach ([10, 20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= (int) $filters['per_page'] === $size ? 'selected' : '' ?>><?= $size ?> por página</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ciac-actions xl:justify-end">
                <button class="ciac-btn ciac-btn--primary" data-loading="Buscando conversaciones...">Buscar</button>
                <a href="<?= site_url('admin/conversations') ?>" class="ciac-btn ciac-btn--outline">Limpiar</a>
            </div>
        </form>
    </div>
</section>

<section class="ciac-card overflow-hidden">
    <header class="ciac-card__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="ciac-page-eyebrow">Bandeja de conversaciones</p>
            <h2 class="ciac-card__title mt-2">Conversaciones ciudadanas</h2>
            <p class="ciac-card__subtitle">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> registros.</p>
        </div>
        <span class="ciac-badge ciac-badge--info"><?= $total ?> conversaciones</span>
    </header>

    <?php if (empty($conversations)): ?>
        <div class="ciac-empty-state">
            <div class="ciac-empty-state__icon">💬</div>
            <h3 class="ciac-empty-state__title">No encontramos conversaciones</h3>
            <p class="ciac-empty-state__description">Ajusta los filtros o espera nuevas interacciones desde Messenger y Facebook.</p>
            <?php if ($filters['q'] !== '' || $filters['channel'] !== '' || $filters['status'] !== ''): ?>
                <a href="<?= site_url('admin/conversations') ?>" class="ciac-btn ciac-btn--outline mt-5">Limpiar filtros</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="ciac-table min-w-[920px]">
                <thead>
                <tr>
                    <th>Ciudadano</th>
                    <th>Ubicación</th>
                    <th>Canal</th>
                    <th>Estado</th>
                    <th>Última actividad</th>
                    <th class="text-right">Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($conversations as $conversation): ?>
                    <tr>
                        <td>
                            <div class="font-extrabold text-slate-900"><?= esc($conversation['citizen_name']) ?></div>
                            <div class="mt-1 text-xs text-slate-400">Conversación #<?= esc($conversation['id']) ?></div>
                        </td>
                        <td>
                            <div class="text-sm text-slate-700"><?= esc($conversation['municipality'] ?? '-') ?></div>
                            <div class="mt-1 text-xs text-slate-400"><?= esc($conversation['community'] ?? 'Sin comunidad') ?></div>
                        </td>
                        <td><span class="ciac-badge ciac-badge--primary"><?= esc($conversation['channel']) ?></span></td>
                        <td><span class="ciac-badge ciac-badge--neutral"><?= esc($conversation['status']) ?></span></td>
                        <td>
                            <div class="text-sm font-semibold text-slate-700"><?= esc($conversation['last_message_at'] ?? 'Sin mensajes') ?></div>
                            <div class="mt-1 text-xs text-slate-400">Actualizada <?= esc($conversation['updated_at'] ?? '-') ?></div>
                        </td>
                        <td class="text-right">
                            <a href="<?= site_url('admin/conversations/' . $conversation['id']) ?>" class="ciac-btn ciac-btn--ghost ciac-btn--sm">Abrir conversación →</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageCount > 1): ?>
            <footer class="ciac-card__footer flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-500">Página <?= $page ?> de <?= $pageCount ?></p>
                <nav class="flex flex-wrap items-center gap-2" aria-label="Paginación de conversaciones">
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
document.getElementById('conversation-per-page')?.addEventListener('change', (event) => {
    event.target.form?.requestSubmit();
});
</script>

<?= $this->endSection() ?>
