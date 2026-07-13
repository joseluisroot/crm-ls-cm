<?php
/** @var \Modules\Citizen\Application\DTO\CitizenCardDTO $citizenCard */
?>
<section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Contexto ciudadano</p>
            <h2 class="text-xl font-black text-slate-900 mt-2"><?= esc($citizenCard->name) ?></h2>
            <p class="text-sm text-slate-500 mt-1">
                <?= esc($citizenCard->primaryChannel ?? 'Sin canal principal') ?>
                · <?= esc($citizenCard->totalIdentities) ?> identidad(es)
            </p>
        </div>
        <a href="<?= site_url('admin/citizens/' . $citizenCard->citizenId) ?>" class="text-sm font-black text-pink-600 whitespace-nowrap">
            Ver perfil →
        </a>
    </div>

    <div class="grid grid-cols-2 gap-3 mt-5">
        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
            <p class="text-2xl font-black text-slate-900"><?= esc($citizenCard->totalWorkItems) ?></p>
            <p class="text-xs text-slate-500 mt-1">Work Items</p>
        </div>
        <div class="rounded-xl bg-amber-50 border border-amber-100 p-4">
            <p class="text-2xl font-black text-amber-700"><?= esc($citizenCard->openWorkItems) ?></p>
            <p class="text-xs text-amber-700 mt-1">Pendientes</p>
        </div>
        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
            <p class="text-2xl font-black text-blue-700"><?= esc($citizenCard->totalConversations) ?></p>
            <p class="text-xs text-blue-700 mt-1">Conversaciones</p>
        </div>
        <div class="rounded-xl bg-green-50 border border-green-100 p-4">
            <p class="text-2xl font-black text-green-700"><?= esc($citizenCard->totalCases) ?></p>
            <p class="text-xs text-green-700 mt-1">Casos</p>
        </div>
    </div>

    <div class="mt-5 pt-4 border-t border-slate-100 text-sm">
        <span class="text-slate-400">Última actividad</span>
        <p class="font-bold text-slate-800 mt-1"><?= esc($citizenCard->lastActivity ?? 'Sin actividad registrada') ?></p>
    </div>
</section>
