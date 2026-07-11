<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
                Workflow
            </p>

            <h1 class="text-3xl font-black text-slate-900 mt-2">
                <?= esc($workflow['name']) ?>
            </h1>

            <p class="text-slate-500 mt-2">
                <?= esc($workflow['description'] ?? '') ?>
            </p>
        </div>

        <form
            method="post"
            action="<?= site_url(
                'admin/workflows/'
                . $workflow['id']
                . '/versions'
            ) ?>"
        >
            <?= csrf_field() ?>

            <button
                type="submit"
                class="bg-pink-600 hover:bg-pink-700 text-white font-bold px-5 py-3 rounded-xl"
            >
                Nueva versión vacía
            </button>
        </form>
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

    <div class="space-y-5">
        <?php foreach ($versions as $version): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-black text-slate-900">
                                Versión <?= esc($version['version_number']) ?>
                            </h2>

                            <span class="px-3 py-1 rounded-full text-xs font-bold
                            <?= $version['status'] === 'published'
                                ? 'bg-green-100 text-green-700'
                                : ($version['status'] === 'archived'
                                    ? 'bg-slate-200 text-slate-600'
                                    : 'bg-amber-100 text-amber-700') ?>">
                            <?= esc($version['status']) ?>
                        </span>
                        </div>

                        <p class="text-sm text-slate-500 mt-3">
                            Nodo inicial:
                            <strong>
                                <?= esc(
                                    $version['start_node_key']
                                    ?? 'No configurado'
                                ) ?>
                            </strong>
                        </p>

                        <p class="text-sm text-slate-500 mt-1">
                            <?= number_format($version['nodes_count']) ?> nodos
                            ·
                            <?= number_format($version['transitions_count']) ?>
                            transiciones
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="<?= site_url(
                                'admin/workflows/'
                                . $workflow['id']
                                . '/versions/'
                                . $version['id']
                            ) ?>"
                            class="px-4 py-2 rounded-xl bg-slate-950 text-white text-sm font-bold"
                        >
                            Ver estructura
                        </a>

                        <form
                            method="post"
                            action="<?= site_url(
                                'admin/workflows/'
                                . $workflow['id']
                                . '/versions/'
                                . $version['id']
                                . '/clone'
                            ) ?>"
                        >
                            <?= csrf_field() ?>

                            <button
                                type="submit"
                                class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm font-bold"
                            >
                                Clonar
                            </button>
                        </form>

                        <?php if ($version['status'] === 'draft'): ?>
                            <form
                                method="post"
                                action="<?= site_url(
                                    'admin/workflows/'
                                    . $workflow['id']
                                    . '/versions/'
                                    . $version['id']
                                    . '/publish'
                                ) ?>"
                            >
                                <?= csrf_field() ?>

                                <button
                                    type="submit"
                                    class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-bold"
                                    onclick="return confirm(
                                    '¿Deseas publicar esta versión?'
                                )"
                                >
                                    Publicar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?= $this->endSection() ?>