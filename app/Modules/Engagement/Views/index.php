<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$total = (int) ($pagination['total'] ?? count($comments));
$currentPage = (int) ($pagination['page'] ?? 1);
$currentPerPage = (int) ($pagination['perPage'] ?? $perPage ?? 25);
$from = $total === 0 ? 0 : (($currentPage - 1) * $currentPerPage) + 1;
$to = min($currentPage * $currentPerPage, $total);
$statusLabels = [
    'new' => 'Nuevo',
    'responded' => 'Respondido',
    'closed' => 'Cerrado',
    'removed' => 'Eliminado',
];
$statusTones = [
    'new' => 'ciac-badge--warning',
    'responded' => 'ciac-badge--success',
    'closed' => 'ciac-badge--neutral',
    'removed' => 'ciac-badge--danger',
];
?>

<div class="ciac-page-header xl:flex-row xl:items-center xl:justify-between">
    <div>
        <p class="ciac-page-eyebrow">Public Engagement Engine</p>
        <h1 class="ciac-page-title mt-2">Centro de interacciones públicas</h1>
        <p class="ciac-page-description">Comentarios, reacciones y participación ciudadana en publicaciones.</p>
    </div>
    <a href="<?= site_url('admin/engagement/participants') ?>" class="ciac-btn ciac-btn--secondary">Ver participación ciudadana</a>
</div>

