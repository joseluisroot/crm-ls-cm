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
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Publicación</p>
                    <p class="text-sm text-slate-600 mt-2"><?= esc($item['source']['post_message'] ?: $item['source']['external_post_id']) ?></p>
                    <?php if (! empty($item['source']['permalink_url'])): ?>
                        <a href="<?= esc($item['source']['permalink_url']) ?>" target="_blank" rel="noopener" class="inline-flex mt-3 text-sm font-bold text-pink-600">Ver en Facebook ↗</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="bg-white border border-pink-200 rounded-2xl p-6 shadow-sm shadow-pink-100/50">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-widest text-pink-600 font-black">Citizen Care Workspace</p>
                    <h2 class="text-2xl font-black text-slate-900 mt-2">Preparar respuesta</h2>
                    <p class="text-sm text-slate-500 mt-2">Usa una acción rápida o redacta una respuesta personalizada. El borrador queda asociado a esta atención.</p>
                </div>
                <?php if ($responseDraft): ?>
                    <span class="px-3 py-2 rounded-full bg-amber-50 text-amber-700 text-xs font-black">Borrador guardado</span>
                <?php endif; ?>
            </div>

            <div class="mt-6">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Quick Actions</p>
                <div class="flex flex-wrap gap-2 mt-3" id="quick-actions">
                    <?php foreach ($quickActions as $action): ?>
                        <button type="button"
                                class="quick-action px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:border-pink-300 hover:bg-pink-50 text-sm font-bold text-slate-700 transition"
                                data-command="<?= esc($action['command'], 'attr') ?>"
                                data-body="<?= esc($action['body'], 'attr') ?>">
                            <?= esc($action['command']) ?> · <?= esc($action['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/response-draft') ?>" class="mt-6">
                <?= csrf_field() ?>
                <input type="hidden" name="channel" value="<?= esc($item['channel'], 'attr') ?>">
                <label for="response-body" class="text-sm font-black text-slate-700">Respuesta al ciudadano</label>
                <textarea id="response-body" name="body" rows="8" maxlength="5000" required
                          class="mt-3 w-full rounded-2xl border border-slate-300 px-5 py-4 focus:border-pink-500 focus:ring-pink-500"
                          placeholder="Escribe / para ver acciones rápidas o redacta la respuesta..."><?= esc($responseDraft['body'] ?? '') ?></textarea>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-3">
                    <p class="text-xs text-slate-400"><span id="response-count">0</span>/5000 caracteres</p>
                    <p class="text-xs text-slate-400">Último guardado: <?= esc($responseDraft['updated_at'] ?? 'Sin guardar') ?></p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 mt-5">
                    <button class="px-5 py-3 rounded-xl bg-slate-950 text-white font-black">Guardar borrador</button>
                    <button type="button" disabled title="Se habilitará al conectar la confirmación de Meta"
                            class="px-5 py-3 rounded-xl bg-pink-300 text-white font-black cursor-not-allowed">Enviar respuesta</button>
                </div>
                <p class="text-xs text-amber-700 mt-3">El envío real se habilitará en el siguiente incremento después de validar destinatario, permisos y confirmación de Meta.</p>
            </form>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Timeline operativo</h2>
            <p class="text-sm text-slate-500 mt-1">Eventos auditables generados por el Core Event Engine.</p>
            <div class="mt-6 space-y-5">
                <?php foreach ($timeline as $event): ?>
                    <?php $payload = json_decode((string) ($event['payload_json'] ?? '{}'), true) ?: []; ?>
                    <div class="relative pl-8 border-l-2 border-slate-200 pb-2">
                        <span class="absolute -left-2 top-1 w-3.5 h-3.5 rounded-full bg-pink-600"></span>
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2">
                            <p class="font-black text-slate-800"><?= esc($event['event_name']) ?></p>
                            <p class="text-xs text-slate-400"><?= esc($event['published_at']) ?></p>
                        </div>
                        <?php if ($payload): ?>
                            <pre class="mt-3 text-xs bg-slate-950 text-slate-200 rounded-xl p-4 overflow-x-auto"><?= esc(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($timeline)): ?><p class="text-slate-500">Todavía no existen eventos para esta atención.</p><?php endif; ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?php if ($citizenCard): ?>
            <?= view('Modules\Citizen\Presentation\Views\components\citizen_card', ['citizenCard' => $citizenCard]) ?>
        <?php else: ?>
            <section class="bg-white border border-dashed border-slate-300 rounded-2xl p-6">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Contexto ciudadano</p>
                <h2 class="text-lg font-black text-slate-800 mt-2">Pendiente de vinculación</h2>
                <p class="text-sm text-slate-500 mt-2">Sin Citizen asociado a esta atención.</p>
            </section>
        <?php endif; ?>

        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Acciones operativas</h2>
            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/assign') ?>" class="mt-5 space-y-3">
                <?= csrf_field() ?>
                <label class="text-sm font-bold text-slate-700">Responsable</label>
                <select name="assigned_user_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 bg-white" required>
                    <option value="">Seleccionar operador</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= esc($user['id']) ?>" <?= (int) ($item['assigned_user_id'] ?? 0) === (int) $user['id'] ? 'selected' : '' ?>><?= esc($user['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="w-full px-4 py-3 rounded-xl bg-slate-950 text-white font-bold">Asignar</button>
            </form>

            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/status') ?>" class="mt-6 space-y-3">
                <?= csrf_field() ?>
                <label class="text-sm font-bold text-slate-700">Estado</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-4 py-3 bg-white">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= esc($status['code']) ?>" <?= $item['status'] === $status['code'] ? 'selected' : '' ?>><?= esc($status['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="w-full px-4 py-3 rounded-xl bg-blue-600 text-white font-bold">Actualizar estado</button>
            </form>

            <form method="post" action="<?= site_url('admin/operations/' . $item['id'] . '/priority') ?>" class="mt-6 space-y-3">
                <?= csrf_field() ?>
                <label class="text-sm font-bold text-slate-700">Prioridad</label>
                <select name="priority" class="w-full rounded-xl border border-slate-300 px-4 py-3 bg-white">
                    <?php foreach ($priorities as $priority): ?>
                        <option value="<?= esc($priority['code']) ?>" <?= $item['priority'] === $priority['code'] ? 'selected' : '' ?>><?= esc($priority['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="w-full px-4 py-3 rounded-xl bg-amber-500 text-slate-950 font-bold">Actualizar prioridad</button>
            </form>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Relaciones</h2>
            <dl class="mt-5 space-y-4 text-sm">
                <div><dt class="text-slate-400">Operador</dt><dd class="font-bold text-slate-800 mt-1"><?= esc($item['assigned_user_name'] ?? 'Sin asignar') ?></dd></div>
                <div><dt class="text-slate-400">Ciudadano</dt><dd class="font-bold text-slate-800 mt-1"><?php if ($item['citizen_id']): ?><a class="text-pink-600" href="<?= site_url('admin/citizens/' . $item['citizen_id']) ?>">#<?= esc($item['citizen_id']) ?></a><?php else: ?>Pendiente de vinculación<?php endif; ?></dd></div>
                <div><dt class="text-slate-400">Caso</dt><dd class="font-bold text-slate-800 mt-1"><?php if ($item['case_id']): ?><a class="text-pink-600" href="<?= site_url('admin/cases/' . $item['case_id']) ?>"><?= esc($item['case_public_code'] ?: '#' . $item['case_id']) ?></a><?php else: ?>Sin caso relacionado<?php endif; ?></dd></div>
                <div><dt class="text-slate-400">Creado</dt><dd class="font-bold text-slate-800 mt-1"><?= esc($item['created_at']) ?></dd></div>
            </dl>
        </section>
    </aside>
</div>

<script>
(() => {
    const textarea = document.getElementById('response-body');
    const counter = document.getElementById('response-count');
    if (!textarea || !counter) return;

    const updateCounter = () => counter.textContent = String(textarea.value.length);
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
