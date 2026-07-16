<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$sourceAuthor = $item['source']['author_name'] ?? $item['title'] ?? 'Ciudadano';
$sourceMessage = $item['source']['message'] ?? $item['summary'] ?? 'Sin contenido disponible.';
$sourceContext = $item['source']['post_message'] ?? $item['source']['external_message_id'] ?? $item['origin_id'] ?? null;
$permalink = $item['source']['permalink_url'] ?? null;
$hasAssignableUsers = ! empty($users);
?>

<header class="mb-7">
    <a href="<?= site_url('admin/operations') ?>" class="inline-flex items-center gap-2 text-sm font-bold text-pink-600 hover:text-pink-700">
        <span aria-hidden="true">←</span> Volver a la cola
    </a>

    <div class="mt-4 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Citizen Care Workspace</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Atención #<?= esc($item['id']) ?></h1>
            <p class="mt-2 max-w-3xl break-all text-sm text-slate-500"><?= esc($item['uuid']) ?></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-pink-50 px-3 py-2 text-xs font-black text-pink-700"><?= esc($item['channel']) ?></span>
            <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-black text-slate-700"><?= esc($item['status_name']) ?></span>
            <span class="rounded-full bg-amber-50 px-3 py-2 text-xs font-black text-amber-700"><?= esc($item['priority_name']) ?></span>
        </div>
    </div>
</header>

