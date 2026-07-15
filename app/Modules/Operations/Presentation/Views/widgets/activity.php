<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Trazabilidad</p>
            <h2 class="text-xl font-black text-slate-900 mt-2">Actividad reciente</h2>
        </div>
        <span class="px-3 py-2 rounded-full bg-slate-100 text-slate-600 text-xs font-black"><?= esc($total ?? 0) ?> eventos</span>
    </div>

    <?php if (empty($items)): ?>
        <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
            Todavía no hay actividad operativa disponible para esta atención.
        </div>
    <?php else: ?>
        <div class="mt-6 space-y-1">
            <?php foreach ($items as $activity): ?>
                <?php
                    $status = strtoupper((string) ($activity['status'] ?? 'INFO'));
                    $dotClass = $status === 'ERROR'
                        ? 'bg-red-500'
                        : ($status === 'SUCCESS' ? 'bg-emerald-500' : 'bg-blue-500');
                ?>
                <article class="relative pl-7 pb-6 last:pb-0">
                    <span class="absolute left-0 top-1.5 w-3 h-3 rounded-full <?= $dotClass ?> ring-4 ring-white"></span>
                    <span class="absolute left-[5px] top-5 bottom-0 w-px bg-slate-200 last:hidden"></span>
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-black text-slate-900"><?= esc($activity['title']) ?></h3>
                            <p class="text-sm text-slate-600 mt-1"><?= esc($activity['description']) ?></p>
                            <p class="text-xs text-slate-400 mt-2">Actor: <?= esc($activity['actor']) ?></p>
                        </div>
                        <time class="text-xs font-bold text-slate-400 whitespace-nowrap"><?= esc($activity['occurred_at']) ?></time>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
