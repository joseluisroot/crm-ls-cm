<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$buildPageUrl = static function (int $targetPage) use ($filters): string {
    return site_url('admin/notifications') . '?' . http_build_query([
        'q' => $filters['q'],
        'status' => $filters['status'],
        'channel' => $filters['channel'],
        'per_page' => $filters['per_page'],
        'page' => $targetPage,
    ]);
};
$firstVisiblePage = max(1, $page - 2);
$lastVisiblePage = min($pageCount, $page + 2);
?>

<section class="ciac-card mb-6">
    <div class="ciac-card__body">
        <form method="get" action="<?= site_url('admin/notifications') ?>" class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_180px_180px_150px_auto] xl:items-end">
            <div>
                <label for="notification-search" class="ciac-label">Buscar notificación</label>
                <input id="notification-search" name="q" value="<?= esc($filters['q']) ?>" class="ciac-field" placeholder="Asunto, contenido o destinatario">
            </div>
            <div>
                <label for="notification-status" class="ciac-label">Estado</label>
                <select id="notification-status" name="status" class="ciac-select">
                    <option value="">Todos</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>No leídas</option>
                    <option value="read" <?= $filters['status'] === 'read' ? 'selected' : '' ?>>Leídas</option>
                </select>
            </div>
            <div>
                <label for="notification-channel" class="ciac-label">Canal</label>
                <input id="notification-channel" name="channel" value="<?= esc($filters['channel']) ?>" class="ciac-field" placeholder="Todos">
            </div>
            <div>
                <label for="notification-per-page" class="ciac-label">Registros</label>
                <select id="notification-per-page" name="per_page" class="ciac-select">
                    <?php foreach ([10, 20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= (int) $filters['per_page'] === $size ? 'selected' : '' ?>><?= $size ?> por página</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ciac-actions xl:justify-end">
                <button class="ciac-btn ciac-btn--primary" data-loading="Buscando notificaciones...">Buscar</button>
                <a href="<?= site_url('admin/notifications') ?>" class="ciac-btn ciac-btn--outline">Limpiar</a>
            </div>
        </form>
    </div>
</section>

<section class="ciac-card overflow-hidden">
    <header class="ciac-card__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="ciac-page-eyebrow">Centro de notificaciones</p>
            <h2 class="ciac-card__title mt-2">Alertas internas</h2>
            <p class="ciac-card__subtitle">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> notificaciones.</p>
        </div>
        <span class="ciac-badge ciac-badge--primary"><?= $total ?> notificaciones</span>
    </header>

    <?php if (empty($notifications)): ?>
        <div class="ciac-empty-state">
            <div class="ciac-empty-state__icon">🔔</div>
            <h3 class="ciac-empty-state__title">No encontramos notificaciones</h3>
            <p class="ciac-empty-state__description">Ajusta los filtros o espera nuevas alertas generadas por la actividad ciudadana y los casos.</p>
            <a href="<?= site_url('admin/notifications') ?>" class="ciac-btn ciac-btn--outline mt-5">Limpiar filtros</a>
        </div>
    <?php else: ?>
        <div class="divide-y divide-slate-100">
            <?php foreach ($notifications as $notification): ?>
                <?php
                $isUnread = ($notification['status'] ?? 'pending') !== 'read';
                $payload = json_decode($notification['payload'] ?? '{}', true) ?: [];
                $caseId = $payload['case_id'] ?? null;
                ?>
                <article class="p-6 <?= $isUnread ? 'bg-pink-50/40' : 'bg-white' ?>">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex min-w-0 gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl <?= $isUnread ? 'bg-pink-600 text-white' : 'bg-slate-100 text-slate-500' ?>">🔔</div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="font-extrabold text-slate-900"><?= esc($notification['subject'] ?? 'Notificación') ?></h3>
                                    <span class="ciac-badge <?= $isUnread ? 'ciac-badge--primary' : 'ciac-badge--neutral' ?>">
                                        <?= $isUnread ? 'No leída' : 'Leída' ?>
                                    </span>
                                    <?php if (! empty($notification['channel'])): ?>
                                        <span class="ciac-badge ciac-badge--info"><?= esc($notification['channel']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600"><?= esc($notification['body'] ?? '') ?></p>
                                <p class="mt-3 text-xs text-slate-400"><?= esc($notification['created_at'] ?? '-') ?></p>
                            </div>
                        </div>

                        <div class="ciac-actions lg:justify-end">
                            <?php if ($caseId): ?>
                                <a href="<?= site_url('admin/cases/' . $caseId) ?>" class="ciac-btn ciac-btn--secondary ciac-btn--sm">Ver caso</a>
                            <?php endif; ?>

                            <?php if ($isUnread): ?>
                                <form method="post" action="<?= site_url('admin/notifications/' . $notification['id'] . '/read') ?>">
                                    <?= csrf_field() ?>
                                    <button class="ciac-btn ciac-btn--outline ciac-btn--sm"
                                            data-confirm="¿Marcar esta notificación como leída?"
                                            data-loading="Actualizando notificación...">
                                        Marcar leída
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($pageCount > 1): ?>
            <footer class="ciac-card__footer flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-500">Página <?= $page ?> de <?= $pageCount ?></p>
                <nav class="flex flex-wrap items-center gap-2" aria-label="Paginación de notificaciones">
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
document.getElementById('notification-per-page')?.addEventListener('change', (event) => {
    event.target.form?.requestSubmit();
});
</script>

<?= $this->endSection() ?>
