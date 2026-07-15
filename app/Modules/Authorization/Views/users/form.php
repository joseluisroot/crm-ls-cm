<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>
<div class="mb-6">
    <a href="<?= site_url('admin/access/users') ?>" class="text-sm font-bold text-pink-600 hover:text-pink-700">← Volver a usuarios</a>
</div>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit
    ? site_url('admin/access/users/' . $user['id'])
    : site_url('admin/access/users');
?>

<form method="post" action="<?= $action ?>" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <?= csrf_field() ?>

    <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <h3 class="text-xl font-black text-slate-900"><?= $isEdit ? 'Editar datos del usuario' : 'Crear nuevo usuario' ?></h3>
        <p class="text-sm text-slate-500 mt-1">Define la identidad, estado inicial y credenciales de acceso.</p>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <label class="block md:col-span-2">
                <span class="text-sm font-bold text-slate-700">Nombre completo</span>
                <input type="text" name="name" required minlength="3" value="<?= esc(old('name', $user['name'] ?? '')) ?>" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-pink-500 focus:ring-pink-500">
            </label>

            <label class="block md:col-span-2">
                <span class="text-sm font-bold text-slate-700">Correo electrónico</span>
                <input type="email" name="email" required value="<?= esc(old('email', $user['email'] ?? '')) ?>" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-pink-500 focus:ring-pink-500">
            </label>

            <?php if (! $isEdit): ?>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">Contraseña temporal</span>
                    <input type="password" name="password" required minlength="10" autocomplete="new-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-pink-500 focus:ring-pink-500">
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">Confirmar contraseña</span>
                    <input type="password" name="password_confirmation" required minlength="10" autocomplete="new-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-pink-500 focus:ring-pink-500">
                </label>
            <?php endif; ?>

            <label class="block">
                <span class="text-sm font-bold text-slate-700">Estado</span>
                <?php $selectedStatus = old('status', $user['status'] ?? 'active'); ?>
                <select name="status" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                    <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </label>
        </div>
    </section>

    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <h3 class="text-xl font-black text-slate-900">Roles iniciales</h3>
        <p class="text-sm text-slate-500 mt-1">Todo usuario debe tener al menos un rol.</p>

        <div class="mt-5 space-y-3">
            <?php $oldRoleIds = array_map('intval', (array) old('role_ids', $assignedRoleIds ?? [])); ?>
            <?php foreach ($roles as $role): ?>
                <label class="flex gap-3 rounded-xl border border-slate-200 p-4 hover:border-pink-300 cursor-pointer">
                    <input type="checkbox" name="role_ids[]" value="<?= (int) $role['id'] ?>" class="mt-1" <?= in_array((int) $role['id'], $oldRoleIds, true) ? 'checked' : '' ?>>
                    <span>
                        <strong class="block text-slate-900"><?= esc($role['name']) ?></strong>
                        <span class="block text-xs font-mono text-pink-600 mt-1"><?= esc($role['code']) ?></span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="mt-6 w-full px-5 py-3 rounded-xl bg-pink-600 text-white font-black hover:bg-pink-700">
            <?= $isEdit ? 'Guardar cambios' : 'Crear usuario' ?>
        </button>
    </section>
</form>
<?= $this->endSection() ?>
