<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900">
            Centro de Notificaciones
        </h1>

        <p class="text-slate-500 mt-2">
            Alertas internas generadas por la actividad ciudadana y el seguimiento de casos.
        </p>
    </div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-100 text-green-700 rounded-xl p-4 mb-5 text-sm">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <?php if (empty($notifications)): ?>
            <div class="p-8 text-center text-slate-500">
                Aún no hay notificaciones registradas.
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($notifications as $notification): ?>
                    <div class="p-6 flex items-start justify-between gap-6 <?= $notification['status'] === 'pending' ? 'bg-pink-50/40' : '' ?>">

                        <div class="flex gap-4">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center <?= $notification['status'] === 'pending' ? 'bg-pink-600 text-white' : 'bg-slate-100 text-slate-500' ?>">
                                🔔
                            </div>

                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="font-bold text-slate-900">
                                        <?= esc($notification['subject'] ?? 'Notificación') ?>
                                    </h3>

                                    <span class="text-xs px-3 py-1 rounded-full <?= $notification['status'] === 'pending' ? 'bg-pink-100 text-pink-700' : 'bg-slate-100 text-slate-500' ?>">
                                    <?= esc($notification['status']) ?>
                                </span>
                                </div>

                                <p class="text-slate-600 mt-2">
                                    <?= esc($notification['body'] ?? '') ?>
                                </p>

                                <p class="text-xs text-slate-400 mt-3">
                                    <?= esc($notification['created_at']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <?php
                            $payload = json_decode($notification['payload'] ?? '{}', true) ?: [];
                            $caseId = $payload['case_id'] ?? null;
                            ?>

                            <?php if ($caseId): ?>
                                <a href="/admin/cases/<?= esc($caseId) ?>"
                                   class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                                    Ver caso
                                </a>
                            <?php endif; ?>

                            <?php if ($notification['status'] !== 'read'): ?>
                                <form method="post" action="/admin/notifications/<?= esc($notification['id']) ?>/read">
                                    <?= csrf_field() ?>
                                    <button class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">
                                        Marcar leída
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?= $this->endSection() ?>