<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-4 text-left">Ciudadano</th>
                <th class="p-4 text-left">Canal</th>
                <th class="p-4 text-left">Estado</th>
                <th class="p-4 text-left">Último mensaje</th>
                <th class="p-4 text-left">Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($conversations as $conversation): ?>
                <tr class="border-b">
                    <td class="p-4"><?= esc($conversation['citizen_name']) ?></td>
                    <td class="p-4"><?= esc($conversation['channel']) ?></td>
                    <td class="p-4"><?= esc($conversation['status']) ?></td>
                    <td class="p-4"><?= esc($conversation['last_message_at'] ?? '-') ?></td>
                    <td class="p-4">
                        <a href="/admin/conversations/<?= $conversation['id'] ?>" class="text-blue-600 font-semibold">
                            Ver conversación
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <?= $pager->links() ?>
    </div>

<?= $this->endSection() ?>