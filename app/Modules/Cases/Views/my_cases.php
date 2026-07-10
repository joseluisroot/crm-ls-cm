<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-900">
                Mis casos asignados
            </h1>

            <p class="text-slate-500 mt-2">
                Casos que requieren tu revisión, atención o seguimiento.
            </p>
        </div>

        <div class="bg-pink-50 text-pink-700 rounded-2xl px-5 py-3">
        <span class="font-black text-xl">
            <?= count($cases) ?>
        </span>
            <span class="text-sm ml-1">
            casos
        </span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <?php if (empty($cases)): ?>
            <div class="p-10 text-center">
                <div class="text-4xl mb-4">✅</div>

                <h3 class="font-bold text-slate-900">
                    No tienes casos asignados
                </h3>

                <p class="text-slate-500 mt-2">
                    Los nuevos casos asignados aparecerán aquí.
                </p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($cases as $case): ?>
                    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-pink-100 text-pink-700">
                                <?= esc($case['public_code'] ?? '#' . $case['id']) ?>
                            </span>

                                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-600">
                                <?= esc($case['status_name']) ?>
                            </span>
                            </div>

                            <h3 class="font-bold text-slate-900 mt-3">
                                <?= esc($case['title']) ?>
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                Ciudadano:
                                <?= esc($case['citizen_name']) ?>
                            </p>

                            <p class="text-sm text-slate-500">
                                Categoría:
                                <?= esc($case['category_name'] ?? 'Sin clasificar') ?>
                            </p>
                        </div>

                        <a
                            href="<?= site_url('admin/cases/' . $case['id']) ?>"
                            class="inline-flex justify-center px-5 py-3 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800"
                        >
                            Gestionar caso
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?= $this->endSection() ?>