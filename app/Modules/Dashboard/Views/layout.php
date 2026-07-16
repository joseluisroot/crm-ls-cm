<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'CIAC Platform') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CIAC Platform - Citizen Intelligence & Attention Center">
    <meta name="theme-color" content="#DB2777">
    <meta name="application-name" content="CIAC Platform">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/ciac-design-system.css') ?>">

    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/favicon/ciac-icon.svg') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/favicon/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/favicon/favicon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/favicon/apple-touch-icon.png') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/favicon/favicon.ico') ?>">
</head>

<?php
use Modules\Authorization\Application\Navigation\NavigationBuilder;
$currentPath = trim(service('uri')->getPath(), '/');
$navigation = (new NavigationBuilder())->build((int) session()->get('admin_user_id'));
$profile = $navigation['profile'];
$profileClasses = ['pink'=>'bg-pink-500/10 border-pink-500/30 text-pink-300','violet'=>'bg-violet-500/10 border-violet-500/30 text-violet-300','emerald'=>'bg-emerald-500/10 border-emerald-500/30 text-emerald-300','blue'=>'bg-blue-500/10 border-blue-500/30 text-blue-300','amber'=>'bg-amber-500/10 border-amber-500/30 text-amber-300','cyan'=>'bg-cyan-500/10 border-cyan-500/30 text-cyan-300','slate'=>'bg-slate-800 border-slate-700 text-slate-300'];
$profileClass = $profileClasses[$profile['accent']] ?? $profileClasses['slate'];
$navClass = static function (string $path, bool $prefix = false) use ($currentPath): string {
    $active = $prefix ? str_starts_with($currentPath, trim($path, '/')) : $currentPath === trim($path, '/');
    return 'block px-4 py-3 rounded-lg transition ' . ($active ? 'bg-pink-600 text-white shadow-lg shadow-pink-950/20' : 'hover:bg-slate-800 text-slate-200');
};
$successMessage = session()->getFlashdata('success');
$errorMessage = session()->getFlashdata('error');
$warningMessage = session()->getFlashdata('warning');
$infoMessage = session()->getFlashdata('info');
?>

