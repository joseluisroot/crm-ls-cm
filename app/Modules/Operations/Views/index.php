<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Unified Work Queue</p>
        <h1 class="text-3xl font-black text-slate-900 mt-2">Citizen Operations Center</h1>
        <p class="text-slate-500 mt-2">Una sola cola para atender interacciones ciudadanas, sin importar el canal de origen.</p>
    </div>

    <form method="post" action="<?= site_url('admin/operations/import-facebook-comments') ?>">
        <?= csrf_field() ?>
        <button class="px-5 py-3 rounded-xl bg-slate-950 text-white font-bold hover:bg-pink-600 transition">
            Sincronizar comentarios pendientes
        </button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5 mb-8">
    <?php foreach ([
        ['label' => 'Total', 'value' => $summary['total'], 'tone' => 'bg-slate-100 text-slate-700'],
        ['label' => 'Nuevos', 'value' => $summary['new'], 'tone' => 'bg-red-50 text-red-700'],
        ['label' => 'Asignados', 'value' => $summary['assigned'], 'tone' => 'bg-amber-50 text-amber-700'],
        ['label' => 'En proceso', 'value' => $summary['in_progress'], 'tone' => 'bg-blue-50 text-blue-700'],
        ['label' => 'Resueltos', 'value' => $summary['resolved'], 'tone' => 'bg-green-50 text-green-700'],
    ] as $card): ?>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold <?= $card['tone'] ?>"><?= esc($card['label']) ?></span>
            <p class="text-4xl font-black text-slate-900 mt-4"><?= esc($card['value']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex flex-col 2xl:flex-row 2xl:items-center 2xl:justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900">Cola operacional</h2>
            <p class="text-sm text-slate-500 mt-1">Ordenada por prioridad y antigüedad.</p>
        </div>

        <form method="get" class="flex flex-wrap gap-3">
            <select name="status" class="rounded-xl border border-slate-300 px-4 py-2 bg-white">
                <option value="">Todos los estados</option>
                <?php foreach ($statuses as $option): ?>
                    <option value="<?= esc($option['code']) ?>" <?= $status === $option['code'] ? 'selected' : '' ?>>
                        <?= esc($option['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="priority" class="rounded-xl border border-slate-300 px-4 py-2 bg-white">
                <option value="">Todas las prioridades</option>
                <?php foreach ($priorities as $option): ?>
                    <option value="<?= esc($option['code']) ?>" <?= $priority === $option['code'] ? 'selected' : '' ?>>
                        <?= esc($option['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="limit" min="1" max="200" value="<?= esc($limit) ?>" class="w-24 rounded-xl border border-slate-300 px-4 py-2">
            <button class="px-4 py-2 rounded-xl bg-pink-600 text-white font-bold">Filtrar</button>
        </form>
    </div>

    <div class="divide-y divide-slate-100">
        <?php foreach ($items as $item): ?>
            <?php
                $priorityTone = match ($item['priority']) {
                    'CRITICAL' => 'bg-red-100 text-red-800',
                    'HIGH' => 'bg-orange-100 text-orange-800',
                    'LOW' => 'bg-slate-100 text-slate-600',
                    default => 'bg-blue-50 text-blue-700',
                };
            ?>
            <article class="p-6 hover:bg-slate-50 transition">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-black bg-pink-50 text-pink-700"><?= esc($item['channel']) ?></span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $priorityTone ?>"><?= esc($item['priority_name']) ?></span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700"><?= esc($item['status_name']) ?></span>
                        </div>

                        <h3 class="text-lg font-black text-slate-900 mt-4"><?= esc($item['author_name'] ?: $item['title']) ?></h3>
                        <p class="text-slate-700 mt-2 whitespace-pre-line"><?= esc($item['comment_message'] ?: $item['summary']) ?></p>

                        <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-4">
                            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Contexto de publicación</p>
                            <p class="text-sm text-slate-600 mt-1 line-clamp-2"><?= esc($item['post_message'] ?: $item['external_post_id'] ?: 'Sin contexto disponible') ?></p>
                            <?php if (! empty($item['permalink_url'])): ?>
                                <a href="<?= esc($item['permalink_url']) ?>" target="_blank" rel="noopener" class="inline-flex mt-3 text-sm font-bold text-pink-600 hover:text-pink-700">Ver en Facebook ↗</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="xl:text-right shrink-0">
                        <p class="text-sm text-slate-400"><?= esc($item['commented_at'] ?: $item['opened_at'] ?: $item['created_at']) ?></p>
                        <p class="text-xs text-slate-400 mt-2">Work Item #<?= esc($item['id']) ?></p>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (empty($items)): ?>
            <div class="p-12 text-center">
                <p class="text-lg font-bold text-slate-700">La cola está vacía.</p>
                <p class="text-slate-500 mt-2">Sincroniza los comentarios pendientes para crear los primeros Work Items.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
