<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<?php
$citizens = $analytics['citizens'];
$conversations = $analytics['conversations'];
$messages = $analytics['messages'];
$cases = $analytics['cases'];
$indices = $analytics['indices'];
$distribution = $analytics['distribution'];
$trends = $analytics['trends'];

$healthScore = (float) $indices['ciac_health_score'];
$trustIndex = (float) $indices['citizen_trust_index'];
$listeningIndex = (float) $indices['listening_effectiveness'];

$scoreLabel = static function (float $score): string {
    return match (true) {
        $score >= 80 => 'Excelente',
        $score >= 65 => 'Saludable',
        $score >= 50 => 'En desarrollo',
        $score >= 30 => 'Requiere atención',
        default => 'Crítico',
    };
};
?>

    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
                    CIAC Intelligence Center
                </p>

                <h1 class="text-3xl md:text-4xl font-black text-slate-900 mt-2">
                    Centro de Inteligencia Ciudadana
                </h1>

                <p class="text-slate-500 mt-3 max-w-3xl">
                    Indicadores operativos para comprender la participación,
                    priorizar la atención y fortalecer la relación con la ciudadanía.
                </p>
            </div>

            <div class="bg-slate-950 text-white rounded-2xl px-6 py-5 min-w-[230px]">
                <p class="text-xs uppercase tracking-widest text-slate-400">
                    Salud general CIAC
                </p>

                <div class="flex items-end gap-2 mt-2">
                <span class="text-4xl font-black">
                    <?= number_format($healthScore, 1) ?>
                </span>

                    <span class="text-slate-400 mb-1">
                    / 100
                </span>
                </div>

                <p class="text-sm text-pink-300 mt-2">
                    <?= esc($scoreLabel($healthScore)) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Ciudadanos registrados
            </p>

            <p class="text-4xl font-black text-slate-900 mt-3">
                <?= number_format($citizens['total']) ?>
            </p>

            <p class="text-sm text-green-600 mt-3">
                +<?= number_format($citizens['new_today']) ?> hoy
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Mensajes de hoy
            </p>

            <p class="text-4xl font-black text-slate-900 mt-3">
                <?= number_format($messages['today']) ?>
            </p>

            <p class="text-sm text-slate-500 mt-3">
                Interacciones del canal
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Casos abiertos
            </p>

            <p class="text-4xl font-black text-slate-900 mt-3">
                <?= number_format($cases['open']) ?>
            </p>

            <p class="text-sm text-amber-600 mt-3">
                <?= number_format($cases['unassigned']) ?> sin responsable
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Casos atendidos
            </p>

            <p class="text-4xl font-black text-slate-900 mt-3">
                <?= number_format($cases['resolved']) ?>
            </p>

            <p class="text-sm text-green-600 mt-3">
                Seguimiento completado
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <p class="text-sm font-bold text-slate-500">
                Efectividad de escucha
            </p>

            <p class="text-4xl font-black text-slate-900 mt-3">
                <?= number_format($listeningIndex, 1) ?>%
            </p>

            <div class="h-3 bg-slate-100 rounded-full mt-5 overflow-hidden">
                <div
                    class="h-full bg-pink-600 rounded-full"
                    style="width: <?= min(100, $listeningIndex) ?>%"
                ></div>
            </div>

            <p class="text-sm text-slate-500 mt-4">
                Relación entre mensajes recibidos y respuestas efectivamente enviadas.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <p class="text-sm font-bold text-slate-500">
                Índice de Confianza Ciudadana
            </p>

            <p class="text-4xl font-black text-slate-900 mt-3">
                <?= number_format($trustIndex, 1) ?>
                <span class="text-lg text-slate-400">/100</span>
            </p>

            <div class="h-3 bg-slate-100 rounded-full mt-5 overflow-hidden">
                <div
                    class="h-full bg-pink-600 rounded-full"
                    style="width: <?= min(100, $trustIndex) ?>%"
                ></div>
            </div>

            <p class="text-sm text-slate-500 mt-4">
                Índice interno basado en participación, recurrencia,
                finalización de procesos y respuesta del equipo.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <p class="text-sm font-bold text-slate-500">
                Ciudadanos recurrentes
            </p>

            <p class="text-4xl font-black text-slate-900 mt-3">
                <?= number_format($citizens['recurring']) ?>
            </p>

            <p class="text-sm text-slate-500 mt-4">
                Personas que han iniciado más de una conversación en el CIAC.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-xl font-black text-slate-900">
                    Casos por estado
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Situación actual del proceso de atención.
                </p>
            </div>

            <?php if (empty($distribution['by_status'])): ?>
                <p class="text-slate-500">No hay datos disponibles.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($distribution['by_status'] as $row): ?>
                        <?php
                        $totalCases = max(1, (int) $cases['total']);
                        $percentage = ((int) $row['total'] / $totalCases) * 100;
                        ?>

                        <div>
                            <div class="flex justify-between gap-4 text-sm">
                            <span class="font-semibold text-slate-700">
                                <?= esc($row['name']) ?>
                            </span>

                                <span class="font-bold text-slate-900">
                                <?= number_format($row['total']) ?>
                            </span>
                            </div>

                            <div class="h-2 bg-slate-100 rounded-full mt-2 overflow-hidden">
                                <div
                                    class="h-full bg-slate-800 rounded-full"
                                    style="width: <?= min(100, $percentage) ?>%"
                                ></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-xl font-black text-slate-900">
                    Principales categorías
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Temas ciudadanos que requieren mayor atención.
                </p>
            </div>

            <?php if (empty($distribution['by_category'])): ?>
                <p class="text-slate-500">No hay datos disponibles.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($distribution['by_category'] as $row): ?>
                        <div class="flex items-center justify-between gap-4 p-4 bg-slate-50 rounded-xl">
                        <span class="font-semibold text-slate-700">
                            <?= esc($row['name'] ?? 'Sin clasificar') ?>
                        </span>

                            <span class="font-black text-slate-900">
                            <?= number_format($row['total']) ?>
                        </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">
                Actividad por municipio
            </h2>

            <p class="text-sm text-slate-500 mt-1 mb-5">
                Municipios registrados con mayor cantidad de casos.
            </p>

            <?php if (empty($distribution['by_municipality'])): ?>
                <p class="text-slate-500">
                    Aún no existen municipios vinculados a los ciudadanos.
                </p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($distribution['by_municipality'] as $row): ?>
                        <div class="flex justify-between p-4 bg-slate-50 rounded-xl">
                        <span class="font-semibold text-slate-700">
                            <?= esc($row['municipality']) ?>
                        </span>

                            <span class="font-black text-slate-900">
                            <?= number_format($row['total']) ?>
                        </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">
                Carga por responsable
            </h2>

            <p class="text-sm text-slate-500 mt-1 mb-5">
                Distribución de los casos actualmente asignados.
            </p>

            <?php if (empty($distribution['by_responsible'])): ?>
                <p class="text-slate-500">
                    Aún no existen casos asignados.
                </p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($distribution['by_responsible'] as $row): ?>
                        <div class="flex justify-between p-4 bg-slate-50 rounded-xl">
                        <span class="font-semibold text-slate-700">
                            <?= esc($row['name'] ?? 'Sin responsable') ?>
                        </span>

                            <span class="font-black text-slate-900">
                            <?= number_format($row['total']) ?>
                        </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-8 p-5 bg-blue-50 border border-blue-200 rounded-2xl">
        <p class="font-bold text-blue-900">
            Nota metodológica
        </p>

        <p class="text-sm text-blue-800 mt-2">
            El Índice de Confianza Ciudadana refleja exclusivamente la relación
            observada entre las personas que interactúan con el CIAC. No representa
            intención de voto ni aceptación electoral de toda la población.
        </p>
    </div>

<?= $this->endSection() ?>