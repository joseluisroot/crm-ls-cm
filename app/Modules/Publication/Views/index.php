<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Publication Intelligence</p>
        <h1 class="text-3xl font-black text-slate-900 mt-2">Publication Center</h1>
        <p class="text-slate-500 mt-2">Publicaciones, comentarios, reacciones y movimiento operativo.</p>
    </div>

    <form method="get" class="flex flex-col sm:flex-row gap-3 sm:items-center">
        <input type="search" name="q" value="<?= esc($search) ?>" placeholder="Buscar publicación..."
               class="min-w-64 rounded-xl border border-slate-300 px-4 py-3 bg-white">
        <select name="per_page" class="rounded-xl border border-slate-300 px-4 py-3 bg-white">
            <?php foreach ([10, 25, 50, 100] as $option): ?>
                <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?> por página</option>
            <?php endforeach; ?>
        </select>
        <button class="px-5 py-3 rounded-xl bg-slate-950 text-white font-bold hover:bg-pink-600 transition">Buscar</button>
        <?php if ($search !== ''): ?>
            <a href="<?= site_url('admin/publications') ?>" class="px-5 py-3 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold">Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500">
                <tr>
                    <th class="px-6 py-4">Publicación</th>
                    <th class="px-6 py-4">Comentarios</th>
                    <th class="px-6 py-4">Reacciones</th>
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($publications as $publication): ?>
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-5 min-w-[24rem]">
                            <p class="font-black text-slate-900">Publicación #<?= esc($publication['id']) ?></p>
                            <p class="text-xs text-slate-400 mt-1"><?= esc($publication['external_post_id'] ?? '-') ?></p>
                            <p class="text-sm text-slate-600 mt-3 line-clamp-2 whitespace-pre-line"><?= esc($publication['message'] ?: 'Publicación sin texto disponible.') ?></p>
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex min-w-12 justify-center px-3 py-1 rounded-full bg-violet-50 text-violet-700 font-black"><?= esc($publication['comments_count'] ?? 0) ?></span>
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex min-w-12 justify-center px-3 py-1 rounded-full bg-pink-50 text-pink-700 font-black"><?= esc($publication['reactions_count'] ?? 0) ?></span>
                        </td>
                        <td class="px-6 py-5 text-sm text-slate-500 whitespace-nowrap"><?= esc($publication['published_at'] ?? $publication['created_at'] ?? '-') ?></td>
                        <td class="px-6 py-5">
                            <div class="flex items-center justify-end gap-2">
                                <?php if (! empty($publication['permalink_url'])): ?>
                                    <a href="<?= esc($publication['permalink_url']) ?>" target="_blank" rel="noopener" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-700">Facebook ↗</a>
                                <?php endif; ?>
                                <a href="<?= site_url('admin/publications/' . $publication['id']) ?>" class="px-3 py-2 rounded-xl bg-slate-950 text-white text-sm font-bold hover:bg-pink-600 transition">Abrir</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($publications)): ?>
                    <tr><td colspan="5" class="px-6 py-14 text-center text-slate-500">No se encontraron publicaciones con los filtros seleccionados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?= view('Modules\Shared\Views\components\pagination', ['pagination' => $pagination]) ?>
</section>

<?= $this->endSection() ?>
