<?php
$subtitle = $subtitle ?? null;
$meta = $meta ?? null;
$actionsHtml = $actionsHtml ?? null;
?>
<header class="ciac-card__header">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <h2 class="ciac-card__title"><?= esc($title) ?></h2>
            <?php if ($subtitle): ?>
                <p class="ciac-card__subtitle"><?= esc($subtitle) ?></p>
            <?php endif; ?>
            <?php if ($meta): ?>
                <p class="mt-2 text-xs font-semibold text-slate-400"><?= esc($meta) ?></p>
            <?php endif; ?>
        </div>
        <?php if ($actionsHtml): ?>
            <div class="flex flex-wrap items-center gap-3"><?= $actionsHtml ?></div>
        <?php endif; ?>
    </div>
</header>
