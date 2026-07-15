<?php
$formatDuration = static function (?int $seconds): string {
    if ($seconds === null) return '—';
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $remaining = $seconds % 60;
    return $hours > 0
        ? sprintf('%02d:%02d:%02d', $hours, $minutes, $remaining)
        : sprintf('%02d:%02d', $minutes, $remaining);
};
$tone = [
    'blue' => 'bg-blue-500 ring-blue-100',
    'violet' => 'bg-violet-500 ring-violet-100',
    'amber' => 'bg-amber-500 ring-amber-100',
    'green' => 'bg-emerald-500 ring-emerald-100',
    'slate' => 'bg-slate-400 ring-slate-100',
];
?>

<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
        <div>
            <p class="text-xs uppercase tracking-widest text-pink-600 font-black">Citizen Timeline</p>
            <h2 class="text-xl font-black text-slate-900 mt-2">Historia de la atención</h2>
            <p class="text-sm text-slate-500 mt-1">Eventos y tiempos clave del ciclo operativo.</p>
        </div>
        <span class="inline-flex px-3 py-2 rounded-full text-xs font-black <?= ! empty($metrics['is_resolved']) ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' ?>">
            <?= ! empty($metrics['is_resolved']) ? 'Atención resuelta' : 'Atención activa' ?>
        </span>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-5 gap-3 mt-6">
        <?php foreach ([
            ['label' => 'Tiempo total', 'value' => $metrics['total_seconds'] ?? null],
            ['label' => 'Hasta asignación', 'value' => $metrics['assignment_seconds'] ?? null],
            ['label' => 'Primer borrador', 'value' => $metrics['first_draft_seconds'] ?? null],
            ['label' => 'Primera respuesta', 'value' => $metrics['first_response_seconds'] ?? null],
            ['label' => 'Responsable actual', 'value' => $metrics['current_owner_seconds'] ?? null],
        ] as $metric): ?>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] uppercase tracking-widest text-slate-400 font-bold"><?= esc($metric['label']) ?></p>
                <p class="text-lg font-black text-slate-900 mt-2 tabular-nums"><?= esc($formatDuration($metric['value'])) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-7">
        <?php foreach ($events as $index => $event): ?>
            <div class="relative pl-9 <?= $index < count($events) - 1 ? 'pb-7 border-l-2 border-slate-200 ml-2' : 'ml-2' ?>">
                <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full ring-4 <?= $tone[$event['tone']] ?? $tone['slate'] ?>"></span>
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                    <div>
                        <p class="font-black text-slate-900"><?= esc($event['label']) ?></p>
                        <p class="text-sm text-slate-500 mt-1"><?= esc($event['description']) ?></p>
                    </div>
                    <time class="text-xs text-slate-400 shrink-0"><?= esc($event['occurred_at']) ?></time>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($events)): ?>
            <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center">
                <p class="font-bold text-slate-700">Todavía no existen eventos para esta atención.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
