<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

<div class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
    <div>
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">Citizen Performance Center</p>
        <h1 class="text-3xl font-black text-slate-900 mt-2">Desempeño de atención</h1>
        <p class="text-slate-500 mt-2">Alcance: <?= esc($scopeLabel) ?>. Métricas de carga, primera respuesta y cumplimiento de SLA.</p>
    </div>
    <p class="text-xs text-slate-400">Actualizado: <?= esc(date('Y-m-d H:i:s')) ?></p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-8">
    <?php foreach ([
        ['Operadores', $summary['operators'], 'bg-slate-100 text-slate-700'],
        ['Atenciones abiertas', $summary['open'], 'bg-blue-50 text-blue-700'],
        ['Próximas a vencer', $summary['due_soon'], 'bg-amber-50 text-amber-700'],
        ['SLA vencidas', $summary['breached'], 'bg-red-50 text-red-700'],
        ['Cumplimiento SLA', $summary['sla_compliance'] === null ? '—' : $summary['sla_compliance'] . '%', 'bg-emerald-50 text-emerald-700'],
    ] as [$label, $value, $tone]): ?>
        <section class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold <?= $tone ?>"><?= esc($label) ?></span>
            <p class="text-4xl font-black text-slate-900 mt-4"><?= esc((string) $value) ?></p>
        </section>
    <?php endforeach; ?>
</div>

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200">
        <h2 class="text-xl font-black text-slate-900">Desempeño por operador</h2>
        <p class="text-sm text-slate-500 mt-1">Los registros con SLA vencido y mayor carga aparecen primero.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide text-xs">
                <tr>
                    <th class="px-5 py-4 text-left">Operador</th>
                    <th class="px-5 py-4 text-right">Abiertas</th>
                    <th class="px-5 py-4 text-right">Pendientes</th>
                    <th class="px-5 py-4 text-right">En marcha</th>
                    <th class="px-5 py-4 text-right">Esperando</th>
                    <th class="px-5 py-4 text-right">Por vencer</th>
                    <th class="px-5 py-4 text-right">Vencidas</th>
                    <th class="px-5 py-4 text-right">Prom. respuesta</th>
                    <th class="px-5 py-4 text-right">Cumplimiento</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($operators as $operator): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-4">
                            <p class="font-black text-slate-900"><?= esc($operator['name']) ?></p>
                            <p class="text-xs text-slate-400 mt-1"><?= esc($operator['email']) ?></p>
                        </td>
                        <td class="px-5 py-4 text-right font-black text-slate-900"><?= esc($operator['open']) ?></td>
                        <td class="px-5 py-4 text-right"><?= esc($operator['pending']) ?></td>
                        <td class="px-5 py-4 text-right"><?= esc($operator['active']) ?></td>
                        <td class="px-5 py-4 text-right"><?= esc($operator['waiting']) ?></td>
                        <td class="px-5 py-4 text-right"><span class="inline-flex min-w-8 justify-center rounded-full px-2.5 py-1 font-bold bg-amber-50 text-amber-700"><?= esc($operator['sla_due_soon']) ?></span></td>
                        <td class="px-5 py-4 text-right"><span class="inline-flex min-w-8 justify-center rounded-full px-2.5 py-1 font-bold <?= $operator['sla_breached'] > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' ?>"><?= esc($operator['sla_breached']) ?></span></td>
                        <td class="px-5 py-4 text-right"><?= $operator['average_first_response_minutes'] === null ? '—' : esc($operator['average_first_response_minutes']) . ' min' ?></td>
                        <td class="px-5 py-4 text-right font-black <?= $operator['sla_compliance'] !== null && $operator['sla_compliance'] < 80 ? 'text-red-600' : 'text-emerald-600' ?>"><?= $operator['sla_compliance'] === null ? '—' : esc($operator['sla_compliance']) . '%' ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($operators)): ?>
                    <tr><td colspan="9" class="px-5 py-14 text-center text-slate-500">No hay operadores o métricas SLA disponibles para este alcance.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?= $this->endSection() ?>
