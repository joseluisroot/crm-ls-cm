<?= $this->extend('Modules\Dashboard\Views\layout') ?>
<?= $this->section('content') ?>
<div class="mb-6"><a href="<?= site_url('admin/access/teams') ?>" class="text-sm font-bold text-pink-600">← Volver a equipos</a></div>
<?php if (session('success')): ?><div class="mb-4 rounded-xl bg-emerald-50 text-emerald-700 p-4 font-semibold"><?= esc(session('success')) ?></div><?php endif; ?>
<?php if (session('error')): ?><div class="mb-4 rounded-xl bg-red-50 text-red-700 p-4 font-semibold"><?= esc(session('error')) ?></div><?php endif; ?>
<form method="post" action="<?= site_url('admin/access/teams/' . $team['id']) ?>" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm"><?= csrf_field() ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div><label class="block text-sm font-bold mb-2">Nombre</label><input name="name" value="<?= esc($team['name']) ?>" required class="w-full rounded-xl border border-slate-300 px-4 py-3"></div>
<div><label class="block text-sm font-bold mb-2">Código</label><input value="<?= esc($team['code']) ?>" disabled class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3"></div>
<div class="md:col-span-2"><label class="block text-sm font-bold mb-2">Descripción</label><textarea name="description" class="w-full rounded-xl border border-slate-300 px-4 py-3"><?= esc($team['description'] ?? '') ?></textarea></div>
<div><label class="block text-sm font-bold mb-2">Supervisor</label><select name="supervisor_user_id" class="w-full rounded-xl border border-slate-300 px-4 py-3"><option value="">Sin supervisor</option><?php foreach ($users as $user): ?><option value="<?= (int) $user['id'] ?>" <?= (int) ($team['supervisor_user_id'] ?? 0) === (int) $user['id'] ? 'selected' : '' ?>><?= esc($user['name']) ?></option><?php endforeach; ?></select></div>
<div class="flex items-end"><label class="flex items-center gap-3"><input type="checkbox" name="is_active" value="1" <?= (int) $team['is_active'] === 1 ? 'checked' : '' ?>><span class="font-bold">Equipo activo</span></label></div>
</div>
<div class="mt-8"><h4 class="text-lg font-black">Miembros</h4><p class="text-sm text-slate-500 mt-1">El supervisor se incluirá automáticamente como miembro del equipo.</p><div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4"><?php foreach ($users as $user): ?><label class="flex gap-3 rounded-xl border border-slate-200 p-4"><input type="checkbox" name="member_ids[]" value="<?= (int) $user['id'] ?>" <?= in_array((int) $user['id'], $memberIds, true) ? 'checked' : '' ?>><span><strong class="block"><?= esc($user['name']) ?></strong><small class="text-slate-500"><?= esc($user['email']) ?></small></span></label><?php endforeach; ?></div></div>
<div class="mt-6 flex justify-end"><button class="rounded-xl bg-pink-600 text-white font-black px-6 py-3">Guardar equipo</button></div>
</form>
<?= $this->endSection() ?>
