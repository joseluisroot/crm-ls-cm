<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="bg-white rounded-2xl shadow p-6">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-slate-900">
                    <?= esc($case['title']) ?>
                </h3>

                <p class="text-slate-500 mt-1">
                    Caso #<?= esc($case['id']) ?>
                </p>
            </div>

            <a href="/admin/cases" class="text-blue-600 font-semibold">
                Volver a casos
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <div>
                <p class="text-sm text-slate-500">Ciudadano</p>
                <p class="font-semibold"><?= esc($case['citizen_name'] ?? '-') ?></p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Categoría</p>
                <p class="font-semibold"><?= esc($case['category_name'] ?? '-') ?></p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Estado</p>
                <p class="font-semibold"><?= esc($case['status_name'] ?? '-') ?></p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Prioridad</p>
                <p class="font-semibold"><?= esc($case['priority'] ?? '-') ?></p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Sentimiento</p>
                <p class="font-semibold"><?= esc($case['sentiment'] ?? '-') ?></p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Responsable</p>
                <p class="font-semibold"><?= esc($case['assigned_to'] ?? 'Sin asignar') ?></p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Fecha creación</p>
                <p class="font-semibold"><?= esc($case['created_at'] ?? '-') ?></p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Fecha cierre</p>
                <p class="font-semibold"><?= esc($case['closed_at'] ?? 'Abierto') ?></p>
            </div>

        </div>

        <div class="border-t pt-6">
            <p class="text-sm text-slate-500 mb-2">Descripción</p>
            <div class="bg-slate-50 rounded-xl p-5 text-slate-700">
                <?= nl2br(esc($case['description'])) ?>
            </div>
        </div>

    </div>

<?= $this->endSection() ?>