<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
                Dynamic Workflow Engine
            </p>

            <h1 class="text-3xl font-black text-slate-900 mt-2">
                Workflows
            </h1>

            <p class="text-slate-500 mt-2">
                Diseña, versiona y publica experiencias de atención.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a
                href="<?= site_url('admin/workflows/simulator') ?>"
                class="px-5 py-3 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold"
            >
                🧪 Simulador
            </a>

            <a
                href="<?= site_url('admin/workflows/create') ?>"
                class="px-5 py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold"
            >
                Crear workflow
            </a>
        </div>
    </div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-100 text-green-700 rounded-xl p-4 mb-6">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="bg-red-100 text-red-700 rounded-xl p-4 mb-6">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <?php foreach ($workflows as $workflow): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                            <?= esc($workflow['channel']) ?>
                        </span>

                            <span class="px-3 py-1 rounded-full text-xs font-bold
                            <?= $workflow['status'] === 'published'
                                ? 'bg-green-100 text-green-700'
                                : ($workflow['status'] === 'archived'
                                    ? 'bg-slate-200 text-slate-600'
                                    : 'bg-amber-100 text-amber-700') ?>">
                            <?= esc($workflow['status']) ?>
                        </span>
                        </div>

                        <h2 class="text-xl font-black text-slate-900 mt-4">
                            <?= esc($workflow['name']) ?>
                        </h2>

                        <p class="text-sm text-slate-400 mt-1">
                            <?= esc($workflow['slug']) ?>
                        </p>
                    </div>

                    <?php if (!empty($workflow['active_version_number'])): ?>
                        <div class="text-center bg-pink-50 text-pink-700 rounded-xl px-4 py-3">
                            <p class="text-xs font-bold uppercase">
                                Activa
                            </p>
                            <p class="text-lg font-black">
                                v<?= esc($workflow['active_version_number']) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <p class="text-slate-600 mt-5">
                    <?= esc(
                        $workflow['description']
                        ?? 'Sin descripción.'
                    ) ?>
                </p>

                <div class="flex flex-wrap gap-3 mt-6">
                    <a
                        href="<?= site_url(
                            'admin/workflows/' . $workflow['id']
                        ) ?>"
                        class="px-4 py-2 rounded-xl bg-slate-950 text-white font-bold text-sm"
                    >
                        Gestionar
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($workflows)): ?>
            <div class="xl:col-span-2 bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center">
                <h3 class="font-black text-slate-900">
                    No existen workflows
                </h3>

                <p class="text-slate-500 mt-2">
                    Crea el primer flujo configurable de la plataforma.
                </p>
            </div>
        <?php endif; ?>
    </div>

<?= $this->endSection() ?>