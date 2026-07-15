<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
        <a href="<?= site_url('admin/access/roles') ?>" class="text-sm font-bold text-pink-600 hover:text-pink-700">← Volver a roles</a>
        <h3 class="mt-3 text-2xl font-black text-slate-900"><?= esc($role['name']) ?></h3>
        <p class="mt-1 text-sm font-mono text-pink-600"><?= esc($role['code']) ?></p>
    </div>
    <form method="post" action="<?= site_url('admin/access/roles/' . $role['id'] . '/status') ?>">
        <?= csrf_field() ?>
        <?php $isActive = (int) $role['is_active'] === 1; ?>
        <input type="hidden" name="is_active" value="<?= $isActive ? 0 : 1 ?>">
        <button type="submit" class="px-5 py-3 rounded-xl font-black <?= $isActive ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' ?>" onclick="return confirm('¿Confirmas este cambio de estado?')">
            <?= $isActive ? 'Desactivar rol' : 'Activar rol' ?>
        </button>
    </form>
</div>

<form method="post" action="<?= site_url('admin/access/roles/' . $role['id'] . '/permissions') ?>" class="space-y-6">
    <?= csrf_field() ?>
    <?php foreach ($permissionsByModule as $module => $permissions): ?>
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h4 class="text-xl font-black text-slate-900 capitalize"><?= esc($module) ?></h4>
                    <p class="text-sm text-slate-500 mt-1">Selecciona las acciones que este rol puede ejecutar.</p>
                </div>
                <span class="rounded-full bg-slate-100 text-slate-600 px-3 py-1 text-xs font-bold"><?= count($permissions) ?> permisos</span>
            </div>
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($permissions as $permission): ?>
                    <label class="flex gap-3 rounded-2xl border border-slate-200 p-4 hover:border-pink-300 cursor-pointer">
                        <input type="checkbox" name="permission_ids[]" value="<?= (int) $permission['id'] ?>" class="mt-1" <?= in_array((int) $permission['id'], $assignedPermissionIds, true) ? 'checked' : '' ?>>
                        <span>
                            <strong class="block text-slate-900"><?= esc($permission['name']) ?></strong>
                            <span class="block mt-1 text-xs font-mono text-pink-600"><?= esc($permission['code']) ?></span>
                            <?php if (! empty($permission['description'])): ?>
                                <span class="block mt-2 text-sm text-slate-500"><?= esc($permission['description']) ?></span>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <div class="sticky bottom-4 flex justify-end">
        <button type="submit" class="px-6 py-3 rounded-xl bg-pink-600 text-white font-black shadow-lg hover:bg-pink-700">Guardar matriz de permisos</button>
    </div>
</form>

<section class="mt-8 bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
    <h4 class="text-xl font-black text-slate-900">Historial de cambios</h4>
    <p class="mt-1 text-sm text-slate-500">Últimas modificaciones realizadas sobre este rol.</p>
    <div class="mt-5 space-y-3">
        <?php foreach ($audit as $entry): ?>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <p class="font-bold text-slate-900"><?= esc($entry['action']) ?></p>
                    <p class="text-xs text-slate-500"><?= esc($entry['created_at']) ?></p>
                </div>
                <p class="mt-2 text-sm text-slate-500">Realizado por <?= esc($entry['actor_name'] ?? 'Sistema') ?></p>
            </div>
        <?php endforeach; ?>
        <?php if ($audit === []): ?>
            <p class="text-slate-500">Todavía no existen cambios auditados para este rol.</p>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
