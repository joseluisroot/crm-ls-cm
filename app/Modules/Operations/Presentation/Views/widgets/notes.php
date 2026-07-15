<?php
$labels = ['GENERAL' => 'General', 'CALL' => 'Llamada', 'VISIT' => 'Visita', 'FOLLOW_UP' => 'Seguimiento', 'DOCUMENT' => 'Documento'];
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Colaboración interna</p>
            <h2 class="text-xl font-black text-slate-900 mt-2">Notas internas</h2>
        </div>
        <span class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 text-xs font-black"><?= count($notes) ?></span>
    </div>

    <?php if ($canAddNote): ?>
        <form method="post" action="<?= site_url('admin/operations/' . $workItemId . '/notes') ?>" class="mt-5" data-confirm="¿Guardar esta nota interna?" data-loading="Guardando nota...">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <select name="note_type" class="rounded-xl border border-slate-300 px-4 py-3 bg-white">
                    <?php foreach ($labels as $code => $label): ?><option value="<?= esc($code) ?>"><?= esc($label) ?></option><?php endforeach; ?>
                </select>
                <textarea name="body" rows="3" maxlength="5000" required class="sm:col-span-2 rounded-xl border border-slate-300 px-4 py-3" placeholder="Agrega contexto útil para el equipo..."></textarea>
            </div>
            <div class="flex justify-end mt-3"><button class="px-4 py-2.5 rounded-xl bg-slate-950 text-white text-sm font-black">Agregar nota</button></div>
        </form>
    <?php endif; ?>

    <div class="mt-6 space-y-4">
        <?php foreach ($notes as $note): ?>
            <article class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div><strong class="text-slate-900"><?= esc($note['author_name'] ?? 'Usuario') ?></strong><span class="ml-2 text-xs font-black text-pink-600"><?= esc($labels[$note['note_type']] ?? $note['note_type']) ?></span></div>
                    <span class="text-xs text-slate-400"><?= esc($note['created_at']) ?></span>
                </div>
                <p class="text-sm text-slate-700 mt-3 whitespace-pre-line"><?= esc($note['body']) ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (empty($notes)): ?><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">Todavía no hay notas internas para esta atención.</div><?php endif; ?>
    </div>
</section>
