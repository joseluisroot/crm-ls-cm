<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-900">Publication Center</h1>
        <p class="text-slate-500 mt-2">Publicaciones, comentarios, reacciones y movimiento operativo.</p>
    </div>

    <form method="get" class="flex items-center gap-3">
        <select name="limit" class="rounded-xl border border-slate-300 px-4 py-3 bg-white">
            <?php foreach ([25, 50, 100, 200] as $option): ?>
                <option value="<?= $option ?>" <?= $limit === $option ? 'selected' : '' ?>><?= $option ?> publicaciones</option>
            <?php endforeach; ?>
        </select>
        <button class="px-5 py-3 rounded-xl bg-slate-950 text-white font-bold">Aplicar</button>
    </form>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <?php foreach ($publications as $publication): ?>
        <article class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Publicación #<?= esc($publication['id']) ?></p>
                    <p class="text-xs text-slate-400 mt-2"><?= esc($publication['external_post_id'] ?? '-') ?></p>
                </div>
                <?php if (! empty($publication['permalink_url'])): ?>
                    <a href="<?= esc($publication['permalink_url']) ?>" target="_blank" rel="noopener" class="text-sm font-bold text-pink-600">Facebook ↗</a>
                <?php endif; ?>
            </div>

            <p class="text-slate-700 mt-5 line-clamp-4 whitespace-pre-line"><?= esc($publication['message'] ?: 'Publicación sin texto disponible.') ?></p>

            <div class="grid grid-cols-2 gap-3 mt-6">
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400">Comentarios</p>
                    <p class="text-2xl font-black text-slate-900 mt-1"><?= esc($publication['comments_count'] ?? 0) ?></p>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400">Reacciones</p>
                    <p class="text-2xl font-black text-slate-900 mt-1"><?= esc($publication['reactions_count'] ?? 0) ?></p>
                </div>
            </div>

            <a href="<?= site_url('admin/publications/' . $publication['id']) ?>" class="inline-flex mt-6 px-5 py-3 rounded-xl bg-pink-600 text-white font-bold">Abrir perfil de publicación</a>
        </article>
    <?php endforeach; ?>

    <?php if (empty($publications)): ?>
        <div class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl p-8 text-slate-500 text-center">
            Todavía no existen publicaciones registradas.
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
