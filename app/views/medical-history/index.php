<div class="card content-card mb-3">
    <div class="card-body">
        <form method="get" action="<?= e(url('')) ?>" class="row g-3 align-items-end">
            <input type="hidden" name="route" value="medical-history/index">
            <div class="col-md-8">
                <label class="form-label">Seleccionar animal</label>
                <select class="form-select" name="animal_id" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($animals as $animal): ?>
                        <option value="<?= e($animal['id']) ?>" <?= (string) $animalId === (string) ($animal['id'] ?? '') ? 'selected' : '' ?>>
                            <?= e($animal['name'] ?? '') ?> · <?= e($animal['identification_code'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100" type="submit">Ver historial</button>
            </div>
        </form>
    </div>
</div>

<?php if ($animalId > 0): ?>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card content-card">
                <div class="card-body">
                    <h3 class="h5">Tratamientos</h3>
                    <?php foreach ($treatments as $treatment): ?>
                        <div class="border rounded p-3 mb-2">
                            <div class="fw-bold"><?= e($treatment['treatment_date'] ?? '') ?></div>
                            <div><?= e($treatment['diagnosis'] ?? '') ?></div>
                            <small class="text-muted"><?= e($treatment['procedure_desc'] ?? '') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card content-card">
                <div class="card-body">
                    <h3 class="h5">Vacunas</h3>
                    <?php foreach ($vaccines as $vaccine): ?>
                        <div class="border rounded p-3 mb-2">
                            <div class="fw-bold"><?= e($vaccine['application_date'] ?? '') ?></div>
                            <div><?= e($vaccine['vaccine_name'] ?? '') ?></div>
                            <small class="text-muted">Lote: <?= e($vaccine['batch'] ?? '') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card content-card">
                <div class="card-body">
                    <h3 class="h5">Observaciones</h3>
                    <?php foreach ($observations as $observation): ?>
                        <div class="border rounded p-3 mb-2">
                            <div class="fw-bold"><?= e($observation['observation_date'] ?? '') ?></div>
                            <div><?= e($observation['description'] ?? '') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">Selecciona un animal para ver su historial médico.</div>
<?php endif; ?>
