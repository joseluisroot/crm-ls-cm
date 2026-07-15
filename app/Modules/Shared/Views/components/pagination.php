<?php
/** @var array{total:int,page:int,perPage:int,pages:int} $pagination */
$query = service('request')->getGet();
$page = (int) ($pagination['page'] ?? 1);
$pages = (int) ($pagination['pages'] ?? 1);
$total = (int) ($pagination['total'] ?? 0);
$perPage = (int) ($pagination['perPage'] ?? 25);
$from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$to = min($page * $perPage, $total);
$url = static function (int $target) use ($query): string {
    $query['page'] = $target;
    return current_url() . '?' . http_build_query($query);
};
$start = max(1, $page - 2);
$end = min($pages, $page + 2);
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 py-5 border-t border-slate-200 bg-slate-50/70">
    <p class="text-sm text-slate-500">
        Mostrando <strong class="text-slate-800"><?= esc($from) ?>–<?= esc($to) ?></strong>
        de <strong class="text-slate-800"><?= esc($total) ?></strong> registros
    </p>

    <?php if ($pages > 1): ?>
        <nav class="flex items-center gap-1" aria-label="Paginación">
            <a href="<?= $page > 1 ? esc($url($page - 1)) : '#' ?>"
               class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold transition <?= $page > 1 ? 'border-slate-200 bg-white text-slate-700 hover:border-pink-300 hover:text-pink-600' : 'pointer-events-none border-slate-100 bg-slate-100 text-slate-300' ?>">
                Anterior
            </a>

            <?php if ($start > 1): ?>
                <a href="<?= esc($url(1)) ?>" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:border-pink-300 hover:text-pink-600">1</a>
                <?php if ($start > 2): ?><span class="px-1 text-slate-400">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($number = $start; $number <= $end; $number++): ?>
                <a href="<?= esc($url($number)) ?>"
                   class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border text-sm font-bold transition <?= $number === $page ? 'border-pink-600 bg-pink-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-pink-300 hover:text-pink-600' ?>">
                    <?= esc($number) ?>
                </a>
            <?php endfor; ?>

            <?php if ($end < $pages): ?>
                <?php if ($end < $pages - 1): ?><span class="px-1 text-slate-400">…</span><?php endif; ?>
                <a href="<?= esc($url($pages)) ?>" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:border-pink-300 hover:text-pink-600"><?= esc($pages) ?></a>
            <?php endif; ?>

            <a href="<?= $page < $pages ? esc($url($page + 1)) : '#' ?>"
               class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold transition <?= $page < $pages ? 'border-slate-200 bg-white text-slate-700 hover:border-pink-300 hover:text-pink-600' : 'pointer-events-none border-slate-100 bg-slate-100 text-slate-300' ?>">
                Siguiente
            </a>
        </nav>
    <?php endif; ?>
</div>
