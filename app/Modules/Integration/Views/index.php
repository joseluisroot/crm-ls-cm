<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-900">Replay Center</h1>
        <p class="text-slate-500 mt-2">Eventos externos, estados, tiempos de procesamiento y replays auditables.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-8">
    <?php foreach ([
        ['label' => 'Eventos', 'value' => $metrics['total_events'], 'class' => 'text-slate-900'],
        ['label' => 'Procesados', 'value' => $metrics['processed_events'], 'class' => 'text-emerald-700'],
        ['label' => 'Fallidos', 'value' => $metrics['failed_events'], 'class' => 'text-red-700'],
        ['label' => 'Replays', 'value' => $metrics['replay_events'], 'class' => 'text-violet-700'],
        ['label' => 'Promedio', 'value' => $metrics['average_processing_ms'] . ' ms', 'class' => 'text-blue-700'],
    ] as $metric): ?>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold"><?= esc($metric['label']) ?></p>
            <p class="text-3xl font-black mt-2 <?= esc($metric['class']) ?>"><?= esc($metric['value']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<form method="get" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-8">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
        <select name="status" class="rounded-xl border border-slate-300 px-4 py-3 bg-white">
            <option value="">Todos los estados</option>
            <?php foreach (['RECEIVED', 'PROCESSING', 'PROCESSED', 'FAILED', 'REPLAYED'] as $status): ?>
                <option value="<?= $status ?>" <?= strtoupper($filters['status']) === $status ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
        </select>
        <input name="source" value="<?= esc($filters['source']) ?>" placeholder="Fuente" class="rounded-xl border border-slate-300 px-4 py-3">
        <input name="event_type" value="<?= esc($filters['event_type']) ?>" placeholder="Tipo de evento" class="rounded-xl border border-slate-300 px-4 py-3">
        <input name="correlation_id" value="<?= esc($filters['correlation_id']) ?>" placeholder="Correlation ID" class="rounded-xl border border-slate-300 px-4 py-3">
        <input name="external_event_id" value="<?= esc($filters['external_event_id']) ?>" placeholder="External Event ID" class="rounded-xl border border-slate-300 px-4 py-3">
        <div class="flex gap-2">
            <select name="limit" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-3 bg-white">
                <?php foreach ([50, 100, 200, 250] as $option): ?>
                    <option value="<?= $option ?>" <?= $limit === $option ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
            <button class="rounded-xl bg-slate-950 text-white font-bold px-5">Filtrar</button>
        </div>
    </div>
</form>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-left text-xs uppercase tracking-widest text-slate-500">
                <tr>
                    <th class="px-5 py-4">Evento</th>
                    <th class="px-5 py-4">Origen</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="px-5 py-4">Correlación</th>
                    <th class="px-5 py-4">Tiempo</th>
                    <th class="px-5 py-4">Recibido</th>
                    <th class="px-5 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($events as $event): ?>
                    <?php
                    $statusClass = match ($event['status']) {
                        'PROCESSED' => 'bg-emerald-50 text-emerald-700',
                        'FAILED' => 'bg-red-50 text-red-700',
                        'PROCESSING' => 'bg-blue-50 text-blue-700',
                        default => 'bg-slate-100 text-slate-700',
                    };
                    ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            <p class="font-black text-slate-900">#<?= esc($event['id']) ?> · <?= esc($event['event_type']) ?></p>
                            <p class="text-xs text-slate-400 mt-1"><?= esc($event['external_event_id'] ?: 'Sin external ID') ?></p>
                            <?php if ((int) $event['replay_attempt'] > 0): ?>
                                <span class="inline-flex mt-2 px-2 py-1 rounded-full bg-violet-50 text-violet-700 text-[11px] font-bold">Replay #<?= esc($event['replay_attempt']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 font-bold text-slate-700"><?= esc($event['source']) ?></td>
                        <td class="px-5 py-4"><span class="px-3 py-1 rounded-full text-xs font-bold <?= $statusClass ?>"><?= esc($event['status']) ?></span></td>
                        <td class="px-5 py-4"><code class="text-xs text-slate-500"><?= esc(substr((string) $event['correlation_id'], 0, 18)) ?>…</code></td>
                        <td class="px-5 py-4"><?= esc($event['processing_time_ms'] ?? '-') ?> ms</td>
                        <td class="px-5 py-4 text-slate-500"><?= esc($event['received_at']) ?></td>
                        <td class="px-5 py-4 text-right"><a href="<?= site_url('admin/integration/events/' . $event['id']) ?>" class="font-bold text-pink-600 hover:text-pink-800">Inspeccionar →</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($events)): ?>
                    <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">No se encontraron eventos con los filtros seleccionados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
