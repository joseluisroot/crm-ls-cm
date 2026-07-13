<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$kpis = $analytics['kpis'] ?? [];
$activity = $analytics['activity_by_date'] ?? [];
$maxActivity = 1;
foreach ($activity as $day) {
    $maxActivity = max($maxActivity, (int) ($day['total'] ?? 0));
}
?>

<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <a href="<?= site_url('admin/publications') ?>" class="text-sm font-bold text-pink-600">← Volver a publicaciones</a>
        <h1 class="text-3xl font-black text-slate-900 mt-3">Publicación #<?= esc($publication['id']) ?></h1>
        <p class="text-slate-400 text-sm mt-2"><?= esc($publication['external_post_id'] ?? '-') ?></p>
    </div>
    <?php if (! empty($publication['permalink_url'])): ?>
        <a href="<?= esc($publication['permalink_url']) ?>" target="_blank" rel="noopener" class="px-5 py-3 rounded-xl bg-blue-600 text-white font-bold">Ver en Facebook ↗</a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <?php foreach ([
        'Comentarios' => $metrics['comments'],
        'Pendientes' => $metrics['pending_comments'],
        'Reacciones' => $metrics['reactions'],
        'Participantes' => $metrics['participants'],
        'Work Items' => $metrics['work_items'],
        'Casos' => $metrics['cases'],
    ] as $label => $value): ?>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs uppercase tracking-widest text-slate-400"><?= esc($label) ?></p>
            <p class="text-3xl font-black text-slate-900 mt-2"><?= esc($value) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-6">
    <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Contenido de la publicación</p>
    <p class="text-slate-700 mt-4 whitespace-pre-line text-lg"><?= esc($publication['message'] ?: 'Publicación sin texto disponible.') ?></p>
    <p class="text-xs text-slate-400 mt-5">Registrada: <?= esc($publication['created_at'] ?? '-') ?></p>
</section>

<section class="bg-slate-950 text-white rounded-2xl p-6 shadow-sm mb-6">
    <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-pink-400 font-bold">Publication Analytics</p>
            <h2 class="text-2xl font-black mt-2">Indicadores de atención e impacto</h2>
            <p class="text-slate-400 mt-2">Lectura operativa basada en comentarios, reacciones, participantes y seguimiento generado.</p>
        </div>
        <div class="text-left xl:text-right">
            <p class="text-xs uppercase tracking-widest text-slate-500">Interacciones registradas</p>
            <p class="text-4xl font-black mt-1"><?= esc($kpis['total_interactions'] ?? 0) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mt-7">
        <?php foreach ([
            'Tasa de respuesta' => ($kpis['response_rate'] ?? 0) . '%',
            'Atención pendiente' => ($kpis['pending_rate'] ?? 0) . '%',
            'Conversión a trabajo' => ($kpis['work_item_conversion_rate'] ?? 0) . '%',
            'Conversión a caso' => ($kpis['case_conversion_rate'] ?? 0) . '%',
            'Concentración principal' => ($kpis['top_participant_share'] ?? 0) . '%',
        ] as $label => $value): ?>
            <div class="rounded-xl bg-slate-900 border border-slate-800 p-4">
                <p class="text-xs uppercase tracking-wider text-slate-500"><?= esc($label) ?></p>
                <p class="text-2xl font-black mt-2"><?= esc($value) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-xl font-black text-slate-900">Actividad cronológica</h2>
        <p class="text-sm text-slate-500 mt-1">Volumen diario de comentarios y reacciones capturadas.</p>

        <div class="mt-6 space-y-4">
            <?php foreach ($activity as $day): ?>
                <?php $width = max(4, ((int) $day['total'] / $maxActivity) * 100); ?>
                <div>
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="font-bold text-slate-700"><?= esc($day['date']) ?></span>
                        <span class="text-slate-500"><?= esc($day['comments']) ?> comentarios · <?= esc($day['reactions']) ?> reacciones</span>
                    </div>
                    <div class="h-3 rounded-full bg-slate-100 overflow-hidden mt-2">
                        <div class="h-full rounded-full bg-pink-600" style="width: <?= esc((string) $width) ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($activity)): ?>
                <p class="text-slate-500">Todavía no existe suficiente actividad para construir la serie temporal.</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="space-y-6">
        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Estado de comentarios</h2>
            <div class="mt-5 space-y-3">
                <?php foreach (($analytics['status_breakdown'] ?? []) as $status => $total): ?>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 border border-slate-200 px-4 py-3">
                        <span class="font-bold text-slate-700"><?= esc($status) ?></span>
                        <span class="font-black text-slate-900"><?= esc($total) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($analytics['status_breakdown'])): ?><p class="text-slate-500">Sin estados disponibles.</p><?php endif; ?>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Prioridad operativa</h2>
            <div class="mt-5 space-y-3">
                <?php foreach (($analytics['priority_breakdown'] ?? []) as $priority => $total): ?>
                    <div class="flex items-center justify-between rounded-xl bg-amber-50 border border-amber-200 px-4 py-3">
                        <span class="font-bold text-amber-800"><?= esc($priority) ?></span>
                        <span class="font-black text-amber-950"><?= esc($total) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($analytics['priority_breakdown'])): ?><p class="text-slate-500">Todavía no existen Work Items priorizados.</p><?php endif; ?>
            </div>
        </section>
    </div>
