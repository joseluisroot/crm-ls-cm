<?= $this->extend('Modules\Dashboard\Views\layout') ?>
<?= $this->section('content') ?>
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div><h3 class="text-2xl font-black text-slate-900">Equipos de atención</h3><p class="text-slate-500 mt-1">Organiza supervisores y operadores para aplicar alcance por equipo.</p></div>
</div>
<?php if (session('error')): ?><div class="mb-4 rounded-xl bg-red-50 text-red-700 p-4 font-semibold"><?= esc(session('error')) ?></div><?php endif; ?>
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
<section class="xl:col-span-1 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
<h4 class="text-lg font-black">Crear equipo</h4>
<form method="post" action="<?= site_url('admin/access/teams') ?>" class="mt-5 space-y-4"><?= csrf_field() ?>
<input name="name" value="<?= esc(old('name')) ?>" placeholder="Nombre" required class="w-full rounded-xl border border-slate-300 px-4 py-3">
<input name="code" value="<?= esc(old('code')) ?>" placeholder="Código" required class="w-full rounded-xl border border-slate-300 px-4 py-3 uppercase">
<textarea name="description" placeholder="Descripción" class="w-full rounded-xl border border-slate-300 px-4 py-3"><?= esc(old('description')) ?></textarea>
<select name="supervisor_user_id" class="w-full rounded-xl border border-slate-300 px-4 py-3"><option value="">Sin supervisor</option><?php foreach ($users as $user): ?><option value="<?= (int) $user['id'] ?>"><?= esc($user['name']) ?></option><?php endforeach; ?></select>
<button class="w-full rounded-xl bg-pink-600 text-white font-black px-4 py-3">Crear equipo</button>
</form></section>
<section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
<table class="w-full text-left"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Equipo</th><th class="p-4">Supervisor</th><th class="p-4">Miembros</th><th class="p-4">Estado</th><th class="p-4"></th></tr></thead><tbody class="divide-y divide-slate-100">
<?php foreach ($teams as $team): ?><tr><td class="p-4"><strong><?= esc($team['name']) ?></strong><div class="text-xs font-mono text-pink-600"><?= esc($team['code']) ?></div></td><td class="p-4 text-sm"><?= esc($team['supervisor_name'] ?? 'Sin supervisor') ?></td><td class="p-4"><?= (int) $team['member_count'] ?></td><td class="p-4"><?= (int) $team['is_active'] === 1 ? 'Activo' : 'Inactivo' ?></td><td class="p-4 text-right"><a class="font-bold text-pink-600" href="<?= site_url('admin/access/teams/' . $team['id']) ?>">Administrar</a></td></tr><?php endforeach; ?>
</tbody></table></section></div>
<?= $this->endSection() ?>
