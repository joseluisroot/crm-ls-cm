<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Citizen Intelligence</p>
        <h1 class="text-3xl font-black text-slate-900 mt-2">Participación ciudadana</h1>
        <p class="text-slate-500 mt-2">Personas con mayor actividad pública registrada en comentarios y reacciones.</p>
    </div>
    <a href="<?= site_url('admin/engagement') ?>" class="px-5 py-3 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold">Volver al Engagement Center</a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col gap-5">
            <div>
                <h2 class="text-xl font-black text-slate-900">Ranking de participación</h2>
                <p class="text-sm text-slate-500 mt-1">El ranking refleja actividad, no afinidad política ni intención de apoyo.</p>
            </div>
            <form method="get" class="flex flex-col md:flex-row gap-3">
                <input type="search" name="q" value="<?= esc($search) ?>" placeholder="Buscar persona o identificador..." class="flex-1 rounded-xl border border-slate-300 px-4 py-3 bg-white">
                <select name="per_page" class="rounded-xl border border-slate-300 px-4 py-3 bg-white">
                    <?php foreach ([10, 25, 50, 100] as $option): ?>
                        <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?> por página</option>
                    <?php endforeach; ?>
                </select>
                <button class="px-5 py-3 rounded-xl bg-pink-600 text-white font-bold">Buscar</button>
                <?php if ($search !== ''): ?><a href="<?= site_url('admin/engagement/participants') ?>" class="px-5 py-3 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold text-center">Limpiar</a><?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500">
                    <tr><th class="px-6 py-4">Posición</th><th class="px-6 py-4">Persona</th><th class="px-6 py-4">Comentarios</th><th class="px-6 py-4">Reacciones</th><th class="px-6 py-4">Total</th><th class="px-6 py-4">Última interacción</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($participants as $index => $person): ?>
                        <?php $position = (($pagination['page'] - 1) * $pagination['perPage']) + $index + 1; ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-black text-slate-400">#<?= esc($position) ?></td>
                            <td class="px-6 py-4"><p class="font-black text-slate-900"><?= esc($person['name']) ?></p><p class="text-xs text-slate-400 mt-1"><?= esc($person['external_id']) ?></p></td>
                            <td class="px-6 py-4 text-slate-700"><?= esc($person['comments_count']) ?></td>
                            <td class="px-6 py-4 text-slate-700"><?= esc($person['reactions_count']) ?></td>
                            <td class="px-6 py-4"><span class="inline-flex px-3 py-1 rounded-full bg-pink-50 text-pink-700 font-black"><?= esc($person['total_interactions']) ?></span></td>
                            <td class="px-6 py-4 text-sm text-slate-500 whitespace-nowrap"><?= esc($person['last_interaction_at'] ?: 'Sin fecha') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($participants)): ?><tr><td colspan="6" class="px-6 py-14 text-center text-slate-500">No se encontraron participantes con los filtros seleccionados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= view('Modules\Shared\Views\components\pagination', ['pagination' => $pagination]) ?>
    </section>

    <aside class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm h-fit">
        <h2 class="text-xl font-black text-slate-900">Distribución de reacciones</h2>
        <div class="mt-5 space-y-4">
            <?php foreach ($reactionBreakdown as $reaction): ?>
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4 last:border-0 last:pb-0"><span class="font-bold text-slate-700"><?= esc($reaction['reaction_type']) ?></span><span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-black"><?= esc($reaction['total']) ?></span></div>
            <?php endforeach; ?>
            <?php if (empty($reactionBreakdown)): ?><p class="text-slate-500">Sin reacciones registradas.</p><?php endif; ?>
        </div>
    </aside>
</div>

<div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
    <p class="font-black">Criterio de uso responsable</p>
    <p class="text-sm mt-2">La actividad pública sirve para priorizar atención y comprender temas de interés. No debe interpretarse automáticamente como apoyo, oposición o afiliación política.</p>
</div>

<?= $this->endSection() ?>
