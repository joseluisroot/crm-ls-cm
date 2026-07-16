<?php
$icon = $icon ?? '📭';
$description = $description ?? null;
$actionHtml = $actionHtml ?? null;
$class = trim((string) ($class ?? ''));
?>
<div class="ciac-empty-state <?= esc($class, 'attr') ?>">
    <div class="ciac-empty-state__icon" aria-hidden="true"><?= esc($icon) ?></div>
    <h3 class="ciac-empty-state__title"><?= esc($title) ?></h3>
    <?php if ($description): ?>
        <p class="ciac-empty-state__description"><?= esc($description) ?></p>
    <?php endif; ?>
    <?php if ($actionHtml): ?>
        <div class="mt-5 flex justify-center"><?= $actionHtml ?></div>
    <?php endif; ?>
</div>
