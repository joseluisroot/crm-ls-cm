<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Citizen Profile</p>
        <h1 class="text-3xl font-black text-slate-900 mt-2"><?= esc($citizen['name']) ?></h1>
        <p class="text-slate-500 mt-2">Contexto operativo e historia consolidada del ciudadano.</p>
    </div>

    <a href="<?= site_url('admin/citizens') ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50">
        ← Volver a ciudadanos
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    <?php foreach ([
        ['label' => 'Work Items', 'value' => $timeline->metrics['total_work_items'] ?? 0],
        ['label' => 'Pendientes', 'value' => $timeline->metrics['open_work_items'] ?? 0],
        ['label' => 'Casos', 'value' => $timeline->metrics['total_cases'] ?? 0],
        ['label' => 'Identidades', 'value' => $timeline->metrics['total_identities'] ?? 0],
    ] as $metric): ?>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500"><?= esc($metric['label']) ?></p>
            <p class="text-4xl font-black text-slate-900 mt-3"><?= esc($metric['value']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <aside class="space-y-6">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-black text-slate-900">Información general</h2>
            <dl class="mt-5 space-y-4 text-sm">
                <div>
                    <dt class="font-bold text-slate-400 uppercase tracking-wider text-xs">Facebook ID</dt>
                    <dd class="text-slate-700 mt-1 break-all"><?= esc($citizen['facebook_id'] ?? 'No disponible') ?></dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-400 uppercase tracking-wider text-xs">Municipio</dt>
                    <dd class="text-slate-700 mt-1"><?= esc($citizen['municipality'] ?? 'No registrado') ?></dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-400 uppercase tracking-wider text-xs">Comunidad</dt>
                    <dd class="text-slate-700 mt-1"><?= esc($citizen['community'] ?? 'No registrada') ?></dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-400 uppercase tracking-wider text-xs">Estado</dt>
                    <dd class="mt-1"><span class="inline-flex px-3 py-1 rounded-full bg-green-50 text-green-700 font-bold"><?= esc($citizen['status'] ?? 'active') ?></span></dd>
                </div>
            </dl>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-black text-slate-900">Identidades vinculadas</h2>
            <div class="mt-4 space-y-3">
                <?php foreach ($identities as $identity): ?>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-black text-slate-800"><?= esc($identity['channel']) ?></span>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700"><?= esc($identity['confidence']) ?>%</span>
                        </div>
                        <p class="text-sm text-slate-600 mt-2"><?= esc($identity['display_name'] ?: 'Sin nombre') ?></p>
                        <p class="text-xs text-slate-400 mt-1 break-all"><?= esc($identity['external_id']) ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($identities)): ?>
                    <p class="text-sm text-slate-500">No hay identidades sociales vinculadas.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-black text-slate-900">Relaciones actuales</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <p class="text-sm font-bold text-slate-500 mb-2">Conversaciones</p>
                    <?php foreach ($conversations as $conversation): ?>
                        <a href="<?= site_url('admin/conversations/' . $conversation['id']) ?>" class="block py-2 text-sm font-bold text-pink-600 hover:text-pink-700">
                            Conversación #<?= esc($conversation['id']) ?> · <?= esc($conversation['status']) ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($conversations)): ?><p class="text-sm text-slate-400">Sin conversaciones.</p><?php endif; ?>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <p class="text-sm font-bold text-slate-500 mb-2">Casos</p>
                    <?php foreach ($cases as $case): ?>
                        <a href="<?= site_url('admin/cases/' . $case['id']) ?>" class="block py-2 text-sm font-bold text-pink-600 hover:text-pink-700">
                            <?= esc($case['title']) ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($cases)): ?><p class="text-sm text-slate-400">Sin casos.</p><?php endif; ?>
                </div>
            </div>
        </section>
    </aside>

    <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <p class="text-xs uppercase tracking-widest text-pink-600 font-black">Citizen Timeline</p>
            <h2 class="text-2xl font-black text-slate-900 mt-2">Historia del ciudadano</h2>
            <p class="text-sm text-slate-500 mt-1">Work Items y eventos internos ordenados cronológicamente.</p>
        </div>

        <div class="p-6">
            <div class="relative border-l-2 border-slate-200 ml-3 space-y-8">
                <?php foreach ($timeline->items as $item): ?>
                    <?php $isWorkItem = $item->type === 'WORK_ITEM'; ?>
                    <article class="relative pl-8">
                        <span class="absolute -left-[11px] top-1 h-5 w-5 rounded-full border-4 border-white <?= $isWorkItem ? 'bg-pink-600' : 'bg-slate-500' ?>"></span>
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                            <div>
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-black <?= $isWorkItem ? 'bg-pink-50 text-pink-700' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= esc($item->type) ?>
                                </span>
                                <h3 class="text-base font-black text-slate-900 mt-2"><?= esc($item->title) ?></h3>
                                <?php if ($item->description): ?>
                                    <p class="text-sm text-slate-600 mt-1 whitespace-pre-line"><?= esc($item->description) ?></p>
                                <?php endif; ?>

                                <?php if (! empty($item->metadata['url'])): ?>
                                    <a href="<?= esc($item->metadata['url']) ?>" class="inline-flex mt-3 text-sm font-bold text-pink-600 hover:text-pink-700">Abrir Work Item →</a>
                                <?php endif; ?>
                            </div>
                            <time class="text-xs font-bold text-slate-400 whitespace-nowrap"><?= esc($item->occurredAt) ?></time>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if (empty($timeline->items)): ?>
                    <div class="pl-8 py-8">
                        <p class="font-bold text-slate-700">Aún no hay actividad en el Timeline.</p>
                        <p class="text-sm text-slate-500 mt-1">Las futuras interacciones y operaciones aparecerán aquí.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?= $this->endSection() ?>
