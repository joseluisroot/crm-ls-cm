<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-4 text-left">ID</th>
                <th class="p-4 text-left">Plataforma</th>
                <th class="p-4 text-left">Tipo</th>
                <th class="p-4 text-left">Sender</th>
                <th class="p-4 text-left">Procesado</th>
                <th class="p-4 text-left">Fecha</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <tr class="border-b">
                    <td class="p-4"><?= esc($event['id']) ?></td>
                    <td class="p-4"><?= esc($event['platform']) ?></td>
                    <td class="p-4"><?= esc($event['event_type']) ?></td>
                    <td class="p-4"><?= esc($event['sender_id'] ?? '-') ?></td>
                    <td class="p-4">
                        <?= ((int) $event['processed'] === 1) ? 'Sí' : 'No' ?>
                    </td>
                    <td class="p-4"><?= esc($event['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <?= $pager->links() ?>
    </div>

<?= $this->endSection() ?>