<div class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1fr)_390px]">
    <main class="min-w-0 space-y-6">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Interacción de origen</p>
                        <h2 class="mt-2 text-xl font-black text-slate-950"><?= esc($sourceAuthor) ?></h2>
                    </div>
                    <?php if ($permalink): ?>
                        <a href="<?= esc($permalink) ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-700">
                            Ver en Facebook ↗
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="px-6 py-6">
                <div class="rounded-2xl bg-slate-50 px-5 py-5 text-[15px] leading-7 text-slate-700 whitespace-pre-line"><?= esc($sourceMessage) ?></div>

                <?php if ($sourceContext): ?>
                    <div class="mt-5 border-l-4 border-pink-200 pl-4">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Contexto de publicación</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600"><?= esc($sourceContext) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-3xl border border-pink-200 bg-white p-6 shadow-sm shadow-pink-100/60">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-pink-600">Centro de respuesta</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">Responder al ciudadano</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Redacta, guarda o envía desde CIAC. La atención solo se marcará respondida cuando el canal confirme el envío.</p>
                </div>
                <?php if ($responseDraft): ?>
                    <span class="w-fit rounded-full bg-amber-50 px-3 py-2 text-xs font-black text-amber-700">Borrador guardado</span>
                <?php endif; ?>
            </div>

            <?php if (! empty($quickActions)): ?>
                <div class="mt-6">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">Respuestas rápidas</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <?php foreach ($quickActions as $action): ?>
                            <button type="button" class="quick-action rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-700" data-body="<?= esc($action['body'], 'attr') ?>">
                                <?= esc($action['command']) ?> · <?= esc($action['label']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/response-draft') ?>" class="mt-6" id="response-form">
                <?= csrf_field() ?>
                <input type="hidden" name="channel" value="<?= esc($item['channel'], 'attr') ?>">

                <label for="response-body" class="text-sm font-black text-slate-700">Mensaje de respuesta</label>
                <textarea id="response-body" name="body" rows="8" maxlength="5000" required class="mt-3 w-full rounded-2xl border border-slate-300 px-5 py-4 leading-7 focus:border-pink-500 focus:ring-pink-500" placeholder="Escribe / para usar acciones rápidas o redacta la respuesta..."><?= esc($responseDraft['body'] ?? '') ?></textarea>

                <div class="mt-3 flex flex-col gap-2 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                    <p><span id="response-count">0</span>/5000 caracteres</p>
                    <p>Último guardado: <?= esc($responseDraft['updated_at'] ?? 'Sin guardar') ?></p>
                </div>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <button data-loading="Guardando borrador..." class="rounded-xl bg-slate-950 px-5 py-3 font-black text-white transition hover:bg-slate-800">Guardar borrador</button>
                    <?php if ($responseCapability['ready']): ?>
                        <button formaction="<?= site_url('admin/operations/' . $item['id'] . '/response-send') ?>" data-confirm="¿Enviar esta respuesta por <?= esc($responseCapability['channel'], 'attr') ?>?" data-loading="Enviando respuesta..." class="rounded-xl bg-pink-600 px-5 py-3 font-black text-white transition hover:bg-pink-700">Enviar por <?= esc($responseCapability['channel']) ?></button>
                    <?php else: ?>
                        <button type="button" disabled class="cursor-not-allowed rounded-xl bg-pink-300 px-5 py-3 font-black text-white">Enviar respuesta</button>
                    <?php endif; ?>
                </div>

                <?php if (! $responseCapability['ready']): ?>
                    <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700"><?= esc($responseCapability['reason']) ?></p>
                <?php endif; ?>
            </form>
        </section>

        <?php if (! empty($responses)): ?>
            <details class="group rounded-3xl border border-slate-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Historial</p>
                        <h2 class="mt-1 text-xl font-black text-slate-950">Respuestas registradas</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-black text-slate-600"><?= count($responses) ?> respuestas</span>
                </summary>
                <div class="space-y-4 border-t border-slate-100 px-6 py-5">
                    <?php foreach ($responses as $response): ?>
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <strong class="text-sm text-slate-900"><?= esc($response['channel']) ?></strong>
                                <span class="text-xs font-black <?= $response['status'] === 'SENT' ? 'text-green-600' : 'text-red-600' ?>"><?= esc($response['status']) ?></span>
                            </div>
                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700"><?= esc($response['body']) ?></p>
                            <p class="mt-3 break-all text-xs text-slate-400">Meta ID: <?= esc($response['external_response_id'] ?? '-') ?> · <?= esc($response['sent_at'] ?? $response['created_at']) ?></p>
                            <?php if (! empty($response['error_message'])): ?>
                                <p class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600"><?= esc($response['error_message']) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endif; ?>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <?php if (! empty($activityWidgetHtml)): ?><div class="min-w-0"><?= $activityWidgetHtml ?></div><?php endif; ?>
            <?php if (! empty($timelineWidgetHtml)): ?><div class="min-w-0"><?= $timelineWidgetHtml ?></div><?php endif; ?>
        </section>
    </main>

    <aside class="min-w-0 space-y-6 2xl:sticky 2xl:top-6 2xl:self-start">
        <?php if (! empty($slaWidgetHtml)): ?><?= $slaWidgetHtml ?><?php endif; ?>
        <?php if (! empty($citizenWidgetHtml)): ?><?= $citizenWidgetHtml ?><?php endif; ?>
        <?php if (! empty($caseWidgetHtml)): ?><?= $caseWidgetHtml ?><?php endif; ?>
        <?php if (! empty($assignmentWidgetHtml)): ?><?= $assignmentWidgetHtml ?><?php endif; ?>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-slate-400">Gestión</p>
                <h2 class="mt-1 text-xl font-black text-slate-950">Acciones operativas</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Actualiza responsable y estado sin abandonar la atención.</p>
            </div>

            <?php if ($hasAssignableUsers): ?>
                <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/assign') ?>" class="mt-6 space-y-3">
                    <?= csrf_field() ?>
                    <label for="assigned-user" class="text-sm font-bold text-slate-700">Responsable</label>
                    <select id="assigned-user" name="assigned_user_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3" required>
                        <option value="">Seleccionar operador</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= esc($user['id']) ?>" <?= (int) ($item['assigned_user_id'] ?? 0) === (int) $user['id'] ? 'selected' : '' ?>><?= esc($user['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button data-confirm="¿Asignar esta atención al responsable seleccionado?" data-loading="Asignando atención..." class="w-full rounded-xl bg-slate-950 px-4 py-3 font-bold text-white transition hover:bg-slate-800">Asignar responsable</button>
                </form>
            <?php endif; ?>

            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/status') ?>" class="<?= $hasAssignableUsers ? 'mt-6 border-t border-slate-100 pt-6' : 'mt-6' ?> space-y-3">
                <?= csrf_field() ?>
                <label for="work-item-status" class="text-sm font-bold text-slate-700">Estado de la atención</label>
                <select id="work-item-status" name="status" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= esc($status['code']) ?>" <?= $item['status'] === $status['code'] ? 'selected' : '' ?>><?= esc($status['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button data-confirm="¿Actualizar el estado de esta atención?" data-loading="Actualizando estado..." class="w-full rounded-xl bg-blue-600 px-4 py-3 font-bold text-white transition hover:bg-blue-700">Actualizar estado</button>
            </form>
        </section>
    </aside>
</div>

<script>
(() => {
    const textarea = document.getElementById('response-body');
    const counter = document.getElementById('response-count');

    if (! textarea || ! counter) {
        return;
    }

    const updateCounter = () => {
        counter.textContent = String(textarea.value.length);
    };

    document.querySelectorAll('.quick-action').forEach((button) => {
        button.addEventListener('click', () => {
            const body = button.dataset.body || '';
            textarea.value = textarea.value.trim()
                ? textarea.value.trimEnd() + '\n\n' + body
                : body;
            textarea.focus();
            updateCounter();
        });
    });

    textarea.addEventListener('input', updateCounter);
    updateCounter();
})();
</script>

<?= $this->endSection() ?>
