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

        <div class="border-t pt-6 mt-8">
            <h4 class="text-xl font-bold text-slate-900 mb-5">
                Gestión del caso
            </h4>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="bg-green-100 text-green-700 rounded-xl p-4 mb-5 text-sm">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-100 text-red-700 rounded-xl p-4 mb-5 text-sm">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <form method="post" action="/admin/cases/<?= esc($case['id']) ?>/change-status" class="bg-slate-50 rounded-xl p-5">
                    <?= csrf_field() ?>

                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Cambiar estado
                    </label>

                    <select name="status_id" class="w-full border border-slate-300 rounded-xl px-4 py-3 mb-4">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= esc($status['id']) ?>" <?= (int)$case['status_id'] === (int)$status['id'] ? 'selected' : '' ?>>
                                <?= esc($status['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button class="bg-pink-600 hover:bg-pink-700 text-white font-bold px-5 py-3 rounded-xl">
                        Actualizar estado
                    </button>
                </form>

                <form
                        method="post"
                        action="<?= site_url('admin/cases/' . $case['id'] . '/assign') ?>"
                        class="bg-slate-50 rounded-xl p-5"
                >
                    <?= csrf_field() ?>

                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Responsable del caso
                    </label>

                    <select
                            name="assigned_user_id"
                            required
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 mb-4 bg-white"
                    >
                        <option value="">Seleccionar responsable</option>

                        <?php foreach ($assignableUsers as $user): ?>
                            <option
                                    value="<?= esc($user['id']) ?>"
                                    <?= (int) ($case['assigned_user_id'] ?? 0) === (int) $user['id']
                                            ? 'selected'
                                            : '' ?>
                            >
                                <?= esc($user['name']) ?>
                                — <?= esc($user['role']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="flex flex-wrap gap-3">
                        <button
                                type="submit"
                                class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-5 py-3 rounded-xl"
                        >
                            Asignar caso
                        </button>
                    </div>
                </form>

                <?php if (!empty($case['assigned_user_id'])): ?>
                    <form
                            method="post"
                            action="<?= site_url('admin/cases/' . $case['id'] . '/unassign') ?>"
                            class="mt-3"
                    >
                        <?= csrf_field() ?>

                        <button
                                type="submit"
                                class="text-sm font-semibold text-red-600 hover:text-red-700"
                                onclick="return confirm('¿Deseas retirar el responsable asignado?')"
                        >
                            Retirar asignación
                        </button>
                    </form>
                <?php endif; ?>

            </div>
        </div>

        <div class="border-t pt-6 mt-8">
            <h4 class="text-xl font-bold text-slate-900 mb-5">
                Línea de tiempo del caso
            </h4>

            <?php if (empty($history)): ?>
                <div class="bg-slate-50 rounded-xl p-5 text-slate-500">
                    Aún no hay movimientos registrados para este caso.
                </div>
            <?php else: ?>
                <div class="space-y-5">
                    <?php foreach ($history as $item): ?>
                        <div class="flex gap-4">
                            <div class="w-3 h-3 mt-2 rounded-full bg-pink-600"></div>

                            <div class="flex-1 bg-slate-50 rounded-xl p-5">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <p class="font-bold text-slate-900">
                                            <?= esc($item['event']) ?>
                                        </p>

                                        <p class="text-slate-600 mt-1">
                                            <?= esc($item['description'] ?? '') ?>
                                        </p>
                                    </div>

                                    <span class="text-xs text-slate-400 whitespace-nowrap">
                                <?= esc($item['created_at']) ?>
                            </span>
                                </div>

                                <p class="text-xs text-slate-400 mt-3">
                                    Registrado por: <?= esc($item['performed_by'] ?? 'system') ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

<?= $this->endSection() ?>