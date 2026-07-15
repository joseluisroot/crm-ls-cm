<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-2xl font-black text-slate-900">Usuarios y accesos</h3>
        <p class="text-slate-500 mt-1">Administra usuarios, estados, roles y credenciales de CIAC.</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
            <span class="text-sm text-slate-500">Total de usuarios</span>
            <strong class="ml-3 text-xl text-slate-900"><?= count($users) ?></strong>
        </div>
        <a href="<?= site_url('admin/access/users/create') ?>" class="inline-flex px-5 py-3 rounded-xl bg-pink-600 text-white font-black hover:bg-pink-700 transition">+ Crear usuario</a>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">Usuario</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="px-5 py-4">Roles</th>
                    <th class="px-5 py-4">Último acceso</th>
                    <th class="px-5 py-4 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($users as $user): ?>
                    <?php $userRoles = $rolesByUser[(int) $user['id']] ?? []; ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            <p class="font-bold text-slate-900"><?= esc($user['name']) ?></p>
                            <p class="text-sm text-slate-500"><?= esc($user['email']) ?></p>
                        </td>
                        <td class="px-5 py-4">
                            <?php $active = ($user['status'] ?? '') === 'active'; ?>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold <?= $active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>"><?= $active ? 'Activo' : 'Inactivo' ?></span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($userRoles as $role): ?>
                                    <span class="rounded-lg bg-violet-50 text-violet-700 px-2.5 py-1 text-xs font-bold"><?= esc($role['name']) ?></span>
                                <?php endforeach; ?>
                                <?php if ($userRoles === []): ?><span class="text-sm text-amber-600 font-semibold">Sin rol asignado</span><?php endif; ?>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-500"><?= ! empty($user['last_login_at']) ? esc($user['last_login_at']) : 'Nunca' ?></td>
                        <td class="px-5 py-4 text-right">
                            <a href="<?= site_url('admin/access/users/' . $user['id']) ?>" class="inline-flex px-4 py-2 rounded-xl bg-slate-900 text-white font-bold hover:bg-pink-600 transition">Administrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
