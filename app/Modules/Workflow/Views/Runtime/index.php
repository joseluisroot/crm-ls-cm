<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$statusClasses = [
    'running' => 'bg-blue-100 text-blue-700',
    'completed' => 'bg-green-100 text-green-700',
    'failed' => 'bg-red-100 text-red-700',
    'cancelled' => 'bg-slate-200 text-slate-600',
];
?>

<div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-6 mb-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Workflow Observability</p>
        <h1 class="text-3xl font-black text-slate-900 mt-2">Runtime Inspector</h1>
        <p class="text-slate-500 mt-2">Monitorea ejecuciones, tiempos, errores y trazabilidad de cada workflow.</p>
    </div>

    <form method="get" class="flex flex-wrap gap-3 bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <select name="status" class="rounded-xl border border-slate-300 px-4 py-3 bg-white text-sm font-semibold">
            <option value="">Todos los estados</option>
            <?php foreach (['running', 'completed', 'failed', 'cancelled'] as $status): ?>
                <option value="<?= esc($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                    <?= esc(ucfirst($status)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input
            type="number"
            min="1"
            max="200"
            name="limit"
            value="<?= esc($filters['limit'] ?? 50) ?>"
            class="w-28 rounded-xl border border-slate-300 px-4 py-3 text-sm"
            aria-label="Límite de resultados"
        >

        <button class="rounded-xl bg-slate-950 px-5 py-3 text-white font-bold text-sm">Aplicar</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    <?php foreach ($kpis as $label => $value): ?>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold"><?= esc($label) ?></p>
            <p class="text-3xl font-black text-slate-900 mt-3"><?= esc($value) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900">Ejecuciones recientes</h2>
            <p class="text-sm text-slate-500 mt-1">Resultados ordenados por fecha de inicio.</p>
        </div>
        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
            <?= count($executions) ?> registros
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
            <tr>
                <th class="px-6 py-4 text-left">ID</th>
                <th class="px-6 py-4 text-left">Workflow</th>
                <th class="px-6 py-4 text-left">Conversación</th>
                <th class="px-6 py-4 text-left">Nodo actual</th>
                <th class="px-6 py-4 text-left">Estado</th>
                <th class="px-6 py-4 text-left">Inicio</th>
                <th class="px-6 py-4 text-right">Acción</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($executions as $execution): ?>
                <?php $status = (string) ($execution['status'] ?? 'unknown'); ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 font-black text-slate-900">#<?= esc($execution['id']) ?></td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-900"><?= esc($execution['workflow_name'] ?? 'Workflow') ?></p>
                        <p class="text-xs text-slate-400 mt-1">v<?= esc($execution['version_number'] ?? '-') ?></p>
                    </td>
                    <td class="px-6 py-4 text-slate-600">#<?= esc($execution['conversation_id'] ?? '-') ?></td>
                    <td class="px-6 py-4 font-mono text-xs text-slate-600"><?= esc($execution['current_node_key'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= esc($statusClasses[$status] ?? 'bg-slate-100 text-slate-600') ?>">
                            <?= esc($status) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-600"><?= esc($execution['started_at'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-right">
                        <a href="<?= site_url('admin/workflows/runtime/' . $execution['id']) ?>" class="inline-flex px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs">
                            Inspeccionar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($executions)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-14 text-center">
                        <p class="font-black text-slate-900">No hay ejecuciones para mostrar</p>
                        <p class="text-slate-500 mt-2">Ejecuta un workflow desde Messenger o desde el simulador.</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
