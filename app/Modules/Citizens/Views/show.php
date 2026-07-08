<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="bg-white rounded-2xl shadow p-6 mb-6">
        <h3 class="text-2xl font-bold"><?= esc($citizen['name']) ?></h3>
        <p class="text-slate-500">Facebook ID: <?= esc($citizen['facebook_id'] ?? '-') ?></p>
        <p class="text-slate-500">Municipio: <?= esc($citizen['municipality'] ?? '-') ?></p>
        <p class="text-slate-500">Comunidad: <?= esc($citizen['community'] ?? '-') ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl shadow p-6">
            <h4 class="text-xl font-bold mb-4">Conversaciones</h4>

            <?php foreach ($conversations as $conversation): ?>
                <a href="/admin/conversations/<?= $conversation['id'] ?>" class="block border-b py-3 text-blue-600">
                    Conversación #<?= esc($conversation['id']) ?> - <?= esc($conversation['status']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <h4 class="text-xl font-bold mb-4">Casos</h4>

            <?php foreach ($cases as $case): ?>
                <a href="/admin/cases/<?= $case['id'] ?>" class="block border-b py-3 text-blue-600">
                    <?= esc($case['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>

    </div>

<?= $this->endSection() ?>