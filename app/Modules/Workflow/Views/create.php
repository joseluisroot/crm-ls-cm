<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="max-w-4xl">
        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
                CIAC Process Designer
            </p>

            <h1 class="text-3xl font-black text-slate-900 mt-2">
                Crear workflow
            </h1>

            <p class="text-slate-500 mt-2">
                Crea la estructura base de una nueva experiencia.
            </p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-100 text-red-700 rounded-xl p-4 mb-6">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form
            method="post"
            action="<?= site_url('admin/workflows') ?>"
            class="bg-white rounded-2xl border border-slate-200 shadow-sm p-7"
        >
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Nombre
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?= esc(old('name')) ?>"
                        required
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                        placeholder="Ej. Registro de voluntarios"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Slug
                    </label>

                    <input
                        type="text"
                        name="slug"
                        value="<?= esc(old('slug')) ?>"
                        required
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                        placeholder="registro-voluntarios"
                    >
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Descripción
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3"
                        placeholder="Describe el propósito del flujo."
                    ><?= esc(old('description')) ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Canal
                    </label>

                    <select
                        name="channel"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 bg-white"
                    >
                        <option value="all">Todos los canales</option>
                        <option value="messenger">Messenger</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="instagram">Instagram</option>
                        <option value="webchat">Web Chat</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mt-8">
                <button
                    type="submit"
                    class="bg-pink-600 hover:bg-pink-700 text-white font-bold px-6 py-3 rounded-xl"
                >
                    Crear workflow
                </button>

                <a
                    href="<?= site_url('admin/workflows') ?>"
                    class="border border-slate-300 text-slate-700 font-bold px-6 py-3 rounded-xl"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>

<?= $this->endSection() ?>