<body class="bg-slate-100 text-slate-800" style="font-family: 'Inter', sans-serif;">
<div class="min-h-screen flex">
    <aside class="w-72 bg-slate-950 text-white p-6 flex flex-col shrink-0">
        <div><div class="flex items-center gap-4"><div class="w-12 h-12 rounded-2xl bg-pink-600 flex items-center justify-center shadow-lg shadow-pink-950/30"><img src="<?= base_url('assets/favicon/ciac-icon.svg') ?>" alt="CIAC Platform" class="w-8 h-8 object-contain"></div><div><h1 class="text-3xl font-black tracking-tight">CIAC</h1><p class="uppercase tracking-[0.30em] text-xs text-slate-400">Platform</p></div></div><div class="mt-5"><p class="text-sm text-slate-300 font-semibold">Citizen Intelligence</p><p class="text-sm text-slate-400">& Attention Center</p></div><div class="mt-4 rounded-xl bg-slate-900 border border-slate-800 px-4 py-3"><p class="text-xs text-slate-500 uppercase tracking-widest">Powered by</p><p class="text-sm font-bold text-pink-400 mt-1">TwinsRG Labs</p></div></div>
        <nav class="mt-8 space-y-2 overflow-y-auto pr-1"><?php foreach ($navigation['groups'] as $groupIndex => $group): ?><div class="<?= $groupIndex > 0 ? 'pt-4 mt-4 border-t border-slate-800' : '' ?>"><p class="px-4 mb-2 text-xs uppercase tracking-widest text-slate-500"><?= esc($group['label']) ?></p><div class="space-y-2"><?php foreach ($group['items'] as $item): ?><a href="<?= site_url($item['url']) ?>" class="<?= $navClass($item['activePath'], (bool) $item['prefix']) ?>"><span aria-hidden="true"><?= esc($item['icon']) ?></span> <?= esc($item['label']) ?></a><?php endforeach; ?></div></div><?php endforeach; ?></nav>
        <div class="mt-auto pt-8"><div class="border-t border-slate-800 pt-6"><p class="text-xs uppercase tracking-widest text-slate-500">Usuario activo</p><p class="font-semibold mt-1"><?= esc(session()->get('admin_user_name') ?? 'Usuario') ?></p><div class="inline-flex mt-2 mb-5 px-3 py-1 rounded-full border text-xs font-bold <?= $profileClass ?>"><?= esc($profile['label']) ?></div><a href="<?= site_url('admin/logout') ?>" class="block text-center px-4 py-3 rounded-xl bg-slate-800 hover:bg-red-600 transition font-semibold">Cerrar sesión</a><div class="mt-6 text-center"><p class="text-xs text-slate-500">CIAC Platform</p><p class="text-xs text-slate-600 mt-1">Version 1.4 Operational Foundation</p></div></div></div>
    </aside>

    <main class="flex-1 p-6 lg:p-10 min-w-0">
        <header class="mb-10"><div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5"><div><p class="ciac-page-eyebrow">CIAC Platform · <?= esc($profile['label']) ?></p><h2 class="ciac-page-title mt-2"><?= esc($title ?? '') ?></h2><p class="ciac-page-description">Centro Inteligente de Atención Ciudadana</p></div><div class="ciac-card px-5 py-4"><p class="text-xs uppercase tracking-widest text-slate-400">Experiencia activa</p><p class="font-bold text-slate-800 mt-1"><?= esc($profile['label']) ?></p></div></div></header>
        <?= $this->renderSection('content') ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const palette = {
        primary: '#db2777',
        secondary: '#64748b',
        danger: '#dc2626'
    };

    const flashMessages = [
        {icon: 'success', title: 'Acción completada', text: <?= json_encode($successMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>},
        {icon: 'error', title: 'No fue posible completar la acción', text: <?= json_encode($errorMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>},
        {icon: 'warning', title: 'Atención', text: <?= json_encode($warningMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>},
        {icon: 'info', title: 'Información', text: <?= json_encode($infoMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>}
    ];

    const showLoading = (message = 'Procesando...') => Swal.fire({
        title: message,
        text: 'Espera un momento mientras CIAC completa la operación.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });

    const lockForm = (form, submitter = null) => {
        form.dataset.submitting = '1';
        form.setAttribute('aria-busy', 'true');

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((control) => {
            control.disabled = true;
            control.setAttribute('aria-disabled', 'true');
        });

        if (submitter) {
            submitter.dataset.originalText = submitter.dataset.originalText || submitter.textContent.trim();
        }
    };

    const submitForm = (form, submitter) => {
        lockForm(form, submitter);
        showLoading(submitter?.dataset.loading || form.dataset.loading || 'Procesando...');

        if (submitter?.formAction) {
            form.action = submitter.formAction;
        }

        if (submitter?.formMethod) {
            form.method = submitter.formMethod;
        }

        form.submit();
    };

    window.CIACAlerts = {
        loading: showLoading,
        success: (text, title = 'Acción completada') => Swal.fire({icon: 'success', title, text, confirmButtonColor: palette.primary}),
        error: (text, title = 'No fue posible completar la acción') => Swal.fire({icon: 'error', title, text, confirmButtonColor: palette.primary}),
        warning: (text, title = 'Atención') => Swal.fire({icon: 'warning', title, text, confirmButtonColor: palette.primary})
    };

    flashMessages.find((message) => Boolean(message.text)) && (() => {
        const message = flashMessages.find((item) => Boolean(item.text));
        Swal.fire({...message, confirmButtonText: 'Aceptar', confirmButtonColor: palette.primary});
    })();

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const submitter = event.submitter;
        const confirmMessage = submitter?.dataset.confirm || form.dataset.confirm;
        const loadingMessage = submitter?.dataset.loading || form.dataset.loading;

        if (!confirmMessage && !loadingMessage) return;

        event.preventDefault();

        if (form.dataset.submitting === '1') return;
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (confirmMessage) {
            const isDanger = (submitter?.dataset.confirmType || form.dataset.confirmType) === 'danger';
            const result = await Swal.fire({
                icon: isDanger ? 'warning' : 'question',
                title: confirmMessage,
                text: submitter?.dataset.confirmText || form.dataset.confirmText || '',
                showCancelButton: true,
                reverseButtons: true,
                focusCancel: isDanger,
                confirmButtonText: submitter?.dataset.confirmButton || form.dataset.confirmButton || (isDanger ? 'Sí, continuar' : 'Confirmar'),
                cancelButtonText: 'Cancelar',
                confirmButtonColor: isDanger ? palette.danger : palette.primary,
                cancelButtonColor: palette.secondary
            });

            if (!result.isConfirmed) return;
        }

        submitForm(form, submitter);
    });

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('a[data-confirm]');
        if (!link) return;

        event.preventDefault();
        const isDanger = link.dataset.confirmType === 'danger';
        const result = await Swal.fire({
            icon: isDanger ? 'warning' : 'question',
            title: link.dataset.confirm || '¿Deseas continuar?',
            text: link.dataset.confirmText || '',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: link.dataset.confirmButton || 'Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: isDanger ? palette.danger : palette.primary,
            cancelButtonColor: palette.secondary
        });

        if (!result.isConfirmed) return;
        showLoading(link.dataset.loading || 'Procesando...');
        window.location.assign(link.href);
    });
});
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
