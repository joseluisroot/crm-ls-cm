<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Lupita CRM') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-100 text-slate-800" style="font-family: 'Inter', sans-serif;">

<div class="min-h-screen flex">

    <aside class="w-72 bg-slate-950 text-white p-6">
        <h1 class="text-2xl font-bold mb-2">Lupita CRM</h1>
        <p class="text-sm text-slate-400 mb-8">Inteligencia Ciudadana</p>

        <nav class="space-y-2">
            <a href="/admin" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Dashboard</a>
            <a href="<?= site_url('admin/analytics') ?>"class="block px-4 py-3 rounded-lg hover:bg-slate-800">
                📊 Centro de Inteligencia
            </a>
            <a href="/admin/citizens" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Ciudadanos</a>
            <a href="/admin/conversations" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Conversaciones</a>
            <a href="/admin/cases" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Casos</a>
            <a href="<?= site_url('admin/my-cases') ?>" class="block px-4 py-3 rounded-lg hover:bg-slate-800">📋 Mis
                casos</a>
            <a href="/admin/messenger/events" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Messenger</a>
            <a href="/admin/notifications" class="block px-4 py-3 rounded-lg hover:bg-slate-800">🔔 Notificaciones</a>

        </nav>
        <div class="mt-10 border-t border-slate-800 pt-6">
            <p class="text-sm text-slate-400 mb-3">
                <?= esc(session()->get('admin_user_name') ?? 'Usuario') ?>
            </p>

            <a href="/admin/logout" class="block px-4 py-3 rounded-lg bg-slate-800 hover:bg-red-600">
                Cerrar sesión
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <header class="mb-8">
            <h2 class="text-3xl font-bold"><?= esc($title ?? '') ?></h2>
            <p class="text-slate-500">Centro de seguimiento ciudadano y análisis político.</p>
        </header>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-6">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

</div>

</body>
</html>