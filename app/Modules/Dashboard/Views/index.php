<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$quickActions = '<a href="' . site_url('admin/operations') . '" class="ciac-btn ciac-btn--primary">Abrir operaciones</a>'
    . '<a href="' . site_url('admin/cases') . '" class="ciac-btn ciac-btn--outline">Ver casos</a>';
?>

<?= view('Modules\Shared\Views\components\page_header', [
    'eyebrow' => 'Operational Intelligence',
    'title' => 'Dashboard CIAC',
    'description' => 'Resumen ejecutivo de atención ciudadana, conversaciones, mensajes y casos.',
    'actionsHtml' => $quickActions,
]) ?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    <?= view('Modules\Shared\Views\components\kpi_card', [
        'label' => 'Ciudadanos',
        'value' => $totalCitizens,
        'help' => $citizensToday . ' registrados hoy',
        'tone' => 'blue',
    ]) ?>
    <?= view('Modules\Shared\Views\components\kpi_card', [
        'label' => 'Conversaciones',
        'value' => $totalConversations,
        'help' => 'Conversaciones registradas',
        'tone' => 'violet',
    ]) ?>
    <?= view('Modules\Shared\Views\components\kpi_card', [
        'label' => 'Mensajes',
        'value' => $totalMessages,
        'help' => $messagesToday . ' registrados hoy',
        'tone' => 'pink',
    ]) ?>
    <?= view('Modules\Shared\Views\components\kpi_card', [
        'label' => 'Casos abiertos',
        'value' => $openCases,
        'help' => $closedCasesToday . ' cerrados hoy',
        'tone' => 'amber',
    ]) ?>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <section class="xl:col-span-2 ciac-card overflow-hidden">
        <?= view('Modules\Shared\Views\components\section_header', [
            'title' => 'Pulso operativo',
            'subtitle' => 'Indicadores que requieren atención o seguimiento inmediato.',
        ]) ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
            <a href="<?= site_url('admin/cases?priority=high') ?>" class="rounded-2xl border border-red-100 bg-red-50 p-5 transition hover:border-red-300 hover:shadow-sm">
                <span class="ciac-badge bg-red-100 text-red-700">Alta prioridad</span>
                <p class="mt-4 text-3xl font-black text-slate-900"><?= esc($highPriorityOpenCases) ?></p>
                <p class="mt-2 text-sm text-slate-600">Casos abiertos con prioridad alta o urgente.</p>
            </a>

            <a href="<?= site_url('admin/cases?assignment=unassigned') ?>" class="rounded-2xl border border-amber-100 bg-amber-50 p-5 transition hover:border-amber-300 hover:shadow-sm">
                <span class="ciac-badge bg-amber-100 text-amber-700">Sin responsable</span>
                <p class="mt-4 text-3xl font-black text-slate-900"><?= esc($unassignedOpenCases) ?></p>
                <p class="mt-2 text-sm text-slate-600">Casos abiertos pendientes de asignación.</p>
            </a>

            <a href="<?= site_url('admin/cases') ?>" class="rounded-2xl border border-green-100 bg-green-50 p-5 transition hover:border-green-300 hover:shadow-sm">
                <span class="ciac-badge bg-green-100 text-green-700">Cerrados hoy</span>
                <p class="mt-4 text-3xl font-black text-slate-900"><?= esc($closedCasesToday) ?></p>
                <p class="mt-2 text-sm text-slate-600">Casos finalizados durante la jornada actual.</p>
            </a>
        </div>
    </section>

    <aside class="ciac-card overflow-hidden">
        <?= view('Modules\Shared\Views\components\section_header', [
            'title' => 'Accesos rápidos',
            'subtitle' => 'Navegación directa a los principales centros operativos.',
        ]) ?>

        <nav class="p-6 space-y-3" aria-label="Accesos rápidos del dashboard">
            <?php foreach ([
                ['label' => 'Bandeja de operaciones', 'description' => 'Atender y distribuir interacciones.', 'url' => site_url('admin/operations')],
                ['label' => 'Centro de casos', 'description' => 'Filtrar, priorizar y supervisar casos.', 'url' => site_url('admin/cases')],
                ['label' => 'Conversaciones', 'description' => 'Consultar el historial ciudadano.', 'url' => site_url('admin/conversations')],
                ['label' => 'Engagement', 'description' => 'Revisar comentarios y reacciones.', 'url' => site_url('admin/engagement')],
            ] as $item): ?>
                <a href="<?= esc($item['url']) ?>" class="block rounded-2xl border border-slate-200 p-4 transition hover:border-pink-300 hover:bg-pink-50/40">
                    <p class="font-black text-slate-900"><?= esc($item['label']) ?></p>
                    <p class="mt-1 text-sm text-slate-500"><?= esc($item['description']) ?></p>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
</div>

<?= $this->endSection() ?>