</div>

<div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
    <div class="2xl:col-span-2 space-y-6">
        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Comentarios</h2>
            <p class="text-sm text-slate-500 mt-1">Comentarios recibidos y su relación operativa.</p>

            <div class="mt-6 space-y-4">
                <?php foreach ($comments as $comment): ?>
                    <article class="rounded-xl border border-slate-200 p-5">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                            <div>
                                <p class="font-black text-slate-800"><?= esc($comment['author_name'] ?: 'Usuario de Facebook') ?></p>
                                <p class="text-xs text-slate-400 mt-1"><?= esc($comment['commented_at'] ?? '-') ?></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold"><?= esc($comment['status'] ?? 'received') ?></span>
                                <?php if (! empty($comment['work_item_id'])): ?>
                                    <a href="<?= site_url('admin/operations/' . $comment['work_item_id']) ?>" class="px-3 py-1 rounded-full bg-pink-50 text-pink-700 text-xs font-bold">Work Item #<?= esc($comment['work_item_id']) ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-slate-700 mt-4 whitespace-pre-line"><?= esc($comment['message'] ?: 'Comentario sin texto disponible.') ?></p>
                    </article>
                <?php endforeach; ?>

                <?php if (empty($comments)): ?>
                    <p class="text-slate-500">Esta publicación todavía no tiene comentarios registrados.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Reacciones</h2>
            <div class="mt-5 space-y-3">
                <?php foreach ($reaction_breakdown as $type => $total): ?>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 border border-slate-200 px-4 py-3">
                        <span class="font-bold text-slate-700"><?= esc($type) ?></span>
                        <span class="font-black text-slate-900"><?= esc($total) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($reaction_breakdown)): ?><p class="text-slate-500">Sin reacciones activas.</p><?php endif; ?>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Participantes principales</h2>
            <div class="mt-5 space-y-4">
                <?php foreach (array_slice($participants, 0, 15) as $participant): ?>
                    <div class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                        <p class="font-bold text-slate-800"><?= esc($participant['name']) ?></p>
                        <p class="text-xs text-slate-400 mt-1"><?= esc($participant['external_id']) ?></p>
                        <p class="text-sm text-slate-600 mt-2"><?= esc($participant['comments_count']) ?> comentarios · <?= esc($participant['reactions_count']) ?> reacciones</p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($participants)): ?><p class="text-slate-500">Sin participantes identificados.</p><?php endif; ?>
            </div>
        </section>
    </aside>
</div>

<?= $this->endSection() ?>
