<?php
$tone = $tone ?? 'slate';
$help = $help ?? null;
$class = trim((string) ($class ?? ''));
$toneClasses = [
    'blue' => 'bg-blue-50 text-blue-700',
    'violet' => 'bg-violet-50 text-violet-700',
    'amber' => 'bg-amber-50 text-amber-700',
    'pink' => 'bg-pink-50 text-pink-700',
    'green' => 'bg-green-50 text-green-700',
    'red' => 'bg-red-50 text-red-700',
    'slate' => 'bg-slate-100 text-slate-700',
];
$badgeClass = $toneClasses[$tone] ?? $toneClasses['slate'];
?>
<article class="ciac-card p-6 <?= esc($class, 'attr') ?>">
    <span class="ciac-badge <?= esc($badgeClass, 'attr') ?>"><?= esc($label) ?></span>
    <p class="mt-4 text-4xl font-black text-slate-900"><?= esc((string) $value) ?></p>
    <?php if ($help): ?>
        <p class="mt-2 text-xs font-semibold text-slate-400"><?= esc($help) ?></p>
    <?php endif; ?>
</article>
