<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$total = (int) ($pagination['total'] ?? count($participants));
$currentPage = (int) ($pagination['page'] ?? 1);
$currentPerPage = (int) ($pagination['perPage'] ?? $perPage ?? 25);
$from = $total === 0 ? 0 : (($currentPage - 1) * $currentPerPage) + 1;
$to = min($currentPage * $currentPerPage, $total);
$totalComments = array_sum(array_map(static fn (array $person): int => (int) ($person['comments_count'] ?? 0), $participants));
$totalReactions = array_sum(array_map(static fn (array $person): int => (int) ($person['reactions_count'] ?? 0), $participants));
?>

<div class="ciac-page-header xl:flex-row xl:items-center xl:justify-between">
    <div>
        <p class="ciac-page-eyebrow">Citizen Intelligence</p>
        <h1 class="ciac-page-title mt-2">Participación ciudadana</h1>
        <p class="ciac-page-description">Personas con mayor actividad pública registrada en comentarios y reacciones.</p>
    </div>
    <a href="<?= site_url('admin/engagement') ?>" class="ciac-btn ciac-btn--outline">Volver al Engagement Center</a>
</div>

<div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-3">
    <section class="ciac-card p-5">
        <p class="ciac-page-eyebrow">Participantes encontrados</p>
        <p class="mt-3 text-3xl font-black text-slate-900"><?= esc($total) ?></p>
        <p class="mt-1 text-sm text-slate-500">Personas dentro de los filtros actuales.</p>
    </section>
    <section class="ciac-card p-5">
        <p class="ciac-page-eyebrow">Comentarios visibles</p>
        <p class="mt-3 text-3xl font-black text-violet-700"><?= esc($totalComments) ?></p>
        <p class="mt-1 text-sm text-slate-500">Suma correspondiente a esta página.</p>
    </section>
    <section class="ciac-card p-5">
        <p class="ciac-page-eyebrow">Reacciones visibles</p>
        <p class="mt-3 text-3xl font-black text-pink-700"><?= esc($totalReactions) ?></p>
        <p class="mt-1 text-sm text-slate-500">Suma correspondiente a esta página.</p>
    </section>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <section class="ciac-card overflow-hidden xl:col-span-2">
        <header class="ciac-card__header">
            <div class="flex flex-col gap-5">
                <div>
                    <p class="ciac-page-eyebrow">Ranking de actividad pública</p>
                    <h2 class="ciac-card__title mt-2">Participación registrada</h2>
                    <p class="ciac-card__subtitle">El ranking refleja volumen de actividad pública, no afinidad política ni intención de apoyo.</p>
                    <p class="mt-2 text-xs font-semibold text-slate-400">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> participantes.</p>
                </div>

                <form method="get" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_160px_auto_auto]" data-loading="Aplicando filtros...">
                    <div>
                        <label for="participants-search" class="ciac-label">Buscar participante</label>
                        <input id="participants-search" type="search" name="q" value="<?= esc($search) ?>" placeholder="Nombre o identificador externo" class="ciac-field">
                    </div>
                    <div>
                        <label for="participants-per-page" class="ciac-label">Registros</label>
                        <select id="participants-per-page" name="per_page" class="ciac-select" onchange="this.form.submit()">
                            <?php foreach ([10, 25, 50, 100] as $option): ?>
                                <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="ciac-btn ciac-btn--primary w-full">Buscar</button>
                    </div>
                    <?php if ($search !== ''): ?>
                        <div class="flex items-end">
                            <a href="<?= site_url('admin/engagement/participants') ?>" class="ciac-btn ciac-btn--outline w-full">Limpiar</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </header>

        <?php if (empty($participants)): ?>
            <div class="ciac-empty-state">
                <div class="ciac-empty-state__icon">👥</div>
                <h3 class="ciac-empty-state__title">No se encontraron participantes</h3>
                <p class="ciac-empty-state__description">Prueba con otro nombre o identificador. Las personas aparecerán aquí cuando registren comentarios o reacciones públicas.</p>
                <a href="<?= site_url('admin/engagement/participants') ?>" class="ciac-btn ciac-btn--outline mt-5">Limpiar filtros</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="ciac-table min-w-[900px]">
                    <thead>
                    <tr>
                        <th>Posición</th>
                        <th>Persona</th>
                        <th>Comentarios</th>
                        <th>Reacciones</th>
                        <th>Interacciones</th>
                        <th>Última actividad</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($participants as $index => $person): ?>
                        <?php $position = (($pagination['page'] - 1) * $pagination['perPage']) + $index + 1; ?>
                        <tr>
                            <td>
                                <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-full bg-slate-950 px-2 text-sm font-black text-white">#<?= esc($position) ?></span>
                            </td>
                            <td>
                                <p class="font-black text-slate-900"><?= esc($person['name']) ?></p>
                                <p class="mt-1 text-xs text-slate-400"><?= esc($person['external_id']) ?></p>
                            </td>
                            <td><span class="ciac-badge ciac-badge--neutral"><?= esc($person['comments_count']) ?></span></td>
                            <td><span class="ciac-badge ciac-badge--info"><?= esc($person['reactions_count']) ?></span></td>
                            <td><span class="ciac-badge ciac-badge--primary"><?= esc($person['total_interactions']) ?></span></td>
                            <td class="whitespace-nowrap text-sm text-slate-500"><?= esc($person['last_interaction_at'] ?: 'Sin fecha') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?= view('Modules\Shared\Views\components\pagination', ['pagination' => $pagination]) ?>
        <?php endif; ?>
    </section>

    <aside class="space-y-6">
        <section class="ciac-card p-6">
            <p class="ciac-page-eyebrow">Comportamiento público</p>
            <h2 class="ciac-card__title mt-2">Distribución de reacciones</h2>
            <p class="ciac-card__subtitle">Totales globales de reacciones activas registradas.</p>

            <?php if (empty($reactionBreakdown)): ?>
                <div class="ciac-empty-state py-10">
                    <div class="ciac-empty-state__icon">👍</div>
                    <h3 class="ciac-empty-state__title">Sin reacciones registradas</h3>
                    <p class="ciac-empty-state__description">La distribución aparecerá cuando Facebook reporte nuevas reacciones.</p>
                </div>
            <?php else: ?>
                <div class="mt-5 space-y-3">
                    <?php foreach ($reactionBreakdown as $reaction): ?>
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <span class="font-bold text-slate-700"><?= esc($reaction['reaction_type']) ?></span>
                            <span class="ciac-badge ciac-badge--neutral"><?= esc($reaction['total']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
            <p class="font-black">Criterio de uso responsable</p>
            <p class="mt-2 text-sm">La actividad pública sirve para priorizar atención y comprender temas de interés. No debe interpretarse automáticamente como apoyo, oposición o afiliación política.</p>
        </section>
    </aside>
</div>

<?= $this->endSection() ?>
