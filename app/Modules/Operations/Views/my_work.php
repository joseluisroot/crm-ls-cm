<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="mb-8">
    <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Centro personal de atención</p>
    <h1 class="text-3xl font-black text-slate-900 mt-2">Hola, <?= esc($userName) ?></h1>
    <p class="text-slate-500 mt-2">Estas son tus asignaciones y prioridades actuales.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 mb-8">
    <?php foreach ([
        ['Pendientes', $groups['pending'], 'PENDING', 'bg-red-50 text-red-700'],
        ['En marcha', $groups['active'], 'ACTIVE', 'bg-blue-50 text-blue-700'],
        ['Esperando ciudadano', $groups['waiting'], 'WAITING', 'bg-amber-50 text-amber-700'],
        ['Borradores', $drafts, 'ACTIVE', 'bg-violet-50 text-violet-700'],
        ['Casos abiertos', $openCases, null, 'bg-slate-100 text-slate-700'],
        ['Completadas hoy', $completedToday, 'COMPLETED', 'bg-emerald-50 text-emerald-700'],
    ] as [$label, $value, $queue, $tone]): ?>
        <?php $url = $label === 'Casos abiertos' ? site_url('admin/my-cases') : site_url('admin/operations' . ($queue ? '?queue=' . $queue : '')); ?>
        <a href="<?= esc($url) ?>" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:-translate-y-0.5 hover:shadow-md transition">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold <?= $tone ?>"><?= esc($label) ?></span>
            <p class="text-4xl font-black text-slate-900 mt-4"><?= esc($value) ?></p>
        </a>
    <?php endforeach; ?>
</div>

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900">Qué atender primero</h2>
            <p class="text-sm text-slate-500 mt-1">Ordenado por prioridad, antigüedad y riesgo de SLA.</p>
        </div>
        <a href="<?= site_url('admin/operations') ?>" class="text-sm font-bold text-pink-600 hover:text-pink-700">Ver todas</a>
    </div>

    <div class="divide-y divide-slate-100">
        <?php foreach ($priority as $item): ?>
            <article class="p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 hover:bg-slate-50 transition">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700"><?= esc($item['channel']) ?></span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700"><?= esc($item['priority_name']) ?></span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700"><?= esc($item['status_name']) ?></span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-black <?= esc($item['sla']['tone']) ?>"><?= esc($item['sla']['label']) ?></span>
                    </div>
                    <h3 class="font-black text-slate-900"><?= esc($item['title'] ?: 'Atención #' . $item['id']) ?></h3>
                    <p class="text-sm text-slate-500 mt-1 line-clamp-2"><?= esc($item['summary'] ?: 'Sin resumen disponible') ?></p>
                </div>
                <div class="shrink-0 lg:text-right">
                    <p class="text-xs text-slate-400 mb-2"><?= esc($item['opened_at']) ?></p>
                    <a href="<?= site_url('admin/operations/' . $item['id']) ?>" class="inline-flex px-4 py-2 rounded-xl bg-slate-950 text-white text-sm font-bold hover:bg-pink-600 transition">Atender</a>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (empty($priority)): ?>
            <div class="p-12 text-center">
                <p class="text-lg font-bold text-slate-700">No tienes atenciones activas.</p>
                <p class="text-slate-500 mt-2">Tus nuevas asignaciones aparecerán aquí.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
