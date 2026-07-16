<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$sourceAuthor = $item['source']['author_name'] ?? $item['title'] ?? 'Ciudadano';
$sourceMessage = $item['source']['message'] ?? $item['summary'] ?? 'Sin contenido disponible.';
$sourceContext = $item['source']['post_message'] ?? $item['source']['external_message_id'] ?? $item['origin_id'] ?? null;
$permalink = $item['source']['permalink_url'] ?? null;
$hasAssignableUsers = ! empty($users);
?>

<header class="ciac-page-header">
    <div>
        <a href="<?= site_url('admin/operations') ?>" class="ciac-btn ciac-btn--ghost ciac-btn--sm">← Volver a la cola</a>
        <p class="ciac-page-eyebrow mt-4">Citizen Care Workspace</p>
        <h1 class="ciac-page-title mt-2">Atención #<?= esc($item['id']) ?></h1>
        <p class="ciac-page-description break-all"><?= esc($item['uuid']) ?></p>
    </div>
    <div class="ciac-actions">
        <span class="ciac-badge ciac-badge--primary"><?= esc($item['channel']) ?></span>
        <span class="ciac-badge ciac-badge--neutral"><?= esc($item['status_name']) ?></span>
        <span class="ciac-badge ciac-badge--warning"><?= esc($item['priority_name']) ?></span>
    </div>
</header>

