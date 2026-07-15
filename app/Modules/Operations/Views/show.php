<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 mb-8">
    <div>
        <a href="<?= site_url('admin/operations') ?>" class="text-sm font-bold text-pink-600">← Volver a la cola</a>
        <h1 class="text-3xl font-black text-slate-900 mt-3">Atención #<?= esc($item['id']) ?></h1>
        <p class="text-slate-500 mt-2"><?= esc($item['uuid']) ?></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <span class="px-3 py-2 rounded-full bg-pink-50 text-pink-700 text-xs font-black"><?= esc($item['channel']) ?></span>
        <span class="px-3 py-2 rounded-full bg-slate-100 text-slate-700 text-xs font-black"><?= esc($item['status_name']) ?></span>
        <span class="px-3 py-2 rounded-full bg-amber-50 text-amber-700 text-xs font-black"><?= esc($item['priority_name']) ?></span>
    </div>
</div>

<div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
    <div class="2xl:col-span-2 space-y-6">
        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Interacción de origen</p>
            <h2 class="text-xl font-black text-slate-900 mt-3"><?= esc($item['source']['author_name'] ?? $item['title']) ?></h2>
            <p class="text-slate-700 mt-4 whitespace-pre-line"><?= esc($item['source']['message'] ?? $item['summary'] ?? 'Sin contenido disponible.') ?></p>
            <?php if (! empty($item['source'])): ?>
                <div class="mt-6 rounded-xl bg-slate-50 border border-slate-200 p-5">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Contexto</p>
                    <p class="text-sm text-slate-600 mt-2"><?= esc($item['source']['post_message'] ?? $item['source']['external_message_id'] ?? $item['origin_id']) ?></p>
                    <?php if (! empty($item['source']['permalink_url'])): ?><a href="<?= esc($item['source']['permalink_url']) ?>" target="_blank" rel="noopener" class="inline-flex mt-3 text-sm font-bold text-pink-600">Ver en Facebook ↗</a><?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="bg-white border border-pink-200 rounded-2xl p-6 shadow-sm shadow-pink-100/50">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div><p class="text-xs uppercase tracking-widest text-pink-600 font-black">Citizen Care Workspace</p><h2 class="text-2xl font-black text-slate-900 mt-2">Responder al ciudadano</h2><p class="text-sm text-slate-500 mt-2">Redacta, guarda o envía desde CIAC. La atención solo se marcará respondida después de la confirmación de Meta.</p></div>
                <?php if ($responseDraft): ?><span class="px-3 py-2 rounded-full bg-amber-50 text-amber-700 text-xs font-black">Borrador guardado</span><?php endif; ?>
            </div>
            <div class="mt-6"><p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Quick Actions</p><div class="flex flex-wrap gap-2 mt-3"><?php foreach ($quickActions as $action): ?><button type="button" class="quick-action px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:border-pink-300 hover:bg-pink-50 text-sm font-bold text-slate-700 transition" data-body="<?= esc($action['body'], 'attr') ?>"><?= esc($action['command']) ?> · <?= esc($action['label']) ?></button><?php endforeach; ?></div></div>
            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/response-draft') ?>" class="mt-6" id="response-form">
                <?= csrf_field() ?><input type="hidden" name="channel" value="<?= esc($item['channel'], 'attr') ?>">
                <label for="response-body" class="text-sm font-black text-slate-700">Respuesta</label>
                <textarea id="response-body" name="body" rows="8" maxlength="5000" required class="mt-3 w-full rounded-2xl border border-slate-300 px-5 py-4 focus:border-pink-500 focus:ring-pink-500" placeholder="Escribe / para usar acciones rápidas o redacta la respuesta..."><?= esc($responseDraft['body'] ?? '') ?></textarea>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-3"><p class="text-xs text-slate-400"><span id="response-count">0</span>/5000 caracteres</p><p class="text-xs text-slate-400">Último guardado: <?= esc($responseDraft['updated_at'] ?? 'Sin guardar') ?></p></div>
                <div class="flex flex-col sm:flex-row gap-3 mt-5"><button class="px-5 py-3 rounded-xl bg-slate-950 text-white font-black">Guardar borrador</button><?php if ($responseCapability['ready']): ?><button formaction="<?= site_url('admin/operations/' . $item['id'] . '/response-send') ?>" data-confirm="¿Enviar esta respuesta por <?= esc($responseCapability['channel'], 'attr') ?>?" data-loading="Enviando respuesta..." class="px-5 py-3 rounded-xl bg-pink-600 text-white font-black">Enviar por <?= esc($responseCapability['channel']) ?></button><?php else: ?><button type="button" disabled class="px-5 py-3 rounded-xl bg-pink-300 text-white font-black cursor-not-allowed">Enviar respuesta</button><?php endif; ?></div>
                <?php if (! $responseCapability['ready']): ?><p class="text-xs text-amber-700 mt-3"><?= esc($responseCapability['reason']) ?></p><?php endif; ?>
            </form>
        </section>

        <?php if (! empty($responses)): ?><section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm"><h2 class="text-xl font-black text-slate-900">Historial de respuestas</h2><div class="mt-5 space-y-4"><?php foreach ($responses as $response): ?><div class="rounded-xl border border-slate-200 p-4"><div class="flex items-center justify-between gap-3"><strong><?= esc($response['channel']) ?></strong><span class="text-xs font-black <?= $response['status'] === 'SENT' ? 'text-green-600' : 'text-red-600' ?>"><?= esc($response['status']) ?></span></div><p class="text-sm text-slate-700 mt-3 whitespace-pre-line"><?= esc($response['body']) ?></p><p class="text-xs text-slate-400 mt-3">Meta ID: <?= esc($response['external_response_id'] ?? '-') ?> · <?= esc($response['sent_at'] ?? $response['created_at']) ?></p><?php if (! empty($response['error_message'])): ?><p class="text-xs text-red-600 mt-2"><?= esc($response['error_message']) ?></p><?php endif; ?></div><?php endforeach; ?></div></section><?php endif; ?>

        <?php if (! empty($activityWidgetHtml)): ?><?= $activityWidgetHtml ?><?php endif; ?>
        <?php if (! empty($timelineWidgetHtml)): ?><?= $timelineWidgetHtml ?><?php endif; ?>
    </div>

    <aside class="space-y-6">
        <?php if (! empty($slaWidgetHtml)): ?><?= $slaWidgetHtml ?><?php endif; ?>
        <?php if (! empty($citizenWidgetHtml)): ?><?= $citizenWidgetHtml ?><?php endif; ?>
        <?php if (! empty($caseWidgetHtml)): ?><?= $caseWidgetHtml ?><?php endif; ?>
        <?php if (! empty($assignmentWidgetHtml)): ?><?= $assignmentWidgetHtml ?><?php endif; ?>
        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Acciones operativas</h2>
            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/assign') ?>" class="mt-5 space-y-3"><?= csrf_field() ?><label class="text-sm font-bold text-slate-700">Responsable</label><select name="assigned_user_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 bg-white" required><option value="">Seleccionar operador</option><?php foreach ($users as $user): ?><option value="<?= esc($user['id']) ?>" <?= (int) ($item['assigned_user_id'] ?? 0) === (int) $user['id'] ? 'selected' : '' ?>><?= esc($user['name']) ?></option><?php endforeach; ?></select><button class="w-full px-4 py-3 rounded-xl bg-slate-950 text-white font-bold">Asignar</button></form>
            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/status') ?>" class="mt-6 space-y-3"><?= csrf_field() ?><label class="text-sm font-bold text-slate-700">Estado</label><select name="status" class="w-full rounded-xl border border-slate-300 px-4 py-3 bg-white"><?php foreach ($statuses as $status): ?><option value="<?= esc($status['code']) ?>" <?= $item['status'] === $status['code'] ? 'selected' : '' ?>><?= esc($status['name']) ?></option><?php endforeach; ?></select><button class="w-full px-4 py-3 rounded-xl bg-blue-600 text-white font-bold">Actualizar estado</button></form>
        </section>
    </aside>
</div>

<script>(() => { const textarea = document.getElementById('response-body'); const counter = document.getElementById('response-count'); if (!textarea || !counter) return; const updateCounter = () => counter.textContent = String(textarea.value.length); document.querySelectorAll('.quick-action').forEach((button) => button.addEventListener('click', () => { const body = button.dataset.body || ''; textarea.value = textarea.value.trim() ? textarea.value.trimEnd() + '\n\n' + body : body; textarea.focus(); updateCounter(); })); textarea.addEventListener('input', updateCounter); updateCounter(); })();</script>

<?= $this->endSection() ?>
