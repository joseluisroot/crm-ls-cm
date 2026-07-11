<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
            CIAC Process Designer
        </p>

        <h1 class="text-3xl font-black text-slate-900 mt-2">
            <?= esc($workflow['name']) ?>
            · Versión <?= esc($version['version_number']) ?>
        </h1>

        <p class="text-slate-500 mt-2">
            Estructura actual de nodos y transiciones.
        </p>
    </div>

<?php if ($version['status'] === 'draft'): ?>
    <div class="flex flex-wrap gap-3 mb-8">
        <a
                href="<?= site_url(
                        "admin/workflows/{$workflow['id']}/versions/"
                        . "{$version['id']}/nodes/create"
                ) ?>"
                class="bg-pink-600 hover:bg-pink-700 text-white font-bold px-5 py-3 rounded-xl"
        >
            Agregar nodo
        </a>

        <a
                href="<?= site_url(
                        "admin/workflows/{$workflow['id']}/versions/"
                        . "{$version['id']}/transitions/create"
                ) ?>"
                class="bg-slate-950 hover:bg-slate-800 text-white font-bold px-5 py-3 rounded-xl"
        >
            Agregar transición
        </a>

        <a
                href="<?= site_url(
                        "admin/workflows/{$workflow['id']}/versions/"
                        . "{$version['id']}/validate"
                ) ?>"
                class="border border-blue-300 bg-blue-50 text-blue-700 font-bold px-5 py-3 rounded-xl"
        >
            Validar workflow
        </a>

    </div>

    <?php if ($version['status'] === 'draft'): ?>
        <p class="text-sm text-slate-500 mt-3">
            Valida la estructura antes de publicar esta versión.
        </p>
    <?php endif; ?>
<?php endif; ?>



    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">
                Nodos
            </h2>


            <div class="space-y-4 mt-5">
                <?php foreach ($nodes as $node): ?>
                    <div class="bg-slate-50 rounded-xl p-5">
                        <?php if ($version['status'] === 'draft'): ?>
                            <div class="flex gap-3 mt-4">
                                <a
                                        href="<?= site_url(
                                                "admin/workflows/{$workflow['id']}/versions/"
                                                . "{$version['id']}/nodes/{$node['id']}/edit"
                                        ) ?>"
                                        class="text-sm font-bold text-blue-600"
                                >
                                    Editar
                                </a>

                                <form
                                        method="post"
                                        action="<?= site_url(
                                                "admin/workflows/{$workflow['id']}/versions/"
                                                . "{$version['id']}/nodes/{$node['id']}/delete"
                                        ) ?>"
                                >
                                    <?= csrf_field() ?>

                                    <button
                                            type="submit"
                                            class="text-sm font-bold text-red-600"
                                            onclick="return confirm(
                    '¿Eliminar este nodo y sus transiciones?'
                )"
                                    >
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-black text-slate-900">
                                    <?= esc($node['name']) ?>
                                </p>

                                <p class="text-xs text-slate-400 mt-1">
                                    <?= esc($node['node_key']) ?>
                                </p>
                            </div>

                            <span class="px-3 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-bold">
                            <?= esc($node['node_type']) ?>
                        </span>
                        </div>

                        <?php if (!empty($node['message_text'])): ?>
                            <p class="text-sm text-slate-600 mt-4 whitespace-pre-line">
                                <?= esc($node['message_text']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($nodes)): ?>
                    <p class="text-slate-500">
                        Esta versión aún no contiene nodos.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">
                Transiciones
            </h2>


            <div class="space-y-4 mt-5">
                <?php foreach ($transitions as $transition): ?>
                    <?php if ($version['status'] === 'draft'): ?>
                        <div class="flex gap-3 mt-4">
                            <a
                                    href="<?= site_url(
                                            "admin/workflows/{$workflow['id']}/versions/"
                                            . "{$version['id']}/transitions/"
                                            . "{$transition['id']}/edit"
                                    ) ?>"
                                    class="text-sm font-bold text-blue-600"
                            >
                                Editar
                            </a>

                            <form
                                    method="post"
                                    action="<?= site_url(
                                            "admin/workflows/{$workflow['id']}/versions/"
                                            . "{$version['id']}/transitions/"
                                            . "{$transition['id']}/delete"
                                    ) ?>"
                            >
                                <?= csrf_field() ?>

                                <button
                                        type="submit"
                                        class="text-sm font-bold text-red-600"
                                        onclick="return confirm(
                    '¿Eliminar esta transición?'
                )"
                                >
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <div class="border border-slate-200 rounded-xl p-5">
                        <p class="font-bold text-slate-900">
                            <?= esc($transition['source_node_key']) ?>
                            →
                            <?= esc($transition['target_node_key']) ?>
                        </p>

                        <p class="text-sm text-slate-500 mt-2">
                            <?= esc(
                                    $transition['label']
                                    ?? 'Sin etiqueta'
                            ) ?>
                        </p>

                        <p class="text-xs text-slate-400 mt-2">
                            Condición:
                            <?= esc($transition['condition_type']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($transitions)): ?>
                    <p class="text-slate-500">
                        Esta versión aún no contiene transiciones.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>