<div class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1fr)_390px]">
    <main class="min-w-0 space-y-6">
        <section class="ciac-card overflow-hidden">
            <header class="ciac-card__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="ciac-page-eyebrow text-slate-400">Interacción de origen</p>
                    <h2 class="ciac-card__title mt-2"><?= esc($sourceAuthor) ?></h2>
                </div>
                <?php if ($permalink): ?>
                    <a href="<?= esc($permalink) ?>" target="_blank" rel="noopener" class="ciac-btn ciac-btn--outline ciac-btn--sm">Ver en Facebook ↗</a>
                <?php endif; ?>
            </header>
            <div class="ciac-card__body">
                <div class="rounded-2xl bg-slate-50 px-5 py-5 text-[15px] leading-7 text-slate-700 whitespace-pre-line"><?= esc($sourceMessage) ?></div>
                <?php if ($sourceContext): ?>
                    <div class="mt-5 border-l-4 border-pink-200 pl-4">
                        <p class="ciac-page-eyebrow text-slate-400">Contexto de publicación</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600"><?= esc($sourceContext) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="ciac-card border-pink-200 shadow-pink-100/60">
            <header class="ciac-card__header flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="ciac-page-eyebrow">Centro de respuesta</p>
                    <h2 class="ciac-card__title mt-2 text-2xl">Responder al ciudadano</h2>
                    <p class="ciac-card__subtitle max-w-2xl leading-6">Redacta, guarda o envía desde CIAC. La atención solo se marcará respondida cuando el canal confirme el envío.</p>
                </div>
                <?php if ($responseDraft): ?><span class="ciac-badge ciac-badge--warning">Borrador guardado</span><?php endif; ?>
            </header>

            <div class="ciac-card__body">
                <?php if (! empty($quickActions)): ?>
                    <div>
                        <p class="ciac-page-eyebrow text-slate-400">Respuestas rápidas</p>
                        <div class="ciac-actions mt-3">
                            <?php foreach ($quickActions as $action): ?>
                                <button type="button" class="quick-action ciac-btn ciac-btn--outline ciac-btn--sm" data-body="<?= esc($action['body'], 'attr') ?>"><?= esc($action['command']) ?> · <?= esc($action['label']) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/response-draft') ?>" class="mt-6" id="response-form" data-loading="Guardando borrador...">
                    <?= csrf_field() ?>
                    <input type="hidden" name="channel" value="<?= esc($item['channel'], 'attr') ?>">
                    <label for="response-body" class="ciac-label">Mensaje de respuesta</label>
                    <textarea id="response-body" name="body" rows="8" maxlength="5000" required class="ciac-textarea leading-7" placeholder="Escribe / para usar acciones rápidas o redacta la respuesta..."><?= esc($responseDraft['body'] ?? '') ?></textarea>
                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="ciac-help"><span id="response-count">0</span>/5000 caracteres</p>
                        <p class="ciac-help">Último guardado: <?= esc($responseDraft['updated_at'] ?? 'Sin guardar') ?></p>
                    </div>
                    <div class="ciac-actions mt-5">
                        <button class="ciac-btn ciac-btn--secondary">Guardar borrador</button>
                        <?php if ($responseCapability['ready']): ?>
                            <button formaction="<?= site_url('admin/operations/' . $item['id'] . '/response-send') ?>" data-confirm="¿Enviar esta respuesta por <?= esc($responseCapability['channel'], 'attr') ?>?" data-loading="Enviando respuesta..." class="ciac-btn ciac-btn--primary">Enviar por <?= esc($responseCapability['channel']) ?></button>
                        <?php else: ?>
                            <button type="button" disabled class="ciac-btn ciac-btn--primary">Enviar respuesta</button>
                        <?php endif; ?>
                    </div>
                    <?php if (! $responseCapability['ready']): ?><p class="ciac-error rounded-xl bg-amber-50 px-4 py-3 text-amber-700"><?= esc($responseCapability['reason']) ?></p><?php endif; ?>
                </form>
            </div>
        </section>

        <?php if (! empty($responses)): ?>
            <details class="ciac-card group">
                <summary class="ciac-card__header flex cursor-pointer list-none items-center justify-between gap-4">
                    <div><p class="ciac-page-eyebrow text-slate-400">Historial</p><h2 class="ciac-card__title mt-1">Respuestas registradas</h2></div>
                    <span class="ciac-badge ciac-badge--neutral"><?= count($responses) ?> respuestas</span>
                </summary>
                <div class="ciac-card__body space-y-4">
                    <?php foreach ($responses as $response): ?>
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <strong class="text-sm text-slate-900"><?= esc($response['channel']) ?></strong>
                                <span class="ciac-badge <?= $response['status'] === 'SENT' ? 'ciac-badge--success' : 'ciac-badge--danger' ?>"><?= esc($response['status']) ?></span>
                            </div>
                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700"><?= esc($response['body']) ?></p>
                            <p class="ciac-help break-all">Meta ID: <?= esc($response['external_response_id'] ?? '-') ?> · <?= esc($response['sent_at'] ?? $response['created_at']) ?></p>
                            <?php if (! empty($response['error_message'])): ?><p class="ciac-error rounded-lg bg-red-50 px-3 py-2"><?= esc($response['error_message']) ?></p><?php endif; ?>
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

        <section class="ciac-card">
            <header class="ciac-card__header">
                <p class="ciac-page-eyebrow text-slate-400">Gestión</p>
                <h2 class="ciac-card__title mt-1">Acciones operativas</h2>
                <p class="ciac-card__subtitle leading-6">Actualiza responsable y estado sin abandonar la atención.</p>
            </header>
            <div class="ciac-card__body">
                <?php if ($hasAssignableUsers): ?>
                    <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/assign') ?>" data-confirm="¿Asignar esta atención al responsable seleccionado?" data-loading="Asignando atención...">
                        <?= csrf_field() ?>
                        <label for="assigned-user" class="ciac-label">Responsable</label>
                        <select id="assigned-user" name="assigned_user_id" class="ciac-select" required>
                            <option value="">Seleccionar operador</option>
                            <?php foreach ($users as $user): ?><option value="<?= esc($user['id']) ?>" <?= (int) ($item['assigned_user_id'] ?? 0) === (int) $user['id'] ? 'selected' : '' ?>><?= esc($user['name']) ?></option><?php endforeach; ?>
                        </select>
                        <button class="ciac-btn ciac-btn--secondary ciac-btn--block mt-3">Asignar responsable</button>
                    </form>
                <?php endif; ?>

                <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/status') ?>" class="<?= $hasAssignableUsers ? 'mt-6 border-t border-slate-100 pt-6' : '' ?>" data-confirm="¿Actualizar el estado de esta atención?" data-loading="Actualizando estado...">
                    <?= csrf_field() ?>
                    <label for="work-item-status" class="ciac-label">Estado de la atención</label>
                    <select id="work-item-status" name="status" class="ciac-select">
                        <?php foreach ($statuses as $status): ?><option value="<?= esc($status['code']) ?>" <?= $item['status'] === $status['code'] ? 'selected' : '' ?>><?= esc($status['name']) ?></option><?php endforeach; ?>
                    </select>
                    <button class="ciac-btn ciac-btn--primary ciac-btn--block mt-3">Actualizar estado</button>
                </form>
            </div>
        </section>
    </aside>
</div>

<script>
(() => {
    const textarea = document.getElementById('response-body');
    const counter = document.getElementById('response-count');
    if (! textarea || ! counter) return;
    const updateCounter = () => { counter.textContent = String(textarea.value.length); };
    document.querySelectorAll('.quick-action').forEach((button) => {
        button.addEventListener('click', () => {
            const body = button.dataset.body || '';
            textarea.value = textarea.value.trim() ? textarea.value.trimEnd() + '\n\n' + body : body;
            textarea.focus();
            updateCounter();
        });
    });
    textarea.addEventListener('input', updateCounter);
    updateCounter();
})();
</script>

<?= $this->endSection() ?>