<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>
<div class="mb-6">
    <a href="<?= site_url('admin/access/users') ?>" class="text-sm font-bold text-pink-600 hover:text-pink-700">← Volver a usuarios</a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <section class="xl:col-span-1 bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Usuario</p>
        <h3 class="mt-2 text-2xl font-black text-slate-900"><?= esc($user['name']) ?></h3>
        <p class="text-slate-500 mt-1"><?= esc($user['email']) ?></p>

        <dl class="mt-6 space-y-4 text-sm">
            <div>
                <dt class="text-slate-400">Estado</dt>
                <dd class="font-bold text-slate-800 mt-1"><?= ($user['status'] ?? '') === 'active' ? 'Activo' : 'Inactivo' ?></dd>
            </div>
            <div>
                <dt class="text-slate-400">Último acceso</dt>
                <dd class="font-bold text-slate-800 mt-1"><?= ! empty($user['last_login_at']) ? esc($user['last_login_at']) : 'Nunca' ?></dd>
            </div>
            <div>
                <dt class="text-slate-400">Creado</dt>
                <dd class="font-bold text-slate-800 mt-1"><?= esc($user['created_at'] ?? '-') ?></dd>
            </div>
        </dl>

        <form method="post" action="<?= site_url('admin/access/users/' . $user['id'] . '/status') ?>" class="mt-8">
            <?= csrf_field() ?>
            <?php $isActive = ($user['status'] ?? '') === 'active'; ?>
            <input type="hidden" name="status" value="<?= $isActive ? 'inactive' : 'active' ?>">
            <button type="submit" class="w-full px-4 py-3 rounded-xl font-black transition <?= $isActive ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' ?>" onclick="return confirm('¿Confirmas este cambio de estado?')">
                <?= $isActive ? 'Desactivar usuario' : 'Activar usuario' ?>
            </button>
        </form>
    </section>

    <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <div>
            <h3 class="text-xl font-black text-slate-900">Roles asignados</h3>
            <p class="text-sm text-slate-500 mt-1">Los permisos efectivos se calculan a partir de todos los roles seleccionados.</p>
        </div>

        <form method="post" action="<?= site_url('admin/access/users/' . $user['id'] . '/roles') ?>" class="mt-6">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($roles as $role): ?>
                    <label class="flex gap-3 rounded-2xl border border-slate-200 p-4 hover:border-pink-300 cursor-pointer">
                        <input type="checkbox" name="role_ids[]" value="<?= (int) $role['id'] ?>" class="mt-1" <?= in_array((int) $role['id'], $assignedRoleIds, true) ? 'checked' : '' ?>>
                        <span>
                            <strong class="block text-slate-900"><?= esc($role['name']) ?></strong>
                            <span class="block text-xs font-mono text-pink-600 mt-1"><?= esc($role['code']) ?></span>
                            <?php if (! empty($role['description'])): ?>
                                <span class="block text-sm text-slate-500 mt-2"><?= esc($role['description']) ?></span>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-5 py-3 rounded-xl bg-pink-600 text-white font-black hover:bg-pink-700">Guardar roles</button>
            </div>
        </form>
    </section>
</div>

<section class="mt-6 bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
    <h3 class="text-xl font-black text-slate-900">Permisos efectivos</h3>
    <p class="text-sm text-slate-500 mt-1">Vista consolidada de las capacidades otorgadas por los roles actuales.</p>
    <div class="mt-5 flex flex-wrap gap-2">
        <?php foreach ($effectivePermissions as $permission): ?>
            <span class="rounded-lg bg-slate-100 text-slate-700 px-3 py-2 text-xs font-mono font-bold"><?= esc($permission) ?></span>
        <?php endforeach; ?>
        <?php if ($effectivePermissions === []): ?>
            <span class="text-amber-600 font-semibold">Este usuario no tiene permisos efectivos.</span>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
