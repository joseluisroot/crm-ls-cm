<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$isEditing = !empty($node);

$configuration = json_decode(
    $node['configuration'] ?? '{}',
    true
) ?: [];

$actionUrl = $isEditing
    ? site_url(
        "admin/workflows/{$workflow['id']}/versions/"
        . "{$version['id']}/nodes/{$node['id']}"
    )
    : site_url(
        "admin/workflows/{$workflow['id']}/versions/"
        . "{$version['id']}/nodes"
    );
?>

    <div class="max-w-5xl">
        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
                CIAC Process Designer
            </p>

            <h1 class="text-3xl font-black text-slate-900 mt-2">
                <?= esc($title) ?>
            </h1>

            <p class="text-slate-500 mt-2">
                <?= esc($workflow['name']) ?>
                · Versión <?= esc($version['version_number']) ?>
            </p>
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
                        Nombre
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        value="<?= esc(old('name', $node['name'] ?? '')) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Clave técnica
                    </label>

                    <input
                        type="text"
                        name="node_key"
                        required
                        value="<?= esc(old('node_key', $node['node_key'] ?? '')) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                        placeholder="ask_phone"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Tipo
                    </label>

                    <select
                        name="node_type"
                        id="node_type"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 bg-white"
                    >
                        <?php foreach ($nodeTypes as $type): ?>
                            <option
                                value="<?= esc($type) ?>"
                                <?= old(
                                    'node_type',
                                    $node['node_type'] ?? ''
                                ) === $type ? 'selected' : '' ?>
                            >
                                <?= esc($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Variable de contexto
                    </label>

                    <input
                        type="text"
                        name="context_key"
                        value="<?= esc(old(
                            'context_key',
                            $node['context_key'] ?? ''
                        )) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                        placeholder="phone"
                    >
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Mensaje
                    </label>

                    <textarea
                        name="message_text"
                        rows="6"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                    ><?= esc(old(
                            'message_text',
                            $node['message_text'] ?? ''
                        )) ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Longitud mínima
                    </label>

                    <input
                        type="number"
                        name="minimum_length"
                        min="0"
                        value="<?= esc(old(
                            'minimum_length',
                            $configuration['minimum_length'] ?? 0
                        )) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Acción
                    </label>

                    <select
                        name="action"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 bg-white"
                    >
                        <option value="">Sin acción</option>
                        <option
                            value="create_case"
                            <?= old(
                                'action',
                                $configuration['action'] ?? ''
                            ) === 'create_case' ? 'selected' : '' ?>
                        >
                            Crear caso
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Categoría para la acción
                    </label>

                    <input
                        type="text"
                        name="category"
                        value="<?= esc(old(
                            'category',
                            $configuration['category'] ?? ''
                        )) ?>"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                        placeholder="necesidad-comunitaria"
                    >
                </div>

                <div class="flex flex-col gap-3 justify-center">
                    <label class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            name="required"
                            value="1"
                            <?= !empty(old(
                                'required',
                                $configuration['required'] ?? false
                            )) ? 'checked' : '' ?>
                        >

                        <span class="text-sm font-semibold text-slate-700">
                        Respuesta obligatoria
                    </span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            name="is_terminal"
                            value="1"
                            <?= !empty(old(
                                'is_terminal',
                                $node['is_terminal'] ?? false
                            )) ? 'checked' : '' ?>
                        >

                        <span class="text-sm font-semibold text-slate-700">
                        Nodo terminal
                    </span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            name="is_start_node"
                            value="1"
                            <?= (
                                $version['start_node_key']
                                ?? null
                            ) === ($node['node_key'] ?? null)
                                ? 'checked'
                                : '' ?>
                        >

                        <span class="text-sm font-semibold text-slate-700">
                        Nodo inicial
                    </span>
                    </label>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mt-8">
                <button
                    type="submit"
                    class="bg-pink-600 hover:bg-pink-700 text-white font-bold px-6 py-3 rounded-xl"
                >
                    Guardar nodo
                </button>

                <a
                    href="<?= site_url(
                        "admin/workflows/{$workflow['id']}/versions/{$version['id']}"
                    ) ?>"
                    class="border border-slate-300 text-slate-700 font-bold px-6 py-3 rounded-xl"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>

<?= $this->endSection() ?>