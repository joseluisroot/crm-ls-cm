<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$isValid = $validation->isValid();
$errors = $validation->errors;
$warnings = $validation->warnings;
$information = $validation->information;
?>

    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
                Workflow Validator
            </p>

            <h1 class="text-3xl font-black text-slate-900 mt-2">
                Validación de
                <?= esc($workflow['name']) ?>
                v<?= esc($version['version_number']) ?>
            </h1>

            <p class="text-slate-500 mt-2">
                Revisión técnica antes de publicar la versión.
            </p>
        </div>

        <a
            href="<?= site_url(
                "admin/workflows/{$workflow['id']}/versions/{$version['id']}"
            ) ?>"
            class="px-5 py-3 rounded-xl bg-slate-950 text-white font-bold"
        >
            Volver a la versión
        </a>
    </div>

    <div class="rounded-2xl p-6 mb-8 border
    <?= $isValid
        ? 'bg-green-50 border-green-200'
        : 'bg-red-50 border-red-200' ?>">

        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl
            <?= $isValid
                ? 'bg-green-600 text-white'
                : 'bg-red-600 text-white' ?>">
                <?= $isValid ? '✓' : '!' ?>
            </div>

            <div>
                <h2 class="text-xl font-black
                <?= $isValid
                    ? 'text-green-900'
                    : 'text-red-900' ?>">
                    <?= $isValid
                        ? 'Workflow válido'
                        : 'Workflow con errores' ?>
                </h2>

                <p class="mt-1
                <?= $isValid
                    ? 'text-green-700'
                    : 'text-red-700' ?>">
                    <?= $isValid
                        ? 'La versión puede publicarse.'
                        : 'Corrige los errores antes de publicar.' ?>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-red-700">
                    Errores
                </h2>

                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-black">
                <?= count($errors) ?>
            </span>
            </div>

            <?php if (empty($errors)): ?>
                <p class="text-sm text-slate-500 mt-5">
                    No se detectaron errores.
                </p>
            <?php else: ?>
                <div class="space-y-4 mt-5">
                    <?php foreach ($errors as $item): ?>
                        <div class="bg-red-50 rounded-xl p-4">
                            <p class="font-bold text-red-900">
                                <?= esc($item['message']) ?>
                            </p>

                            <p class="text-xs text-red-500 mt-2">
                                <?= esc($item['code']) ?>
                            </p>

                            <?php if (!empty($item['context'])): ?>
                                <pre class="mt-3 text-xs text-red-700 whitespace-pre-wrap"><?= esc(
                                        json_encode(
                                            $item['context'],
                                            JSON_PRETTY_PRINT
                                            | JSON_UNESCAPED_UNICODE
                                        )
                                    ) ?></pre>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-amber-700">
                    Advertencias
                </h2>

                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-black">
                <?= count($warnings) ?>
            </span>
            </div>

            <?php if (empty($warnings)): ?>
                <p class="text-sm text-slate-500 mt-5">
                    No se detectaron advertencias.
                </p>
            <?php else: ?>
                <div class="space-y-4 mt-5">
                    <?php foreach ($warnings as $item): ?>
                        <div class="bg-amber-50 rounded-xl p-4">
                            <p class="font-bold text-amber-900">
                                <?= esc($item['message']) ?>
                            </p>

                            <p class="text-xs text-amber-500 mt-2">
                                <?= esc($item['code']) ?>
                            </p>

                            <?php if (!empty($item['context'])): ?>
                                <pre class="mt-3 text-xs text-amber-700 whitespace-pre-wrap"><?= esc(
                                        json_encode(
                                            $item['context'],
                                            JSON_PRETTY_PRINT
                                            | JSON_UNESCAPED_UNICODE
                                        )
                                    ) ?></pre>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-blue-700">
                    Información
                </h2>

                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-black">
                <?= count($information) ?>
            </span>
            </div>

            <div class="space-y-4 mt-5">
                <?php foreach ($information as $item): ?>
                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="font-bold text-blue-900">
                            <?= esc($item['message']) ?>
                        </p>

                        <p class="text-xs text-blue-500 mt-2">
                            <?= esc($item['code']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

<?= $this->endSection() ?>