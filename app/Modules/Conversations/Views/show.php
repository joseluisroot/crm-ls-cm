<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="bg-white rounded-2xl shadow p-6 mb-6">
        <h3 class="text-xl font-bold"><?= esc($conversation['citizen_name']) ?></h3>
        <p class="text-slate-500">Canal: <?= esc($conversation['channel']) ?></p>
    </div>

    <div class="space-y-4">
        <?php foreach ($messages as $message): ?>
            <div class="<?= $message['direction'] === 'inbound' ? 'bg-white' : 'bg-blue-50' ?> rounded-2xl shadow p-5">
                <div class="flex justify-between mb-2">
                <span class="font-bold">
                    <?= $message['direction'] === 'inbound' ? 'Ciudadano' : 'Equipo' ?>
                </span>
                    <span class="text-sm text-slate-500"><?= esc($message['created_at']) ?></span>
                </div>

                <p class="text-slate-700"><?= esc($message['body']) ?></p>

                <div class="mt-3 flex gap-2 text-xs">
                <span class="px-3 py-1 bg-slate-100 rounded-full">
                    Sentimiento: <?= esc($message['sentiment'] ?? 'Sin analizar') ?>
                </span>
                    <span class="px-3 py-1 bg-slate-100 rounded-full">
                    Prioridad: <?= esc($message['priority']) ?>
                </span>
                    <span class="px-3 py-1 bg-slate-100 rounded-full">
    Envío: <?= esc($message['sent_status'] ?? 'not_sent') ?>
</span>
                    <span class="px-3 py-1 bg-slate-100 rounded-full">
                    Categoría: <?= esc($message['category'] ?? 'Sin categoría') ?>
                </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?= $this->endSection() ?>