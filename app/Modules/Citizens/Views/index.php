<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-4 text-left">Nombre</th>
                <th class="p-4 text-left">Municipio</th>
                <th class="p-4 text-left">Comunidad</th>
                <th class="p-4 text-left">Estado</th>
                <th class="p-4 text-left">Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($citizens as $citizen): ?>
                <tr class="border-b">
                    <td class="p-4"><?= esc($citizen['name']) ?></td>
                    <td class="p-4"><?= esc($citizen['municipality'] ?? '-') ?></td>
                    <td class="p-4"><?= esc($citizen['community'] ?? '-') ?></td>
                    <td class="p-4"><?= esc($citizen['status']) ?></td>
                    <td class="p-4">
                        <a href="/admin/citizens/<?= $citizen['id'] ?>" class="text-blue-600 font-semibold">
                            Ver perfil
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