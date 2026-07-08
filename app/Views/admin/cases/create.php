<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

    <form method="post" action="/admin/cases/store" class="bg-white rounded-2xl shadow p-6 space-y-5">

        <?= csrf_field() ?>

        <div>
            <label class="block font-semibold mb-2">Ciudadano</label>
            <select name="citizen_id" class="w-full border rounded-xl p-3" required>
                <?php foreach ($citizens as $citizen): ?>
                    <option value="<?= $citizen['id'] ?>"><?= esc($citizen['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-2">Categoría</label>
            <select name="category_id" class="w-full border rounded-xl p-3">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-2">Estado</label>
            <select name="status_id" class="w-full border rounded-xl p-3">
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status['id'] ?>"><?= esc($status['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-2">Título</label>
            <input type="text" name="title" class="w-full border rounded-xl p-3" required>
        </div>

        <div>
            <label class="block font-semibold mb-2">Descripción</label>
            <textarea name="description" rows="5" class="w-full border rounded-xl p-3" required></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold mb-2">Prioridad</label>
                <select name="priority" class="w-full border rounded-xl p-3">
                    <option value="low">Baja</option>
                    <option value="normal">Normal</option>
                    <option value="high">Alta</option>
                    <option value="urgent">Urgente</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2">Sentimiento</label>
                <select name="sentiment" class="w-full border rounded-xl p-3">
                    <option value="positive">Positivo</option>
                    <option value="neutral">Neutral</option>
                    <option value="negative">Negativo</option>
                    <option value="very_negative">Muy negativo</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2">Responsable</label>
                <input type="text" name="assigned_to" class="w-full border rounded-xl p-3">
            </div>
        </div>

        <button class="bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold">
            Guardar caso
        </button>

    </form>

<?= $this->endSection() ?>