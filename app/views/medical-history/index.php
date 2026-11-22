<?php
$typeLabels = [
    'treatment' => ['Tratamiento', 'bg-primary'],
    'vaccine' => ['Vacuna', 'bg-success'],
    'observation' => ['Observación', 'bg-secondary'],
];
?>
<div class="card content-card mb-3">
    <div class="card-body">
        <form method="get" action="<?= e(url('')) ?>" class="row g-3 align-items-end">
            <input type="hidden" name="route" value="medical-history/index">
            <div class="col-md-4">
                <label class="form-label">Animal</label>
                <select class="form-select" name="animal_id" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($animals as $animal): ?>
                        <option value="<?= e($animal['id']) ?>" <?= (string) $filters['animal_id'] === (string) ($animal['id'] ?? '') ? 'selected' : '' ?>>
                            <?= e($animal['name'] ?? '') ?> · <?= e($animal['identification_code'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select class="form-select" name="type">
                    <option value="">Todos</option>
                    <?php foreach ($typeLabels as $value => [$label]): ?>
                        <option value="<?= e($value) ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input class="form-control" type="date" name="from" value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input class="form-control" type="date" name="to" value="<?= e($filters['to']) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Ver historial</button>
            </div>
        </form>
    </div>
</div>

<?php if ($serviceError): ?>
    <div class="alert alert-warning"><?= e($serviceError) ?></div>
<?php elseif ($filters['animal_id'] <= 0): ?>
    <div class="alert alert-info">Selecciona un animal para ver su historial médico.</div>
<?php elseif (!$events): ?>
    <div class="alert alert-light border">No hay registros con esos filtros.</div>
<?php else: ?>
    <div class="card content-card">
        <div class="card-body table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Registro</th>
                    <th>Detalle</th>
                    <th>Próxima fecha</th>
                    <th>Responsable</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($events as $event): ?>
                    <?php [$label, $badge] = $typeLabels[$event['type']] ?? [$event['type'], 'bg-light text-dark']; ?>
                    <tr>
                        <td class="text-nowrap"><?= e($event['event_date']) ?></td>
                        <td><span class="badge <?= e($badge) ?>"><?= e($label) ?></span></td>
                        <td class="fw-semibold"><?= e($event['title']) ?></td>
                        <td>
                            <?= e($event['detail'] ?? '') ?>
                            <?php if (!empty($event['notes'])): ?>
                                <div class="small text-muted"><?= e($event['notes']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap"><?= e($event['next_date'] ?? '-') ?></td>
                        <td><?= e($event['user_name'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
