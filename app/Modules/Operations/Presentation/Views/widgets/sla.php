<?php
/** @var array<string, mixed>|null $sla */

$formatDuration = static function (?int $seconds): string {
    if ($seconds === null) return '—';
    $absolute = abs($seconds);
    $days = intdiv($absolute, 86400);
    $hours = intdiv($absolute % 86400, 3600);
    $minutes = intdiv($absolute % 3600, 60);
    $secs = $absolute % 60;

    if ($days > 0) return sprintf('%dd %02dh %02dm', $days, $hours, $minutes);
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
};

$tones = [
    'ON_TIME' => ['badge' => 'bg-emerald-50 text-emerald-700', 'bar' => 'bg-emerald-500', 'ring' => 'border-emerald-200'],
    'WARNING' => ['badge' => 'bg-amber-50 text-amber-700', 'bar' => 'bg-amber-500', 'ring' => 'border-amber-200'],
    'BREACHED' => ['badge' => 'bg-red-50 text-red-700', 'bar' => 'bg-red-500', 'ring' => 'border-red-200'],
    'COMPLETED' => ['badge' => 'bg-blue-50 text-blue-700', 'bar' => 'bg-blue-500', 'ring' => 'border-blue-200'],
    'UNAVAILABLE' => ['badge' => 'bg-slate-100 text-slate-600', 'bar' => 'bg-slate-300', 'ring' => 'border-slate-200'],
];
?>

<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm" data-sla-widget>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-black">Control de tiempos</p>
            <h2 class="text-xl font-black text-slate-900 mt-2">SLA de atención</h2>
        </div>
        <?php if ($sla): ?>
            <span class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 text-xs font-black">
                <?= esc($sla['policy']['name'] ?? 'Política SLA') ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if (! $sla): ?>
        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
            Esta atención todavía no tiene un reloj SLA asociado.
        </div>
    <?php else: ?>
        <div class="mt-6 space-y-5">
            <?php foreach ([
                ['key' => 'first_response', 'title' => 'Primera respuesta', 'due' => $sla['first_response_due_at'] ?? null, 'completed' => $sla['first_response_at'] ?? null],
                ['key' => 'resolution', 'title' => 'Resolución', 'due' => $sla['resolution_due_at'] ?? null, 'completed' => $sla['resolved_at'] ?? null],
            ] as $definition): ?>
                <?php
                    $metric = $sla[$definition['key']] ?? [];
                    $state = (string) ($metric['state'] ?? 'UNAVAILABLE');
                    $tone = $tones[$state] ?? $tones['UNAVAILABLE'];
                    $remaining = isset($metric['remaining_seconds']) ? (int) $metric['remaining_seconds'] : null;
                    $remainingText = $remaining === null
                        ? 'Sin objetivo'
                        : ($remaining < 0 ? 'Vencido por ' . $formatDuration($remaining) : 'Restan ' . $formatDuration($remaining));
                ?>
                <article class="rounded-2xl border <?= $tone['ring'] ?> p-5"
                         data-sla-metric
                         data-warning-percent="<?= esc((string) ($sla['policy']['warning_percent'] ?? 80), 'attr') ?>"
                         data-started-at="<?= esc((string) ($metric['started_at_unix'] ?? ''), 'attr') ?>"
                         data-due-at="<?= esc((string) ($metric['due_at_unix'] ?? ''), 'attr') ?>"
                         data-completed-at="<?= esc((string) ($metric['completed_at_unix'] ?? ''), 'attr') ?>"
                         data-live="<?= ! empty($metric['is_live']) ? '1' : '0' ?>">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-black text-slate-900"><?= esc($definition['title']) ?></h3>
                            <p class="text-xs text-slate-400 mt-1">Límite: <?= esc($definition['due'] ?: 'Sin fecha') ?></p>
                        </div>
                        <span data-sla-badge class="px-3 py-1 rounded-full text-xs font-black <?= $tone['badge'] ?>">
                            <?= esc($metric['label'] ?? 'Sin información') ?>
                        </span>
                    </div>

                    <div class="mt-5 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Tiempo</p>
                            <p data-sla-remaining class="text-2xl font-black text-slate-900 mt-1"><?= esc($remainingText) ?></p>
                        </div>
                        <p data-sla-percentage class="text-sm font-black text-slate-500"><?= esc((string) ($metric['raw_percentage'] ?? $metric['percentage'] ?? 0)) ?>%</p>
                    </div>

                    <div class="mt-4 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                        <div data-sla-progress class="h-full rounded-full transition-all duration-500 <?= $tone['bar'] ?>" style="width: <?= esc((string) ($metric['percentage'] ?? 0), 'attr') ?>%"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-400">Transcurrido</p>
                            <p data-sla-elapsed class="font-black text-slate-800 mt-1"><?= esc($formatDuration((int) ($metric['elapsed_seconds'] ?? 0))) ?></p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-400">Completado</p>
                            <p class="font-black text-slate-800 mt-1"><?= esc($definition['completed'] ?: 'Pendiente') ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
(() => {
    const formatDuration = (seconds) => {
        const value = Math.abs(Math.trunc(seconds));
        const days = Math.floor(value / 86400);
        const hours = Math.floor((value % 86400) / 3600);
        const minutes = Math.floor((value % 3600) / 60);
        const secs = value % 60;
        if (days > 0) return `${days}d ${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m`;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    };

    const tones = {
        ON_TIME: { badge: 'px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700', bar: 'h-full rounded-full transition-all duration-500 bg-emerald-500' },
        WARNING: { badge: 'px-3 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-700', bar: 'h-full rounded-full transition-all duration-500 bg-amber-500' },
        BREACHED: { badge: 'px-3 py-1 rounded-full text-xs font-black bg-red-50 text-red-700', bar: 'h-full rounded-full transition-all duration-500 bg-red-500' },
    };

    document.querySelectorAll('[data-sla-metric][data-live="1"]').forEach((element) => {
        const startedAt = Number(element.dataset.startedAt || 0);
        const dueAt = Number(element.dataset.dueAt || 0);
        const warningPercent = Number(element.dataset.warningPercent || 80);
        if (!startedAt || !dueAt || dueAt <= startedAt) return;

        const update = () => {
            const now = Math.floor(Date.now() / 1000);
            const target = dueAt - startedAt;
            const elapsed = Math.max(0, now - startedAt);
            const remaining = dueAt - now;
            const rawPercentage = Math.round((elapsed / target) * 100);
            const percentage = Math.max(0, Math.min(100, rawPercentage));
            const state = remaining < 0 ? 'BREACHED' : (rawPercentage >= warningPercent ? 'WARNING' : 'ON_TIME');
            const label = state === 'BREACHED' ? 'Vencido' : (state === 'WARNING' ? 'Próximo a vencer' : 'Dentro del tiempo');
            const tone = tones[state];

            element.querySelector('[data-sla-badge]').textContent = label;
            element.querySelector('[data-sla-badge]').className = tone.badge;
            element.querySelector('[data-sla-progress]').className = tone.bar;
            element.querySelector('[data-sla-progress]').style.width = `${percentage}%`;
            element.querySelector('[data-sla-percentage]').textContent = `${rawPercentage}%`;
            element.querySelector('[data-sla-elapsed]').textContent = formatDuration(elapsed);
            element.querySelector('[data-sla-remaining]').textContent = remaining < 0
                ? `Vencido por ${formatDuration(remaining)}`
                : `Restan ${formatDuration(remaining)}`;
        };

        update();
        window.setInterval(update, 1000);
    });
})();
</script>
