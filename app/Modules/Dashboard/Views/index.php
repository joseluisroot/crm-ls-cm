<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <div class="bg-white rounded-2xl shadow p-6">
            <p class="text-slate-500">Ciudadanos</p>
            <h3 class="text-4xl font-bold"><?= esc($totalCitizens) ?></h3>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <p class="text-slate-500">Conversaciones</p>
            <h3 class="text-4xl font-bold"><?= esc($totalConversations) ?></h3>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <p class="text-slate-500">Mensajes</p>
            <h3 class="text-4xl font-bold"><?= esc($totalMessages) ?></h3>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <p class="text-slate-500">Casos abiertos</p>
            <h3 class="text-4xl font-bold"><?= esc($openCases) ?></h3>
        </div>

    </div>

<?= $this->endSection() ?>