<div class="grid grid-cols-1 gap-5 mb-8 md:grid-cols-2 xl:grid-cols-4">
    <?php foreach ([
        ['label' => 'Publicaciones', 'value' => $summary['posts'], 'badge' => 'ciac-badge--info'],
        ['label' => 'Comentarios', 'value' => $summary['comments'], 'badge' => 'ciac-badge--neutral'],
        ['label' => 'Pendientes', 'value' => $summary['pending_comments'], 'badge' => 'ciac-badge--warning'],
        ['label' => 'Reacciones activas', 'value' => $summary['active_reactions'], 'badge' => 'ciac-badge--primary'],
    ] as $card): ?>
        <article class="ciac-card p-6">
            <span class="ciac-badge <?= $card['badge'] ?>"><?= esc($card['label']) ?></span>
            <p class="mt-4 text-4xl font-black text-slate-900"><?= esc($card['value']) ?></p>
        </article>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
    <section class="ciac-card overflow-hidden 2xl:col-span-2">
        <header class="ciac-card__header">
            <div class="flex flex-col gap-5">
                <div>
                    <p class="ciac-page-eyebrow">Bandeja pública</p>
                    <h2 class="ciac-card__title mt-2">Bandeja de comentarios</h2>
                    <p class="ciac-card__subtitle">Interacciones públicas que pueden requerir atención.</p>
                    <p class="mt-2 text-xs font-semibold text-slate-400">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> comentarios.</p>
                </div>

                <form method="get" class="grid grid-cols-1 gap-3 md:grid-cols-4" data-loading="Aplicando filtros...">
                    <div class="md:col-span-2">
                        <label for="engagement-search" class="ciac-label">Buscar</label>
                        <input id="engagement-search" type="search" name="q" value="<?= esc($search) ?>" placeholder="Persona, comentario o publicación" class="ciac-field">
                    </div>
                    <div>
                        <label for="engagement-status" class="ciac-label">Estado</label>
                        <select id="engagement-status" name="status" class="ciac-select">
                            <option value="">Todos los estados</option>
                            <?php foreach ($statusLabels as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="engagement-per-page" class="ciac-label">Registros</label>
                        <select id="engagement-per-page" name="per_page" class="ciac-select" onchange="this.form.submit()">
                            <?php foreach ([10, 25, 50, 100] as $option): ?>
                                <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-wrap justify-end gap-3 md:col-span-4">
                        <?php if ($status !== '' || $search !== ''): ?>
                            <a href="<?= site_url('admin/engagement') ?>" class="ciac-btn ciac-btn--outline">Limpiar</a>
                        <?php endif; ?>
                        <button class="ciac-btn ciac-btn--primary">Aplicar filtros</button>
                    </div>
                </form>
            </div>
        </header>

        <?php if (empty($comments)): ?>
            <div class="ciac-empty-state">
                <div class="ciac-empty-state__icon">💬</div>
                <h3 class="ciac-empty-state__title">No se encontraron comentarios</h3>
                <p class="ciac-empty-state__description">Prueba ajustando la búsqueda o el estado seleccionado.</p>
                <a href="<?= site_url('admin/engagement') ?>" class="ciac-btn ciac-btn--outline mt-5">Limpiar filtros</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="ciac-table min-w-[900px]">
                    <thead>
                    <tr>
                        <th>Persona y comentario</th>
                        <th>Estado</th>
                        <th>Publicación</th>
                        <th>Fecha</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <?php $commentStatus = (string) ($comment['status'] ?? 'new'); ?>
                        <tr>
                            <td class="min-w-[24rem]">
                                <p class="font-black text-slate-900"><?= esc($comment['author_name'] ?: 'Usuario de Facebook') ?></p>
                                <p class="mt-2 text-sm text-slate-600 whitespace-pre-line line-clamp-3"><?= esc($comment['message'] ?: 'Comentario sin texto disponible.') ?></p>
                            </td>
                            <td>
                                <div class="flex flex-col items-start gap-2">
                                    <span class="ciac-badge <?= $statusTones[$commentStatus] ?? 'ciac-badge--neutral' ?>"><?= esc($statusLabels[$commentStatus] ?? ucfirst($commentStatus)) ?></span>
                                    <?php if ((int) $comment['requires_response'] === 1): ?>
                                        <span class="ciac-badge ciac-badge--warning">Requiere atención</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="max-w-sm text-sm text-slate-500"><p class="line-clamp-2"><?= esc($comment['post_message'] ?: $comment['external_post_id']) ?></p></td>
                            <td class="text-sm text-slate-500 whitespace-nowrap"><?= esc($comment['commented_at'] ?: $comment['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= view('Modules\Shared\Views\components\pagination', ['pagination' => $pagination]) ?>
        <?php endif; ?>
    </section>

    <aside class="space-y-6">
        <section class="ciac-card">
            <header class="ciac-card__header">
                <h2 class="ciac-card__title">Reacciones recientes</h2>
                <p class="ciac-card__subtitle">Actividad más reciente sobre publicaciones.</p>
            </header>
            <div class="ciac-card__body space-y-4">
                <?php foreach ($reactions as $reaction): ?>
                    <div class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-bold text-slate-800"><?= esc($reaction['actor_name'] ?: 'Usuario de Facebook') ?></p>
                            <span class="ciac-badge ciac-badge--primary"><?= esc($reaction['reaction_type']) ?></span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500 line-clamp-2"><?= esc($reaction['post_message'] ?: $reaction['external_post_id']) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($reactions)): ?>
                    <div class="ciac-empty-state py-8">
                        <p class="ciac-empty-state__description">Todavía no existen reacciones registradas.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="ciac-card">
            <header class="ciac-card__header">
                <h2 class="ciac-card__title">Personas más activas</h2>
                <p class="ciac-card__subtitle">Participantes con mayor interacción acumulada.</p>
            </header>
            <div class="ciac-card__body space-y-4">
                <?php foreach ($participants as $index => $person): ?>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-950 text-white font-black"><?= $index + 1 ?></div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-800 truncate"><?= esc($person['name']) ?></p>
                            <p class="text-xs text-slate-500"><?= esc($person['comments_count']) ?> comentarios · <?= esc($person['reactions_count']) ?> reacciones</p>
                        </div>
                        <span class="ciac-badge ciac-badge--primary"><?= esc($person['total_interactions']) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($participants)): ?>
                    <div class="ciac-empty-state py-8">
                        <p class="ciac-empty-state__description">Sin datos de participación todavía.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </aside>
</div>

<?= $this->endSection() ?>