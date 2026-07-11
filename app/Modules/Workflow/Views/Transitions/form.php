<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$isEditing = !empty($transition);

$actionUrl = $isEditing
    ? site_url(
        "admin/workflows/{$workflowId}/versions/"
        . "{$versionId}/transitions/{$transition['id']}"
    )
    : site_url(
        "admin/workflows/{$workflowId}/versions/"
        . "{$versionId}/transitions"
    );
?>

    <div class="max-w-4xl">
        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
                CIAC Process Designer
            </p>

            <h1 class="text-3xl font-black text-slate-900 mt-2">
                <?= esc($title) ?>
            </h1>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-100 text-red-700 rounded-xl p-4 mb-6">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form
            method="post"
            action="<?= $actionUrl ?>"
            class="bg-white border border-slate-200 rounded-2xl shadow-sm p-7"
        >
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Nodo origen
                    </label>

                    <select
                        name="source_node_key"
                        required
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 bg-white"
                    >
                        <?php foreach ($nodes as $node): ?>
                            <option
                                value="<?= esc($node['node_key']) ?>"
                                <?= old(
                                    'source_node_key',
                                    $transition['source_node_key'] ?? ''
                                ) === $node['node_key'] ? 'selected' : '' ?>
                            >
                                <?= esc($node['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Nodo destino
                    </label>

                    <select
                        name="target_node_key"
                        required
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 bg-white"
                    >
                        <?php foreach ($nodes as $node): ?>
                            <option
                                value="<?= esc($node['node_key']) ?>"
                                <?= old(
                                    'target_node_key',
                                    $transition['target_node_key'] ?? ''
                                ) === $node['node_key'] ? 'selected' : '' ?>
                            >
                                <?= esc($node['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Etiqueta visible
                    </label>

                    <input
                        type="text"
                        name="label"
                        value="<?= esc(old(
                            'label',
                            $transition['label'] ?? ''
                        )) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                        placeholder="Reportar necesidad"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Payload
                    </label>

                    <input
                        type="text"
                        name="payload"
                        value="<?= esc(old(
                            'payload',
                            $transition['payload'] ?? ''
                        )) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                        placeholder="OPTION_NEED"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Tipo de condición
                    </label>

                    <select
                        name="condition_type"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 bg-white"
                    >
                        <?php foreach ([
                                           'always',
                                           'payload_equals',
                                           'text_equals',
                                       ] as $condition): ?>
                            <option
                                value="<?= esc($condition) ?>"
                                <?= old(
                                    'condition_type',
                                    $transition['condition_type'] ?? 'always'
                                ) === $condition ? 'selected' : '' ?>
                            >
                                <?= esc($condition) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Valor de condición
                    </label>

                    <input
                        type="text"
                        name="condition_value"
                        value="<?= esc(old(
                            'condition_value',
                            $transition['condition_value'] ?? ''
                        )) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Orden
                    </label>

                    <input
                        type="number"
                        min="0"
                        name="sort_order"
                        value="<?= esc(old(
                            'sort_order',
                            $transition['sort_order'] ?? 0
                        )) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                    >
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mt-8">
                <button
                    type="submit"
                    class="bg-pink-600 hover:bg-pink-700 text-white font-bold px-6 py-3 rounded-xl"
                >
                    Guardar transición
                </button>

                <a
                    href="<?= site_url(
                        "admin/workflows/{$workflowId}/versions/{$versionId}"
                    ) ?>"
                    class="border border-slate-300 text-slate-700 font-bold px-6 py-3 rounded-xl"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>

<?= $this->endSection() ?>