<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto bg-white border border-slate-200 rounded-3xl shadow-sm p-8 lg:p-12 text-center">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 text-red-600 flex items-center justify-center text-3xl">🔒</div>
    <p class="mt-6 text-sm uppercase tracking-[0.2em] font-bold text-red-500">403 · Acceso denegado</p>
    <h3 class="mt-3 text-3xl font-black text-slate-900">No tienes permisos para acceder a esta función.</h3>
    <p class="mt-4 text-slate-500">La acción fue bloqueada por la política de seguridad de CIAC. Si consideras que necesitas este acceso, comunícate con un administrador.</p>
    <?php if (! empty($permission)): ?>
        <div class="mt-6 inline-flex px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-mono text-sm">
            <?= esc($permission) ?>
        </div>
    <?php endif; ?>
    <div class="mt-8 flex justify-center gap-3">
        <a href="javascript:history.back()" class="px-5 py-3 rounded-xl border border-slate-300 font-semibold hover:bg-slate-50">Volver</a>
        <a href="<?= site_url('admin') ?>" class="px-5 py-3 rounded-xl bg-pink-600 text-white font-semibold hover:bg-pink-700">Ir al inicio</a>
    </div>
</div>
<?= $this->endSection() ?>
