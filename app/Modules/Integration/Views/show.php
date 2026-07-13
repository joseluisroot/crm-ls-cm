<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$statusClass = match ($event['status']) {
    'PROCESSED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'FAILED' => 'bg-red-50 text-red-700 border-red-200',
    'PROCESSING' => 'bg-blue-50 text-blue-700 border-blue-200',
    default => 'bg-slate-100 text-slate-700 border-slate-200',
};
?>

<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <a href="<?= site_url('admin/integration/events') ?>" class="text-sm font-bold text-pink-600">← Volver al Replay Center</a>
        <h1 class="text-3xl font-black text-slate-900 mt-3">Integration Event #<?= esc($event['id']) ?></h1>
        <p class="text-slate-500 mt-2">Evidencia, trazabilidad, linaje y diagnóstico del procesamiento.</p>
    </div>
    <form method="post" action="<?= site_url('admin/integration/events/' . $event['id'] . '/replay') ?>" onsubmit="return confirm('¿Deseas reproducir este evento? Se creará una nueva ejecución auditable.');">
        <?= csrf_field() ?>
        <button class="px-5 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold shadow-sm">Reproducir evento</button>
    </form>
</div>

<?php foreach (['integration_replay_success' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'integration_replay_error' => 'bg-red-50 border-red-200 text-red-800'] as $key => $class): ?>
    <?php if (session()->getFlashdata($key)): ?>
        <div class="mb-6 rounded-xl border p-4 <?= $class ?>"><?= esc(session()->getFlashdata($key)) ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Estado</p>
        <span class="inline-flex mt-3 px-3 py-1 rounded-full border text-sm font-bold <?= $statusClass ?>"><?= esc($event['status']) ?></span>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Fuente / Tipo</p>
        <p class="font-black text-slate-900 mt-2"><?= esc($event['source']) ?></p>
        <p class="text-sm text-slate-500 mt-1"><?= esc($event['event_type']) ?> · v<?= esc($event['event_version']) ?></p>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Procesamiento</p>
        <p class="text-2xl font-black text-blue-700 mt-2"><?= esc($event['processing_time_ms'] ?? '-') ?> ms</p>
        <p class="text-xs text-slate-400 mt-1"><?= esc($event['processed_at'] ?: 'Pendiente') ?></p>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Linaje</p>
        <p class="font-black text-violet-700 mt-2"><?= (int) $event['replay_attempt'] > 0 ? 'Replay #' . esc($event['replay_attempt']) : 'Evento original' ?></p>
        <p class="text-xs text-slate-400 mt-1">Raíz #<?= esc($event['original_event_id'] ?: $event['id']) ?></p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-xl font-black text-slate-900">Identificadores y evidencia</h2>
        <dl class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
            <?php foreach ([
                'UUID' => $event['uuid'],
                'Correlation ID' => $event['correlation_id'],
                'External Event ID' => $event['external_event_id'] ?: '-',
                'Endpoint' => $event['endpoint'] ?: '-',
                'Request IP' => $event['request_ip'] ?: '-',
                'Recibido' => $event['received_at'],
                'Reproducido' => $event['replayed_at'] ?: '-',
                'Firma Meta' => $event['signature'] ?: '-',
            ] as $label => $value): ?>
                <div>
                    <dt class="text-xs uppercase tracking-widest text-slate-400 font-bold"><?= esc($label) ?></dt>
                    <dd class="mt-2 break-all text-slate-700 font-medium"><?= esc($value) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
        <?php if (! empty($event['error_message'])): ?>
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="font-bold text-red-800">Error de procesamiento</p>
                <p class="text-sm text-red-700 mt-2 whitespace-pre-line"><?= esc($event['error_message']) ?></p>
            </div>
        <?php endif; ?>
    </section>

    <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-xl font-black text-slate-900">Processing Trace</h2>
        <div class="mt-5 space-y-4">
            <?php foreach ($event['processing_trace'] as $index => $step): ?>
                <div class="relative pl-7">
                    <span class="absolute left-0 top-1 w-3 h-3 rounded-full bg-pink-600"></span>
                    <?php if ($index < count($event['processing_trace']) - 1): ?><span class="absolute left-[5px] top-4 w-px h-full bg-slate-200"></span><?php endif; ?>
                    <p class="font-black text-slate-800"><?= esc($step['step'] ?? 'step') ?></p>
                    <p class="text-xs text-slate-400 mt-1"><?= esc($step['at'] ?? '') ?></p>
                    <?php if (isset($step['reason'])): ?><p class="text-sm text-slate-600 mt-1"><?= esc($step['reason']) ?></p><?php endif; ?>
                    <?php if (isset($step['message'])): ?><p class="text-sm text-red-600 mt-1"><?= esc($step['message']) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (empty($event['processing_trace'])): ?><p class="text-slate-500">No existe trace registrado.</p><?php endif; ?>
        </div>
    </section>
</div>

<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
    <h2 class="text-xl font-black text-slate-900">Linaje de ejecuciones</h2>
    <div class="mt-5 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-widest text-slate-500">
                <tr><th class="px-4 py-3">Evento</th><th class="px-4 py-3">Intento</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Correlación</th><th class="px-4 py-3">Tiempo</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($lineage as $item): ?>
                    <tr class="<?= (int) $item['id'] === (int) $event['id'] ? 'bg-pink-50/50' : '' ?>">
                        <td class="px-4 py-3 font-black">#<?= esc($item['id']) ?></td>
                        <td class="px-4 py-3"><?= (int) $item['replay_attempt'] > 0 ? 'Replay #' . esc($item['replay_attempt']) : 'Original' ?></td>
                        <td class="px-4 py-3 font-bold"><?= esc($item['status']) ?></td>
                        <td class="px-4 py-3"><code class="text-xs"><?= esc(substr((string) $item['correlation_id'], 0, 18)) ?>…</code></td>
                        <td class="px-4 py-3"><?= esc($item['processing_time_ms'] ?? '-') ?> ms</td>
                        <td class="px-4 py-3 text-right"><a href="<?= site_url('admin/integration/events/' . $item['id']) ?>" class="font-bold text-pink-600">Abrir</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <?php foreach (['Payload original' => $event['payload'], 'Headers originales' => $event['headers']] as $label => $data): ?>
        <section class="bg-slate-950 text-slate-100 rounded-2xl p-6 shadow-sm overflow-hidden">
            <h2 class="text-lg font-black"><?= esc($label) ?></h2>
            <pre class="mt-4 text-xs leading-6 overflow-auto max-h-[38rem] whitespace-pre-wrap break-words"><?= esc(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
        </section>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
