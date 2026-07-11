<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$metadata = $response->metadata ?? [];
$quickReplies = $response->quickReplies ?? [];
$nodeType = $metadata['node_type'] ?? 'desconocido';
$nodeName = $metadata['node_name'] ?? $response->currentNodeKey;
?>

    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
                Workflow Simulator
            </p>

            <h1 class="text-3xl font-black text-slate-900 mt-2">
                <?= esc($simulation['workflow_name']) ?>
            </h1>

            <p class="text-slate-500 mt-2">
                Versión <?= esc($simulation['version_number']) ?>
                · Simulación #<?= esc($simulation['id']) ?>
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <form
                method="post"
                action="<?= site_url(
                    'admin/workflows/simulator/'
                    . $simulation['id']
                    . '/restart'
                ) ?>"
            >
                <?= csrf_field() ?>

                <button
                    type="submit"
                    class="px-5 py-3 rounded-xl border border-slate-300 bg-white font-bold text-slate-700 hover:bg-slate-50"
                >
                    Reiniciar simulación
                </button>
            </form>

            <a
                href="<?= site_url('admin/workflows/simulator') ?>"
                class="px-5 py-3 rounded-xl bg-slate-950 text-white font-bold hover:bg-slate-800"
            >
                Nueva simulación
            </a>
        </div>
    </div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="bg-red-100 text-red-700 rounded-xl p-4 mb-6">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

    <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">

        <div class="2xl:col-span-2">
            <div class="bg-slate-950 rounded-3xl overflow-hidden shadow-xl min-h-[650px] flex flex-col">

                <div class="border-b border-slate-800 p-5 flex items-center justify-between">
                    <div>
                        <p class="font-black text-white">
                            CIAC Platform
                        </p>

                        <p class="text-xs text-slate-400 mt-1">
                            Simulación segura · No envía mensajes reales
                        </p>
                    </div>

                    <span class="text-xs font-bold px-3 py-1 rounded-full
                    <?= $simulation['status'] === 'completed'
                        ? 'bg-green-500/20 text-green-300'
                        : 'bg-blue-500/20 text-blue-300' ?>">
                    <?= esc($simulation['status']) ?>
                </span>
                </div>

                <div class="flex-1 p-6 md:p-8 overflow-y-auto">
                    <div class="max-w-2xl">

                        <?php if (!empty($response->text)): ?>
                            <div class="flex items-end gap-3">
                                <div class="w-9 h-9 rounded-full bg-pink-600 text-white flex items-center justify-center shrink-0">
                                    C
                                </div>

                                <div class="bg-slate-800 text-slate-100 rounded-2xl rounded-bl-md px-5 py-4 whitespace-pre-line leading-relaxed">
                                    <?= esc($response->text) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($quickReplies)): ?>
                            <div class="flex flex-wrap gap-3 mt-5 ml-12">
                                <?php foreach ($quickReplies as $reply): ?>
                                    <form
                                        method="post"
                                        action="<?= site_url(
                                            'admin/workflows/simulator/'
                                            . $simulation['id']
                                            . '/interact'
                                        ) ?>"
                                    >
                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="payload"
                                            value="<?= esc($reply['payload']) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="px-4 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold"
                                        >
                                            <?= esc($reply['title']) ?>
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <?php if (!$response->completed): ?>
                    <div class="border-t border-slate-800 p-5">
                        <form
                            method="post"
                            action="<?= site_url(
                                'admin/workflows/simulator/'
                                . $simulation['id']
                                . '/interact'
                            ) ?>"
                            class="flex gap-3"
                        >
                            <?= csrf_field() ?>

                            <input
                                type="text"
                                name="text"
                                class="flex-1 bg-slate-800 border border-slate-700 text-white placeholder-slate-500 rounded-xl px-4 py-3"
                                placeholder="Escribe una respuesta de prueba..."
                                autocomplete="off"
                            >

                            <button
                                type="submit"
                                class="bg-pink-600 hover:bg-pink-700 text-white font-bold px-6 py-3 rounded-xl"
                            >
                                Enviar
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="border-t border-slate-800 p-5 text-center">
                        <p class="text-green-300 font-bold">
                            ✓ La simulación finalizó correctamente.
                        </p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <div class="space-y-6">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="font-black text-slate-900 text-lg">
                    Nodo actual
                </h2>

                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Clave</dt>
                        <dd class="font-bold text-slate-900 mt-1">
                            <?= esc($response->currentNodeKey) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">Nombre</dt>
                        <dd class="font-bold text-slate-900 mt-1">
                            <?= esc($nodeName) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">Tipo</dt>
                        <dd class="mt-1">
                        <span class="inline-flex px-3 py-1 rounded-full bg-pink-100 text-pink-700 font-bold">
                            <?= esc($nodeType) ?>
                        </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="font-black text-slate-900 text-lg">
                    Contexto capturado
                </h2>

                <?php if (empty($context)): ?>
                    <p class="text-sm text-slate-500 mt-4">
                        Aún no se han capturado variables.
                    </p>
                <?php else: ?>
                    <div class="space-y-3 mt-4">
                        <?php foreach ($context as $key => $value): ?>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs uppercase tracking-wider text-slate-400 font-bold">
                                    <?= esc($key) ?>
                                </p>

                                <p class="text-sm text-slate-800 mt-2">
                                    <?= esc(
                                        is_scalar($value)
                                            ? (string) $value
                                            : json_encode(
                                            $value,
                                            JSON_UNESCAPED_UNICODE
                                        )
                                    ) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="font-black text-slate-900 text-lg">
                    Registro de ejecución
                </h2>

                <?php if (empty($executionLog)): ?>
                    <p class="text-sm text-slate-500 mt-4">
                        El flujo aún no ha recorrido transiciones.
                    </p>
                <?php else: ?>
                    <div class="space-y-4 mt-5">
                        <?php foreach (array_reverse($executionLog) as $entry): ?>
                            <div class="border-l-2 border-pink-500 pl-4">
                                <p class="text-sm font-bold text-slate-800">
                                    <?php if (!empty($entry['source_node'])): ?>
                                        <?= esc($entry['source_node']) ?>
                                        →
                                        <?= esc($entry['target_node']) ?>
                                    <?php else: ?>
                                        Acción:
                                        <?= esc($entry['action'] ?? 'desconocida') ?>
                                    <?php endif; ?>
                                </p>

                                <p class="text-xs text-slate-400 mt-1">
                                    <?= esc($entry['timestamp'] ?? '') ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($simulation['last_error'])): ?>
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                    <h2 class="font-black text-red-800">
                        Último error
                    </h2>

                    <p class="text-sm text-red-700 mt-3">
                        <?= esc($simulation['last_error']) ?>
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>

<?= $this->endSection() ?>