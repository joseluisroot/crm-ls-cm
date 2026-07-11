<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$status = (string) ($execution['status'] ?? 'unknown');
$statusClass = match ($status) {
    'completed' => 'bg-green-100 text-green-700',
    'failed' => 'bg-red-100 text-red-700',
    'running' => 'bg-blue-100 text-blue-700',
    default => 'bg-slate-100 text-slate-600',
};
$summary = $execution['summary'] ?? [];
?>

<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6 mb-8">
    <div>
        <a href="<?= site_url('admin/workflows/runtime') ?>" class="text-sm font-bold text-pink-600">← Volver al inspector</a>
        <h1 class="text-3xl font-black text-slate-900 mt-3">Ejecución #<?= esc($execution['id']) ?></h1>
        <p class="text-slate-500 mt-2">
            <?= esc($execution['workflow_name'] ?? 'Workflow') ?> · versión <?= esc($execution['version_number'] ?? '-') ?>
        </p>
    </div>

    <span class="px-4 py-2 rounded-full text-sm font-black <?= esc($statusClass) ?>">
        <?= esc(strtoupper($status)) ?>
    </span>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5 mb-8">
    <?php
    $cards = [
        'Nodos' => $summary['total_nodes'] ?? 0,
        'Completados' => $summary['completed_nodes'] ?? 0,
        'Fallidos' => $summary['failed_nodes'] ?? 0,
        'Duración total' => ($summary['total_duration_ms'] ?? 0) . ' ms',
        'Nodo más lento' => ($summary['slowest_node_ms'] ?? 0) . ' ms',
    ];
    ?>
    <?php foreach ($cards as $label => $value): ?>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold"><?= esc($label) ?></p>
            <p class="text-2xl font-black text-slate-900 mt-3"><?= esc($value) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 2xl:grid-cols-3 gap-8">
    <section class="2xl:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-black text-slate-900">Timeline de ejecución</h2>
                <p class="text-sm text-slate-500 mt-1">Secuencia registrada por el Runtime Inspector.</p>
            </div>
            <span class="text-xs font-bold text-slate-400"><?= count($execution['timeline'] ?? []) ?> nodos</span>
        </div>

        <div class="space-y-5">
            <?php foreach (($execution['timeline'] ?? []) as $index => $node): ?>
                <?php
                $nodeStatus = (string) ($node['status'] ?? 'unknown');
                $dotClass = match ($nodeStatus) {
                    'completed' => 'bg-green-500',
                    'failed' => 'bg-red-500',
                    'running' => 'bg-blue-500 animate-pulse',
                    default => 'bg-slate-300',
                };
                ?>
                <div class="relative pl-10">
                    <?php if ($index < count($execution['timeline']) - 1): ?>
                        <div class="absolute left-[11px] top-7 bottom-[-22px] w-px bg-slate-200"></div>
                    <?php endif; ?>
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full <?= esc($dotClass) ?> ring-4 ring-white"></div>

                    <details class="group rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <summary class="cursor-pointer list-none flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-slate-900"><?= esc($node['node_name'] ?? $node['node_key']) ?></h3>
                                    <span class="text-xs font-bold rounded-full bg-white border border-slate-200 px-2 py-1"><?= esc($nodeStatus) ?></span>
                                    <span class="text-xs text-slate-400">Intento <?= esc($node['attempt'] ?? 1) ?></span>
                                </div>
                                <p class="text-xs font-mono text-slate-500 mt-2"><?= esc($node['node_key'] ?? '-') ?> · <?= esc($node['node_type'] ?? '-') ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-slate-900"><?= esc($node['duration_ms'] ?? 0) ?> ms</p>
                                <p class="text-xs text-slate-400 mt-1"><?= esc($node['started_at'] ?? '-') ?></p>
                            </div>
                        </summary>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mt-5 pt-5 border-t border-slate-200">
                            <?php if (!empty($node['error_message'])): ?>
                                <div class="xl:col-span-2 rounded-xl bg-red-50 border border-red-200 p-4 text-red-700">
                                    <p class="font-black"><?= esc($node['error_class'] ?? 'Error') ?></p>
                                    <p class="text-sm mt-1"><?= esc($node['error_message']) ?></p>
                                </div>
                            <?php endif; ?>

                            <div>
                                <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Variables</h4>
                                <pre class="rounded-xl bg-slate-950 text-slate-200 p-4 text-xs overflow-auto max-h-72"><?= esc(json_encode($node['variables'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Logs</h4>
                                <pre class="rounded-xl bg-slate-950 text-slate-200 p-4 text-xs overflow-auto max-h-72"><?= esc(json_encode($node['logs'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Payloads</h4>
                                <pre class="rounded-xl bg-slate-950 text-slate-200 p-4 text-xs overflow-auto max-h-72"><?= esc(json_encode($node['payloads'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Snapshots</h4>
                                <pre class="rounded-xl bg-slate-950 text-slate-200 p-4 text-xs overflow-auto max-h-72"><?= esc(json_encode($node['snapshots'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                        </div>
                    </details>
                </div>
            <?php endforeach; ?>

            <?php if (empty($execution['timeline'])): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                    <p class="font-black text-slate-900">Esta ejecución todavía no tiene nodos registrados.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <aside class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900">Contexto de ejecución</h2>
            <dl class="mt-5 space-y-4 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Workflow</dt><dd class="font-bold text-right"><?= esc($execution['workflow_slug'] ?? '-') ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Conversación</dt><dd class="font-bold">#<?= esc($execution['conversation_id'] ?? '-') ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Nodo actual</dt><dd class="font-mono text-xs text-right"><?= esc($execution['current_node_key'] ?? '-') ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Inicio</dt><dd class="font-bold text-right"><?= esc($execution['started_at'] ?? '-') ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Finalización</dt><dd class="font-bold text-right"><?= esc($execution['completed_at'] ?? '-') ?></dd></div>
            </dl>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900">Metadata</h2>
            <pre class="mt-4 rounded-xl bg-slate-950 text-slate-200 p-4 text-xs overflow-auto max-h-[32rem]"><?= esc(json_encode($execution['metadata'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>
    </aside>
</div>

<?= $this->endSection() ?>
