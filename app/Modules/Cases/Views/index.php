<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="mb-6">
        <a href="/admin/cases/create" class="bg-blue-600 text-white px-5 py-3 rounded-xl font-semibold">
            Crear caso
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-4 text-left">Título</th>
                <th class="p-4 text-left">Ciudadano</th>
                <th class="p-4 text-left">Categoría</th>
                <th class="p-4 text-left">Prioridad</th>
                <th class="p-4 text-left">Estado</th>
                <th class="p-4 text-left">Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($cases as $case): ?>
                <tr class="border-b">
                    <td class="p-4"><?= esc($case['title']) ?></td>
                    <td class="p-4"><?= esc($case['citizen_name']) ?></td>
                    <td class="p-4"><?= esc($case['category_name'] ?? '-') ?></td>
                    <td class="p-4"><?= esc($case['priority']) ?></td>
                    <td class="p-4"><?= esc($case['status_name']) ?></td>
                    <td class="p-4">
                        <a href="/admin/cases/<?= $case['id'] ?>" class="text-blue-600 font-semibold">
                            Ver
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