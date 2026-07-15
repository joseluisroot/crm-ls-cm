<?php
/** @var array<string, mixed>|null $caseItem */

$formatDuration = static function (?int $seconds): string {
    if ($seconds === null) return 'Sin datos';
    $days = intdiv($seconds, 86400);
    $hours = intdiv($seconds % 86400, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    if ($days > 0) return $days . ' d ' . $hours . ' h';
    if ($hours > 0) return $hours . ' h ' . $minutes . ' min';
    return $minutes . ' min';
};
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Caso relacionado</p>
            <h2 class="text-xl font-black text-slate-900 mt-2">
                <?= $caseItem ? esc($caseItem['public_code'] ?: ('Caso #' . $caseItem['id'])) : 'Sin expediente vinculado' ?>
            </h2>
        </div>
        <?php if ($caseItem): ?>
            <span class="px-3 py-2 rounded-full bg-blue-50 text-blue-700 text-xs font-black"><?= esc($caseItem['status_name'] ?? 'Sin estado') ?></span>
        <?php endif; ?>
    </div>

    <?php if (! $caseItem): ?>
        <p class="text-sm text-slate-500 mt-4">Esta atención no tiene un caso asociado.</p>
    <?php else: ?>
        <p class="font-bold text-slate-800 mt-5"><?= esc($caseItem['title'] ?? 'Sin título') ?></p>
        <?php if (! empty($caseItem['description'])): ?><p class="text-sm text-slate-500 mt-2 line-clamp-3"><?= esc($caseItem['description']) ?></p><?php endif; ?>

        <dl class="grid grid-cols-2 gap-4 mt-6 text-sm">
            <div><dt class="text-slate-400">Prioridad</dt><dd class="font-black text-slate-800 mt-1"><?= esc($caseItem['priority'] ?? '-') ?></dd></div>
            <div><dt class="text-slate-400">Categoría</dt><dd class="font-black text-slate-800 mt-1"><?= esc($caseItem['category_name'] ?? '-') ?></dd></div>
            <div><dt class="text-slate-400">Responsable</dt><dd class="font-black text-slate-800 mt-1"><?= esc($caseItem['assigned_user_name'] ?? 'Sin asignar') ?></dd></div>
            <div><dt class="text-slate-400"><?= ! empty($caseItem['is_closed']) ? 'Duración total' : 'Tiempo abierto' ?></dt><dd class="font-black text-slate-800 mt-1"><?= esc($formatDuration($caseItem['open_seconds'])) ?></dd></div>
        </dl>

        <div class="flex flex-wrap gap-2 mt-6">
            <a href="<?= esc($caseItem['detail_url']) ?>" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-950 text-white text-sm font-black">Abrir expediente</a>
            <?php if ($canUpdateCase): ?><a href="<?= esc($caseItem['detail_url']) ?>#case-status" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-bold">Cambiar estado</a><?php endif; ?>
            <?php if ($canAssignCase): ?><a href="<?= esc($caseItem['detail_url']) ?>#case-assignment" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-bold">Reasignar</a><?php endif; ?>
        </div>
    <?php endif; ?>
</section>
