<?php
/**
 * @var string $title
 * @var string|null $eyebrow
 * @var string|null $description
 * @var string|null $actionsHtml
 * @var string|null $class
 */
$eyebrow = $eyebrow ?? null;
$description = $description ?? null;
$actionsHtml = $actionsHtml ?? null;
$class = trim((string) ($class ?? ''));
?>
<header class="ciac-page-header xl:flex-row xl:items-center xl:justify-between <?= esc($class, 'attr') ?>">
    <div class="min-w-0">
        <?php if ($eyebrow): ?>
            <p class="ciac-page-eyebrow"><?= esc($eyebrow) ?></p>
        <?php endif; ?>
        <h1 class="ciac-page-title<?= $eyebrow ? ' mt-2' : '' ?>"><?= esc($title) ?></h1>
        <?php if ($description): ?>
            <p class="ciac-page-description"><?= esc($description) ?></p>
        <?php endif; ?>
    </div>
    <?php if ($actionsHtml): ?>
        <div class="flex flex-wrap items-center gap-3">
            <?= $actionsHtml ?>
        </div>
    <?php endif; ?>
</header>
