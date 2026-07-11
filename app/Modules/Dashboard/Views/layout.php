<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        <?= esc($title ?? 'CIAC Platform') ?>
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
            name="description"
            content="CIAC Platform - Citizen Intelligence & Attention Center"
    >

    <meta
            name="theme-color"
            content="#DB2777"
    >

    <meta
            name="application-name"
            content="CIAC Platform"
    >

    <script src="https://cdn.tailwindcss.com"></script>

    <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
    >

    <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
    >

    <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
            rel="stylesheet"
    >

    <!-- Favicons -->
    <link
            rel="icon"
            type="image/svg+xml"
            href="<?= base_url('assets/favicon/ciac-icon.svg') ?>"
    >

    <link
            rel="icon"
            type="image/png"
            sizes="32x32"
            href="<?= base_url('assets/favicon/favicon-32x32.png') ?>"
    >

    <link
            rel="icon"
            type="image/png"
            sizes="16x16"
            href="<?= base_url('assets/favicon/favicon-16x16.png') ?>"
    >

    <link
            rel="apple-touch-icon"
            sizes="180x180"
            href="<?= base_url('assets/favicon/apple-touch-icon.png') ?>"
    >

    <link
            rel="shortcut icon"
            type="image/x-icon"
            href="<?= base_url('assets/favicon/favicon.ico') ?>"
    >
</head>

<body
        class="bg-slate-100 text-slate-800"
        style="font-family: 'Inter', sans-serif;"
>

<div class="min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-72 bg-slate-950 text-white p-6 flex flex-col">

        <!-- Identidad -->
        <div>
            <div class="flex items-center gap-4">

                <div class="w-12 h-12 rounded-2xl bg-pink-600 flex items-center justify-center shadow-lg shadow-pink-950/30">
                    <img
                            src="<?= base_url('assets/favicon/ciac-icon.svg') ?>"
                            alt="CIAC Platform"
                            class="w-8 h-8 object-contain"
                    >
                </div>

                <div>
                    <h1 class="text-3xl font-black tracking-tight">
                        CIAC
                    </h1>

                    <p class="uppercase tracking-[0.30em] text-xs text-slate-400">
                        Platform
                    </p>
                </div>
            </div>

            <div class="mt-5">
                <p class="text-sm text-slate-300 font-semibold">
                    Citizen Intelligence
                </p>

                <p class="text-sm text-slate-400">
                    & Attention Center
                </p>
            </div>

            <div class="mt-4 rounded-xl bg-slate-900 border border-slate-800 px-4 py-3">
                <p class="text-xs text-slate-500 uppercase tracking-widest">
                    Powered by
                </p>

                <p class="text-sm font-bold text-pink-400 mt-1">
                    TwinsRG Labs
                </p>
            </div>
        </div>

        <!-- Navegación -->
        <nav class="mt-10 space-y-2">

            <a
                    href="<?= site_url('admin') ?>"
                    class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
            >
                🏠 Executive Dashboard
            </a>

            <a
                    href="<?= site_url('admin/analytics') ?>"
                    class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
            >
                📊 Intelligence Center
            </a>

            <a
                    href="<?= site_url('admin/citizens') ?>"
                    class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
            >
                👥 Citizen Center
            </a>

            <a
                    href="<?= site_url('admin/conversations') ?>"
                    class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
            >
                💬 Conversation Center
            </a>

            <a
                    href="<?= site_url('admin/cases') ?>"
                    class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
            >
                📁 Case Management
            </a>

            <a
                    href="<?= site_url('admin/my-cases') ?>"
                    class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
            >
                ✅ My Assigned Cases
            </a>

            <a
                    href="<?= site_url('admin/notifications') ?>"
                    class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
            >
                🔔 Notification Center
            </a>

            <a
                    href="<?= site_url('admin/messenger/events') ?>"
                    class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
            >
                💙 Channel Events
            </a>

            <div class="pt-4 mt-4 border-t border-slate-800">

                <p class="px-4 mb-2 text-xs uppercase tracking-widest text-slate-500">
                    Process Automation
                </p>

                <a
                        href="<?= site_url('admin/workflows') ?>"
                        class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
                >
                    ⚙️ Workflow Designer
                </a>

                <a
                        href="<?= site_url('admin/workflows/simulator') ?>"
                        class="block px-4 py-3 rounded-lg hover:bg-slate-800 transition"
                >
                    🧪 Workflow Simulator
                </a>

            </div>

        </nav>

        <!-- Usuario y versión -->
        <div class="mt-auto pt-8">

            <div class="border-t border-slate-800 pt-6">

                <p class="text-xs uppercase tracking-widest text-slate-500">
                    Usuario activo
                </p>

                <p class="font-semibold mt-1 mb-5">
                    <?= esc(
                            session()->get('admin_user_name')
                            ?? 'Administrador'
                    ) ?>
                </p>

                <a
                        href="<?= site_url('admin/logout') ?>"
                        class="block text-center px-4 py-3 rounded-xl bg-slate-800 hover:bg-red-600 transition font-semibold"
                >
                    Cerrar sesión
                </a>

                <div class="mt-6 text-center">
                    <p class="text-xs text-slate-500">
                        CIAC Platform
                    </p>

                    <p class="text-xs text-slate-600 mt-1">
                        Version 0.8 Alpha
                    </p>
                </div>

            </div>

        </div>

    </aside>

    <!-- Contenido principal -->
    <main class="flex-1 p-10">

        <header class="mb-10">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">

                <div>
                    <p class="text-sm uppercase tracking-[0.20em] text-pink-600 font-bold">
                        CIAC Platform
                    </p>

                    <h2 class="text-4xl font-black text-slate-900 mt-2">
                        <?= esc($title ?? '') ?>
                    </h2>

                    <p class="text-slate-500 mt-2">
                        Centro Inteligente de Atención Ciudadana
                    </p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl px-5 py-4 shadow-sm">
                    <p class="text-xs uppercase tracking-widest text-slate-400">
                        Plataforma
                    </p>

                    <p class="font-bold text-slate-800 mt-1">
                        Citizen Intelligence & Attention Center
                    </p>
                </div>

            </div>
        </header>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 rounded-xl bg-green-100 border border-green-300 p-4 text-green-800">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 rounded-xl bg-red-100 border border-red-300 p-4 text-red-700">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>

    </main>

</div>

</body>
</html>