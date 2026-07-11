<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Public Engagement Engine</p>
        <h1 class="text-3xl font-black text-slate-900 mt-2">Centro de interacciones públicas</h1>
        <p class="text-slate-500 mt-2">Comentarios, reacciones y participación ciudadana en publicaciones.</p>
    </div>

    <a href="<?= site_url('admin/engagement/participants') ?>"
       class="px-5 py-3 rounded-xl bg-slate-950 text-white font-bold">
        Ver participación ciudadana
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    <?php foreach ([
        ['label' => 'Publicaciones', 'value' => $summary['posts'], 'tone' => 'text-blue-700 bg-blue-50'],
        ['label' => 'Comentarios', 'value' => $summary['comments'], 'tone' => 'text-violet-700 bg-violet-50'],
        ['label' => 'Pendientes', 'value' => $summary['pending_comments'], 'tone' => 'text-amber-700 bg-amber-50'],
        ['label' => 'Reacciones activas', 'value' => $summary['active_reactions'], 'tone' => 'text-pink-700 bg-pink-50'],
    ] as $card): ?>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold <?= $card['tone'] ?>">
                <?= esc($card['label']) ?>
            </span>
            <p class="text-4xl font-black text-slate-900 mt-4"><?= esc($card['value']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
    <section class="2xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900">Bandeja de comentarios</h2>
                <p class="text-sm text-slate-500 mt-1">Interacciones públicas que pueden requerir atención.</p>
            </div>

            <form method="get" class="flex flex-wrap gap-3">
                <select name="status" class="rounded-xl border border-slate-300 px-4 py-2 bg-white">
                    <option value="">Todos los estados</option>
                    <?php foreach (['new', 'responded', 'closed', 'removed'] as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                            <?= esc(ucfirst($option)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="limit" min="1" max="200" value="<?= esc($limit) ?>"
                       class="w-24 rounded-xl border border-slate-300 px-4 py-2">
                <button class="px-4 py-2 rounded-xl bg-pink-600 text-white font-bold">Filtrar</button>
            </form>
        </div>

        <div class="divide-y divide-slate-100">
            <?php foreach ($comments as $comment): ?>
                <article class="p-6 hover:bg-slate-50 transition">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-black text-slate-900"><?= esc($comment['author_name'] ?: 'Usuario de Facebook') ?></p>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                    <?= esc($comment['status']) ?>
                                </span>
                                <?php if ((int) $comment['requires_response'] === 1): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">Requiere atención</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-slate-700 mt-3 whitespace-pre-line"><?= esc($comment['message'] ?: 'Comentario sin texto disponible.') ?></p>
                            <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-4">
                                <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Publicación</p>
                                <p class="text-sm text-slate-600 mt-1 line-clamp-2"><?= esc($comment['post_message'] ?: $comment['external_post_id']) ?></p>
                            </div>
                        </div>
                        <div class="text-sm text-slate-400 shrink-0">
                            <?= esc($comment['commented_at'] ?: $comment['created_at']) ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (empty($comments)): ?>
                <div class="p-10 text-center text-slate-500">Todavía no existen comentarios registrados.</div>
            <?php endif; ?>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Reacciones recientes</h2>
            <div class="mt-5 space-y-4">
                <?php foreach ($reactions as $reaction): ?>
                    <div class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-bold text-slate-800"><?= esc($reaction['actor_name'] ?: 'Usuario de Facebook') ?></p>
                            <span class="px-3 py-1 rounded-full bg-pink-50 text-pink-700 text-xs font-black">
                                <?= esc($reaction['reaction_type']) ?>
                            </span>
                        </div>
                        <p class="text-sm text-slate-500 mt-2 line-clamp-2"><?= esc($reaction['post_message'] ?: $reaction['external_post_id']) ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($reactions)): ?>
                    <p class="text-slate-500">Todavía no existen reacciones registradas.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Personas más activas</h2>
            <div class="mt-5 space-y-4">
                <?php foreach ($participants as $index => $person): ?>
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 rounded-full bg-slate-950 text-white flex items-center justify-center font-black">
                            <?= $index + 1 ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-800 truncate"><?= esc($person['name']) ?></p>
                            <p class="text-xs text-slate-500">
                                <?= esc($person['comments_count']) ?> comentarios · <?= esc($person['reactions_count']) ?> reacciones
                            </p>
                        </div>
                        <span class="font-black text-pink-600"><?= esc($person['total_interactions']) ?></span>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($participants)): ?>
                    <p class="text-slate-500">Sin datos de participación todavía.</p>
                <?php endif; ?>
            </div>
        </section>
    </aside>
</div>

<?= $this->endSection() ?>
