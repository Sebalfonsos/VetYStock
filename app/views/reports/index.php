<?php
$selects = [
    'category' => ['Categoría', fn ($row) => $row['name']],
    'animal_id' => ['Animal', fn ($row) => $row['name'] . ' · ' . $row['identification_code']],
    'user_id' => ['Usuario', fn ($row) => $row['name']],
];
$fixedSelects = [
    'movement_type' => ['Tipo', ['input' => 'Entrada', 'output' => 'Salida']],
    'type' => ['Tipo de registro', ['treatment' => 'Tratamiento', 'vaccine' => 'Vacuna', 'observation' => 'Observación']],
];
$textLabels = ['entity' => 'Módulo', 'action' => 'Acción'];
$money = fn ($value) => '$' . number_format((float) $value, 0, ',', '.');
?>
<ul class="nav nav-pills mb-3 flex-wrap">
    <?php foreach ($types as $value => $name): ?>
        <li class="nav-item">
            <a class="nav-link <?= $value === $type ? 'active' : '' ?>" href="<?= e(url('reports/index&type=' . $value)) ?>"><?= e($name) ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="card content-card mb-3">
    <div class="card-body">
        <form method="get" action="<?= e(url('')) ?>" class="row g-3 align-items-end">
            <input type="hidden" name="route" value="reports/index">
            <input type="hidden" name="type" value="<?= e($type) ?>">
            <?php foreach ($filters as $key => $value): ?>
                <div class="col-md-2">
                    <?php if (isset($selects[$key])): ?>
                        <?php [$fieldLabel, $optionLabel] = $selects[$key]; ?>
                        <label class="form-label"><?= e($fieldLabel) ?></label>
                        <select class="form-select" name="<?= e($key) ?>">
                            <option value="">Todos</option>
                            <?php foreach ($options[$key] ?? [] as $row): ?>
                                <option value="<?= e($row['id']) ?>" <?= (string) $row['id'] === $value ? 'selected' : '' ?>><?= e($optionLabel($row)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif (isset($fixedSelects[$key])): ?>
                        <?php [$fieldLabel, $choices] = $fixedSelects[$key]; ?>
                        <label class="form-label"><?= e($fieldLabel) ?></label>
                        <select class="form-select" name="<?= e($key) ?>">
                            <option value="">Todos</option>
                            <?php foreach ($choices as $choice => $choiceLabel): ?>
                                <option value="<?= e($choice) ?>" <?= $choice === $value ? 'selected' : '' ?>><?= e($choiceLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif (in_array($key, ['from', 'to'], true)): ?>
                        <label class="form-label"><?= $key === 'from' ? 'Desde' : 'Hasta' ?></label>
                        <input class="form-control" type="date" name="<?= e($key) ?>" value="<?= e($value) ?>">
                    <?php else: ?>
                        <label class="form-label"><?= e($textLabels[$key] ?? $key) ?></label>
                        <input class="form-control" name="<?= e($key) ?>" value="<?= e($value) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Generar</button>
            </div>
            <?php if ($report && $report['rows']): ?>
                <div class="col-md-2">
                    <button class="btn btn-outline-success w-100" type="submit" name="export" value="csv"><i class="bi bi-filetype-csv"></i> Exportar CSV</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($serviceError): ?>
    <div class="alert alert-warning"><?= e($serviceError) ?></div>
<?php elseif (!$report['rows']): ?>
    <div class="alert alert-light border">No hay datos para este reporte con esos filtros.</div>
<?php else: ?>
    <div class="card content-card">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <h2 class="h5 mb-0"><?= e($label) ?></h2>
                <span class="text-muted"><?= count($report['rows']) ?> registros</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <?php foreach ($report['columns'] as $column): ?>
                            <th><?= e($column) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($report['rows'] as $row): ?>
                        <tr>
                            <?php foreach (array_keys($report['columns']) as $key): ?>
                                <td><?= e($key === 'value' ? $money($row[$key]) : ($row[$key] ?? '-')) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
