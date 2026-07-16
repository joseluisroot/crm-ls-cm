<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$total = (int) ($pagination['total'] ?? count($publications));
$currentPage = (int) ($pagination['page'] ?? 1);
$currentPerPage = (int) ($pagination['perPage'] ?? $perPage ?? 25);
$from = $total === 0 ? 0 : (($currentPage - 1) * $currentPerPage) + 1;
$to = min($currentPage * $currentPerPage, $total);
?>

<div class="ciac-page-header xl:flex-row xl:items-center xl:justify-between">
    <div>
        <p class="ciac-page-eyebrow">Publication Intelligence</p>
        <h1 class="ciac-page-title mt-2">Publication Center</h1>
        <p class="ciac-page-description">Publicaciones, comentarios, reacciones y movimiento operativo.</p>
    </div>
</div>

<section class="ciac-card mb-6">
    <div class="ciac-card__body">
        <form method="get" action="<?= site_url('admin/publications') ?>" class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_170px_auto] lg:items-end" data-loading="Buscando publicaciones...">
            <div>
                <label for="publication-search" class="ciac-label">Buscar publicación</label>
                <input id="publication-search" type="search" name="q" value="<?= esc($search) ?>" placeholder="Texto o identificador externo" class="ciac-field">
            </div>
            <div>
                <label for="publication-per-page" class="ciac-label">Registros</label>
                <select id="publication-per-page" name="per_page" class="ciac-select">
                    <?php foreach ([10, 25, 50, 100] as $option): ?>
                        <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?> por página</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ciac-actions lg:justify-end">
                <button class="ciac-btn ciac-btn--primary">Buscar</button>
                <a href="<?= site_url('admin/publications') ?>" class="ciac-btn ciac-btn--outline">Limpiar</a>
            </div>
        </form>
    </div>
</section>

<section class="ciac-card overflow-hidden">
    <header class="ciac-card__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="ciac-page-eyebrow">Publicaciones registradas</p>
            <h2 class="ciac-card__title mt-2">Contenido social</h2>
            <p class="ciac-card__subtitle">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> publicaciones.</p>
        </div>
        <span class="ciac-badge ciac-badge--primary"><?= $total ?> publicaciones</span>
    </header>

    <?php if (empty($publications)): ?>
        <div class="ciac-empty-state">
            <div class="ciac-empty-state__icon">📣</div>
            <h3 class="ciac-empty-state__title">No encontramos publicaciones</h3>
            <p class="ciac-empty-state__description">Ajusta la búsqueda o espera nuevas publicaciones sincronizadas desde Facebook.</p>
            <a href="<?= site_url('admin/publications') ?>" class="ciac-btn ciac-btn--outline mt-5">Limpiar filtros</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="ciac-table min-w-[980px]">
                <thead>
                    <tr>
                        <th>Publicación</th>
                        <th>Comentarios</th>
                        <th>Reacciones</th>
                        <th>Fecha</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($publications as $publication): ?>
                        <tr>
                            <td class="min-w-[24rem]">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="ciac-badge ciac-badge--neutral">#<?= esc($publication['id']) ?></span>
                                    <span class="text-xs font-semibold text-slate-400"><?= esc($publication['external_post_id'] ?? '-') ?></span>
                                </div>
                                <p class="mt-3 max-w-2xl line-clamp-2 whitespace-pre-line text-sm text-slate-600"><?= esc($publication['message'] ?: 'Publicación sin texto disponible.') ?></p>
                            </td>
                            <td><span class="ciac-badge ciac-badge--info"><?= esc($publication['comments_count'] ?? 0) ?></span></td>
                            <td><span class="ciac-badge ciac-badge--primary"><?= esc($publication['reactions_count'] ?? 0) ?></span></td>
                            <td class="whitespace-nowrap text-sm text-slate-500"><?= esc($publication['published_at'] ?? $publication['created_at'] ?? '-') ?></td>
                            <td class="text-right">
                                <div class="ciac-actions justify-end">
                                    <?php if (! empty($publication['permalink_url'])): ?>
                                        <a href="<?= esc($publication['permalink_url']) ?>" target="_blank" rel="noopener" class="ciac-btn ciac-btn--outline ciac-btn--sm">Facebook ↗</a>
                                    <?php endif; ?>
                                    <a href="<?= site_url('admin/publications/' . $publication['id']) ?>" class="ciac-btn ciac-btn--primary ciac-btn--sm">Abrir perfil</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= view('Modules\Shared\Views\components\pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</section>

<script>
document.getElementById('publication-per-page')?.addEventListener('change', (event) => {
    event.target.form?.requestSubmit();
});
</script>

<?= $this->endSection() ?>
