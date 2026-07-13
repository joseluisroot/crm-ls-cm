<?= $this->extend('Modules\Dashboard\Views\layout') ?>
<?= $this->section('content') ?>

<?php
$kpis = $analytics['kpis'] ?? [];
$activity = $analytics['activity_by_date'] ?? [];
$threadMetrics = $commentThreads['metrics'] ?? [];
$threads = $commentThreads['threads'] ?? [];
$identityMetrics = $identity_metrics ?? [];
$maxActivity = 1;
foreach ($activity as $day) $maxActivity = max($maxActivity, (int) ($day['total'] ?? 0));
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

<?php foreach ([
    'participant_resolution_success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
    'participant_resolution_info' => 'border-blue-200 bg-blue-50 text-blue-800',
    'participant_resolution_warning' => 'border-amber-200 bg-amber-50 text-amber-800',
] as $flashKey => $classes): ?>
    <?php if (session()->getFlashdata($flashKey)): ?>
        <div class="mb-6 rounded-2xl border px-5 py-4 font-semibold <?= $classes ?>">
            <?= esc(session()->getFlashdata($flashKey)) ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-4 mb-8">
    <?php foreach ([
        'Comentarios' => $metrics['comments'], 'Pendientes' => $metrics['pending_comments'],
        'Reacciones' => $metrics['reactions'], 'Participantes' => $metrics['participants'],
        'Identificados' => $identityMetrics['identified_participants'] ?? 0,
        'Sin vincular' => $identityMetrics['unidentified_participants'] ?? 0,
        'Work Items' => $metrics['work_items'], 'Casos' => $metrics['cases'],
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
        <div><p class="text-xs uppercase tracking-[0.25em] text-pink-400 font-bold">Publication Analytics</p><h2 class="text-2xl font-black mt-2">Indicadores de atención e impacto</h2></div>
        <div><p class="text-xs uppercase tracking-widest text-slate-500">Interacciones</p><p class="text-4xl font-black mt-1"><?= esc($kpis['total_interactions'] ?? 0) ?></p></div>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mt-7">
        <?php foreach ([
            'Tasa de respuesta' => ($kpis['response_rate'] ?? 0) . '%',
            'Atención pendiente' => ($kpis['pending_rate'] ?? 0) . '%',
            'Conversión a trabajo' => ($kpis['work_item_conversion_rate'] ?? 0) . '%',
            'Conversión a caso' => ($kpis['case_conversion_rate'] ?? 0) . '%',
            'Concentración principal' => ($kpis['top_participant_share'] ?? 0) . '%',
        ] as $label => $value): ?>
            <div class="rounded-xl bg-slate-900 border border-slate-800 p-4"><p class="text-xs uppercase tracking-wider text-slate-500"><?= esc($label) ?></p><p class="text-2xl font-black mt-2"><?= esc($value) ?></p></div>
        <?php endforeach; ?>
    </div>
</section>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-xl font-black text-slate-900">Actividad cronológica</h2>
        <div class="mt-6 space-y-4">
            <?php foreach ($activity as $day): $width = max(4, ((int) $day['total'] / $maxActivity) * 100); ?>
                <div><div class="flex justify-between text-sm"><span class="font-bold"><?= esc($day['date']) ?></span><span><?= esc($day['comments']) ?> comentarios · <?= esc($day['reactions']) ?> reacciones</span></div><div class="h-3 rounded-full bg-slate-100 mt-2"><div class="h-full rounded-full bg-pink-600" style="width: <?= esc((string) $width) ?>%"></div></div></div>
            <?php endforeach; ?>
            <?php if (empty($activity)): ?><p class="text-slate-500">Sin actividad suficiente.</p><?php endif; ?>
        </div>
    </section>
    <div class="space-y-6">
        <?php foreach (['Estado de comentarios' => $analytics['status_breakdown'] ?? [], 'Prioridad operativa' => $analytics['priority_breakdown'] ?? []] as $titleBlock => $rows): ?>
            <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm"><h2 class="text-xl font-black"><?= esc($titleBlock) ?></h2><div class="mt-5 space-y-3"><?php foreach ($rows as $name => $total): ?><div class="flex justify-between rounded-xl bg-slate-50 border p-3"><span class="font-bold"><?= esc($name) ?></span><span class="font-black"><?= esc($total) ?></span></div><?php endforeach; ?><?php if (empty($rows)): ?><p class="text-slate-500">Sin datos.</p><?php endif; ?></div></section>
        <?php endforeach; ?>
    </div>
</div>

<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-6">
    <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
        <div><p class="text-xs uppercase tracking-[0.22em] text-pink-600 font-bold">Comment Threads</p><h2 class="text-2xl font-black text-slate-900 mt-2">Conversaciones de la publicación</h2><p class="text-sm text-slate-500 mt-2">Los autores vinculados permiten abrir directamente su perfil ciudadano.</p></div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <?php foreach (['Raíces' => $threadMetrics['root_comments'] ?? 0, 'Respuestas' => $threadMetrics['replies'] ?? 0, 'Profundidad' => $threadMetrics['max_depth'] ?? 0, 'Huérfanos' => $threadMetrics['orphan_comments'] ?? 0] as $label => $value): ?>
                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3"><p class="text-[11px] uppercase text-slate-400"><?= esc($label) ?></p><p class="text-xl font-black mt-1"><?= esc($value) ?></p></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="mt-7 space-y-5">
        <?php foreach ($threads as $thread): ?><?= view('Modules\Publication\Views\components\comment_thread', ['comment' => $thread]) ?><?php endforeach; ?>
        <?php if (empty($threads)): ?><p class="text-slate-500">Esta publicación todavía no tiene comentarios registrados.</p><?php endif; ?>
    </div>
</section>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm"><h2 class="text-xl font-black">Reacciones</h2><div class="mt-5 space-y-3"><?php foreach ($reaction_breakdown as $type => $total): ?><div class="flex justify-between rounded-xl bg-slate-50 border p-3"><span class="font-bold"><?= esc($type) ?></span><span class="font-black"><?= esc($total) ?></span></div><?php endforeach; ?><?php if (empty($reaction_breakdown)): ?><p class="text-slate-500">Sin reacciones activas.</p><?php endif; ?></div></section>

    <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black">Participantes principales</h2>
                <p class="text-sm text-slate-500 mt-1">Vinculación exacta por identidad social de Facebook.</p>
            </div>
            <?php if (($identityMetrics['unidentified_participants'] ?? 0) > 0): ?>
                <form method="post" action="<?= site_url('admin/publications/' . $publication['id'] . '/resolve-participants') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-pink-600 text-white text-sm font-black hover:bg-pink-700" onclick="return confirm('Se crearán Citizens para los participantes aún no vinculados. ¿Continuar?')">
                        Resolver <?= esc($identityMetrics['unidentified_participants']) ?> pendiente(s)
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <div class="mt-5 space-y-4">
            <?php foreach (array_slice($participants, 0, 15) as $participant): ?>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <?php if (! empty($participant['citizen_id'])): ?>
                                <a href="<?= site_url('admin/citizens/' . $participant['citizen_id']) ?>" class="font-black text-pink-700 hover:text-pink-900"><?= esc($participant['citizen_name'] ?: $participant['name']) ?></a>
                                <p class="text-xs text-emerald-700 font-bold mt-1">Identidad resuelta · <?= esc($participant['identity_actor_type']) ?> · <?= esc($participant['identity_confidence']) ?>%</p>
                            <?php else: ?>
                                <p class="font-bold text-slate-800"><?= esc($participant['name']) ?></p>
                                <p class="text-xs text-slate-400 mt-1">Participante todavía no vinculado</p>
                            <?php endif; ?>
                        </div>
                        <span class="px-3 py-1 rounded-full <?= ! empty($participant['citizen_id']) ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' ?> text-xs font-bold"><?= ! empty($participant['citizen_id']) ? 'Citizen #' . esc($participant['citizen_id']) : 'Sin Citizen' ?></span>
                    </div>
                    <p class="text-sm text-slate-600 mt-3"><?= esc($participant['comments_count']) ?> comentarios · <?= esc($participant['reactions_count']) ?> reacciones</p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($participants)): ?><p class="text-slate-500">Sin participantes identificados.</p><?php endif; ?>
        </div>
    </section>
</div>

<?= $this->endSection() ?>
