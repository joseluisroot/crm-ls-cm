<?php
/** @var array<int, array<string, mixed>> $assignments */
/** @var array<string, mixed> $metrics */

$formatDuration = static function (?int $seconds): string {
    if ($seconds === null) return 'Sin datos';
    $days = intdiv($seconds, 86400);
    $hours = intdiv($seconds % 86400, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    if ($days > 0) return $days . ' d ' . $hours . ' h';
    if ($hours > 0) return $hours . ' h ' . $minutes . ' min';
    return max(0, $minutes) . ' min';
};
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-violet-600 font-black">Ownership</p>
            <h2 class="text-xl font-black text-slate-900 mt-2">Historial de asignaciones</h2>
            <p class="text-sm text-slate-500 mt-1">Responsables y tiempo de custodia de la atención.</p>
        </div>
        <span class="px-3 py-1.5 rounded-full bg-violet-50 text-violet-700 text-xs font-black">
            <?= esc((string) ($metrics['reassignments'] ?? 0)) ?> reasignaciones
        </span>
    </div>

    <div class="grid grid-cols-2 gap-3 mt-6">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Responsable actual</p>
            <p class="font-black text-slate-900 mt-2"><?= esc($metrics['current_user_name'] ?: 'Sin asignar') ?></p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Tiempo asignado</p>
            <p class="font-black text-slate-900 mt-2"><?= esc($formatDuration((int) ($metrics['total_assignment_seconds'] ?? 0))) ?></p>
        </div>
    </div>

    <?php if (empty($assignments)): ?>
        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
            Esta atención todavía no tiene eventos de asignación registrados.
        </div>
    <?php else: ?>
        <div class="mt-7 space-y-0">
            <?php foreach ($assignments as $index => $assignment): ?>
                <article class="relative pl-9 <?= $index < count($assignments) - 1 ? 'pb-7 border-l-2 border-slate-200 ml-2' : 'ml-2' ?>">
                    <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full ring-4 <?= ! empty($assignment['is_current']) ? 'bg-violet-500 ring-violet-100' : 'bg-slate-400 ring-slate-100' ?>"></span>
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-black text-slate-900"><?= esc($assignment['user_name']) ?></p>
                                <?php if (! empty($assignment['is_current'])): ?>
                                    <span class="px-2.5 py-1 rounded-full bg-violet-50 text-violet-700 text-xs font-black">Actual</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">
                                Desde <?= esc($assignment['started_at'] ?: 'Sin fecha') ?>
                                <?php if (! empty($assignment['ended_at'])): ?> · hasta <?= esc($assignment['ended_at']) ?><?php endif; ?>
                            </p>
                        </div>
                        <span class="text-sm font-black text-slate-700 whitespace-nowrap"><?= esc($formatDuration($assignment['duration_seconds'])) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
