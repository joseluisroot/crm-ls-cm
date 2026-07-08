<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'CRM Político') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800">

<div class="min-h-screen flex">

    <aside class="w-72 bg-slate-950 text-white p-6">
        <h1 class="text-2xl font-bold mb-8">Lupita CRM</h1>

        <nav class="space-y-2">
            <a href="/admin" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Dashboard</a>
            <a href="/admin/citizens" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Ciudadanos</a>
            <a href="/admin/conversations" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Conversaciones</a>
            <a href="/admin/cases" class="block px-4 py-3 rounded-lg hover:bg-slate-800">Casos</a>
        </nav>
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