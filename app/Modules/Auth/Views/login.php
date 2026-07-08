<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Iniciar sesión') ?> | Centro de Inteligencia Ciudadana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-950 min-h-screen flex items-center justify-center px-4" style="font-family: 'Inter', sans-serif;">

<div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8">

    <div class="text-center mb-8">

        <img
                src="<?= base_url('assets/img/nombre_vertical_rosa_transparente.png') ?>"
                alt="Centro de Inteligencia Ciudadana"
                class="mx-auto w-32 h-32 object-contain mb-6"
        >

        <p class="text-xs font-bold tracking-[0.35em] text-pink-600 uppercase mb-2">
            Centro de
        </p>

        <h1 class="text-3xl font-black text-slate-900 leading-tight uppercase">
            Inteligencia<br>Ciudadana
        </h1>

        <p class="text-slate-500 mt-3 text-sm">
            Gestión estratégica de relaciones con la ciudadanía
        </p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 text-red-700 rounded-xl p-4 mb-5 text-sm">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/login" class="space-y-5">
        <?= csrf_field() ?>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Correo electrónico
            </label>
            <input
                    type="email"
                    name="email"
                    value="<?= old('email') ?>"
                    required
                    class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="user@lupitaserrano.com"
            >
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Contraseña
            </label>
            <input
                    type="password"
                    name="password"
                    required
                    class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="••••••••"
            >
        </div>

        <button
                type="submit"
                class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-xl transition">
            Ingresar
        </button>
    </form>

    <p class="text-center text-xs text-slate-400 mt-8">
        Plataforma de Inteligencia Ciudadana • Versión 1.0
    </p>

</div>

</body>
</html>