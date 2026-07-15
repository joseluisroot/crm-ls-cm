<?php
/** @var \Modules\Core\UI\Widgets\WidgetResult $widget */
/** @var ?\Modules\Citizen\Application\DTO\CitizenCardDTO $citizen */
?>

<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-pink-600 font-black">Citizen Widget</p>
            <h2 class="text-xl font-black text-slate-900 mt-2"><?= esc($widget->title) ?></h2>
        </div>
        <?php if ($citizen): ?>
            <span class="inline-flex px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black">Identidad vinculada</span>
        <?php endif; ?>
    </div>

    <?php if (! $citizen): ?>
        <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center">
            <p class="font-bold text-slate-700">Ciudadano no disponible</p>
            <p class="text-sm text-slate-500 mt-1">La atención todavía no tiene una identidad ciudadana vinculada.</p>
        </div>
    <?php else: ?>
        <div class="mt-5 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-slate-950 text-white flex items-center justify-center text-xl font-black">
                <?= esc(mb_strtoupper(mb_substr($citizen->name, 0, 1))) ?>
            </div>
            <div class="min-w-0">
                <p class="text-lg font-black text-slate-900 truncate"><?= esc($citizen->name) ?></p>
                <p class="text-sm text-slate-500 mt-1"><?= esc($citizen->primaryChannel ?: 'Canal no identificado') ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-6">
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">Atenciones</p>
                <p class="text-2xl font-black text-slate-900 mt-1"><?= esc($citizen->totalWorkItems) ?></p>
                <p class="text-xs text-amber-700 mt-1"><?= esc($citizen->openWorkItems) ?> abiertas</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">Casos</p>
                <p class="text-2xl font-black text-slate-900 mt-1"><?= esc($citizen->totalCases) ?></p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">Conversaciones</p>
                <p class="text-2xl font-black text-slate-900 mt-1"><?= esc($citizen->totalConversations) ?></p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400">Identidades</p>
                <p class="text-2xl font-black text-slate-900 mt-1"><?= esc($citizen->totalIdentities) ?></p>
            </div>
        </div>

        <div class="mt-5 pt-5 border-t border-slate-100">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Última actividad</p>
            <p class="text-sm font-semibold text-slate-700 mt-2"><?= esc($citizen->lastActivity ?: 'Sin actividad registrada') ?></p>
        </div>

        <a href="<?= site_url('admin/citizens/' . $citizen->citizenId) ?>"
           class="inline-flex w-full justify-center mt-5 px-4 py-3 rounded-xl bg-slate-950 text-white text-sm font-black hover:bg-pink-600 transition">
            Abrir perfil ciudadano
        </a>
    <?php endif; ?>
</section>
