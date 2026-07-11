<?= $this->extend('Modules\Dashboard\Views\layout') ?>

<?= $this->section('content') ?>

    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-widest text-pink-600">
            Dynamic Workflow Engine
        </p>

        <h1 class="text-3xl font-black text-slate-900 mt-2">
            Simulador de workflows
        </h1>

        <p class="text-slate-500 mt-2 max-w-3xl">
            Prueba flujos publicados sin afectar conversaciones,
            ciudadanos, casos ni notificaciones reales.
        </p>
    </div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="bg-red-100 text-red-700 rounded-xl p-4 mb-6">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-7">
            <h2 class="text-xl font-black text-slate-900">
                Iniciar una simulación
            </h2>

            <p class="text-slate-500 mt-2">
                Selecciona un flujo publicado para recorrer sus nodos,
                transiciones y validaciones.
            </p>

            <?php if (empty($workflows)): ?>
                <div class="bg-amber-50 text-amber-800 rounded-xl p-5 mt-6">
                    No existen workflows publicados disponibles.
                </div>
            <?php else: ?>
                <form
                    method="post"
                    action="<?= site_url('admin/workflows/simulator/start') ?>"
                    class="mt-6"
                >
                    <?= csrf_field() ?>

                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Workflow
                    </label>

                    <select
                        name="workflow_slug"
                        required
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 bg-white"
                    >
                        <option value="">Seleccionar workflow</option>

                        <?php foreach ($workflows as $workflow): ?>
                            <option value="<?= esc($workflow['slug']) ?>">
                                <?= esc($workflow['name']) ?>
                                — <?= esc($workflow['channel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button
                        type="submit"
                        class="mt-5 bg-pink-600 hover:bg-pink-700 text-white font-bold px-6 py-3 rounded-xl"
                    >
                        Iniciar simulación
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="bg-slate-950 text-white rounded-2xl p-7">
            <h2 class="text-xl font-black">
                Entorno seguro
            </h2>

            <div class="space-y-4 mt-5 text-sm text-slate-300">
                <p>✓ No envía mensajes a Messenger.</p>
                <p>✓ No crea casos reales.</p>
                <p>✓ No genera notificaciones.</p>
                <p>✓ No altera conversaciones ciudadanas.</p>
                <p>✓ Registra cada nodo recorrido.</p>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>