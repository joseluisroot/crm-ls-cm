<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-2xl font-black text-slate-900">Roles y permisos</h3>
        <p class="text-slate-500 mt-1">Administra la matriz de capacidades de cada perfil de CIAC.</p>
    </div>
    <a href="<?= site_url('admin/access/users') ?>" class="inline-flex px-4 py-3 rounded-xl border border-slate-300 bg-white font-bold hover:bg-slate-50">Usuarios y accesos</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    <?php foreach ($roles as $role): ?>
        <article class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-mono font-bold text-pink-600"><?= esc($role['code']) ?></p>
                    <h4 class="mt-2 text-xl font-black text-slate-900"><?= esc($role['name']) ?></h4>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold <?= (int) $role['is_active'] === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>">
                    <?= (int) $role['is_active'] === 1 ? 'Activo' : 'Inactivo' ?>
                </span>
            </div>
            <p class="mt-3 text-sm text-slate-500 min-h-10"><?= esc($role['description'] ?? 'Sin descripción.') ?></p>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <p class="text-xs text-slate-400">Permisos</p>
                    <p class="text-xl font-black text-slate-900 mt-1"><?= (int) $role['permission_count'] ?></p>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                    <p class="text-xs text-slate-400">Usuarios</p>
                    <p class="text-xl font-black text-slate-900 mt-1"><?= (int) $role['user_count'] ?></p>
                </div>
            </div>
            <a href="<?= site_url('admin/access/roles/' . $role['id']) ?>" class="mt-5 block text-center px-4 py-3 rounded-xl bg-slate-900 text-white font-black hover:bg-pink-600 transition">Administrar rol</a>
        </article>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
