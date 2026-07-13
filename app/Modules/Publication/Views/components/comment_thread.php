<?php
/** @var array $comment */
$depth = (int) ($comment['depth'] ?? 0);
$margin = min($depth, 5) * 20;
?>

<article class="rounded-2xl border <?= $comment['is_orphan'] ? 'border-amber-300 bg-amber-50/40' : 'border-slate-200 bg-white' ?> p-5 shadow-sm" style="margin-left: <?= $margin ?>px">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <p class="font-black text-slate-800"><?= esc($comment['author_name'] ?: 'Usuario de Facebook') ?></p>
                <?php if ($depth > 0): ?>
                    <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold">Respuesta nivel <?= esc($depth) ?></span>
                <?php endif; ?>
                <?php if ($comment['is_orphan']): ?>
                    <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">Padre no disponible</span>
                <?php endif; ?>
            </div>
            <p class="text-xs text-slate-400 mt-1"><?= esc($comment['commented_at'] ?? '-') ?></p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold"><?= esc($comment['status'] ?? 'received') ?></span>
            <?php if (! empty($comment['work_item_id'])): ?>
                <a href="<?= site_url('admin/operations/' . $comment['work_item_id']) ?>" class="px-3 py-1 rounded-full bg-pink-50 text-pink-700 text-xs font-bold">Work Item #<?= esc($comment['work_item_id']) ?></a>
            <?php endif; ?>
        </div>
    </div>

    <p class="text-slate-700 mt-4 whitespace-pre-line"><?= esc($comment['message'] ?: 'Comentario sin texto disponible.') ?></p>

    <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
        <span><?= esc($comment['reply_count']) ?> respuestas directas</span>
        <span><?= esc($comment['descendant_count']) ?> respuestas totales</span>
        <span>ID: <?= esc($comment['external_comment_id'] ?? $comment['id']) ?></span>
    </div>

    <?php if (! empty($comment['children'])): ?>
        <div class="mt-4 space-y-4 border-l-2 border-slate-200 pl-3">
            <?php foreach ($comment['children'] as $child): ?>
                <?= view('Modules\Publication\Views\components\comment_thread', ['comment' => $child]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
