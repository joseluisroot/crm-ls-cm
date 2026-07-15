<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Operational Work Queues</p>
        <h1 class="text-3xl font-black text-slate-900 mt-2"><?= esc($title) ?></h1>
        <p class="text-slate-500 mt-2">Lo pendiente permanece visible; lo completado sale de la bandeja activa sin perder trazabilidad.</p>
    </div>
    <?php if (can('operations.view')): ?>
        <form method="post" action="<?= site_url('admin/operations/import-facebook-comments') ?>" data-confirm="¿Sincronizar comentarios de Facebook?" data-confirm-text="Se importarán los comentarios pendientes a Citizen Operations." data-loading="Sincronizando comentarios...">
            <?= csrf_field() ?>
            <button class="px-5 py-3 rounded-xl bg-slate-950 text-white font-bold hover:bg-pink-600 transition">Sincronizar comentarios</button>
        </form>
    <?php endif; ?>
</div>

<?php
$queueCounts = ['PENDING' => $summary['pending'], 'ACTIVE' => $summary['active'], 'WAITING' => $summary['waiting'], 'COMPLETED' => $summary['completed'], 'CANCELLED' => $summary['cancelled']];
$toneClasses = ['red' => 'border-red-200 bg-red-50 text-red-800', 'blue' => 'border-blue-200 bg-blue-50 text-blue-800', 'amber' => 'border-amber-200 bg-amber-50 text-amber-800', 'green' => 'border-green-200 bg-green-50 text-green-800', 'slate' => 'border-slate-200 bg-slate-50 text-slate-700'];
?>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-8">
    <?php foreach ($queues as $code => $definition): ?>
        <?php $active = $queue === $code; ?>
        <a href="<?= site_url('admin/operations?' . http_build_query(['queue' => $code])) ?>" class="rounded-2xl border p-5 transition <?= $active ? 'ring-2 ring-pink-500 shadow-md ' : 'hover:shadow-sm ' ?><?= $toneClasses[$definition['tone']] ?? $toneClasses['slate'] ?>">
            <div class="flex items-start justify-between gap-4">
                <div><p class="font-black"><?= esc($definition['label']) ?></p><p class="text-xs opacity-75 mt-1"><?= esc($definition['description']) ?></p></div>
                <span class="text-3xl font-black"><?= esc($queueCounts[$code] ?? 0) ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200">
        <div class="flex flex-col 2xl:flex-row 2xl:items-end 2xl:justify-between gap-5">
            <div><h2 class="text-xl font-black text-slate-900"><?= esc($queues[$queue]['label'] ?? 'Cola operacional') ?></h2><p class="text-sm text-slate-500 mt-1"><?= esc($queues[$queue]['description'] ?? '') ?></p></div>
            <form method="get" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3" data-loading="Aplicando filtros...">
                <input type="hidden" name="queue" value="<?= esc($queue) ?>">
                <input type="search" name="q" value="<?= esc($search) ?>" placeholder="Buscar ciudadano, mensaje..." class="sm:col-span-2 rounded-xl border border-slate-300 px-4 py-2.5 bg-white focus:border-pink-500 focus:ring-pink-500">
                <select name="status" class="rounded-xl border border-slate-300 px-4 py-2.5 bg-white"><option value="">Todos los estados</option><?php foreach ($statuses as $option): ?><option value="<?= esc($option['code']) ?>" <?= $status === $option['code'] ? 'selected' : '' ?>><?= esc($option['name']) ?></option><?php endforeach; ?></select>
                <select name="priority" class="rounded-xl border border-slate-300 px-4 py-2.5 bg-white"><option value="">Todas las prioridades</option><?php foreach ($priorities as $option): ?><option value="<?= esc($option['code']) ?>" <?= $priority === $option['code'] ? 'selected' : '' ?>><?= esc($option['name']) ?></option><?php endforeach; ?></select>
                <div class="flex gap-2"><select name="per_page" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 bg-white"><?php foreach ([10,25,50,100] as $size): ?><option value="<?= $size ?>" <?= $perPage === $size ? 'selected' : '' ?>><?= $size ?> por página</option><?php endforeach; ?></select><button class="px-4 py-2.5 rounded-xl bg-pink-600 text-white font-bold hover:bg-pink-700">Filtrar</button></div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50"><tr><th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Atención</th><th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Estado</th><th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Responsable</th><th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Fecha</th><th class="px-6 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Acción</th></tr></thead>
            <tbody class="divide-y divide-slate-100 bg-white">
            <?php foreach ($items as $item): ?>
                <?php $priorityTone = match ($item['priority']) {'CRITICAL' => 'bg-red-100 text-red-800','HIGH' => 'bg-orange-100 text-orange-800','LOW' => 'bg-slate-100 text-slate-600',default => 'bg-blue-50 text-blue-700'}; ?>
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-6 py-5"><div class="flex flex-wrap gap-2 mb-2"><span class="px-2.5 py-1 rounded-full text-xs font-black bg-pink-50 text-pink-700"><?= esc($item['channel']) ?></span><span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $priorityTone ?>"><?= esc($item['priority_name']) ?></span></div><p class="font-black text-slate-900"><?= esc($item['author_name'] ?: $item['title']) ?></p><p class="text-sm text-slate-500 mt-1 max-w-2xl line-clamp-2"><?= esc($item['comment_message'] ?: $item['summary']) ?></p><p class="text-xs text-slate-400 mt-2">Work Item #<?= esc($item['id']) ?></p></td>
                    <td class="px-6 py-5"><span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700"><?= esc($item['status_name']) ?></span></td>
                    <td class="px-6 py-5 text-sm font-semibold text-slate-700"><?= esc($item['assigned_user_name'] ?: 'Sin asignar') ?></td>
                    <td class="px-6 py-5 text-sm text-slate-500 whitespace-nowrap"><?= esc($item['commented_at'] ?: $item['opened_at'] ?: $item['created_at']) ?></td>
                    <td class="px-6 py-5 text-right"><a href="<?= site_url('admin/operations/' . $item['id']) ?>" class="inline-flex px-4 py-2 rounded-xl bg-slate-950 text-white text-sm font-bold hover:bg-pink-600 transition">Abrir</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?><tr><td colspan="5" class="p-12 text-center"><p class="text-lg font-bold text-slate-700">No hay atenciones en esta bandeja.</p><p class="text-slate-500 mt-2">Prueba modificando la búsqueda o los filtros.</p></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?= view('Modules\Shared\Views\components\pagination', ['pagination' => $pagination]) ?>
</section>

<?= $this->endSection() ?>
