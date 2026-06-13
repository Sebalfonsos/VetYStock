<?php
$singular = rtrim($title ?? 'Registro', 's');
?>
<div class="card content-card">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <form class="d-flex gap-2" method="get" action="<?= e(url(($routeBase ?? '') . '/index')) ?>">
                <input class="form-control" type="search" name="q" placeholder="Buscar..." value="<?= e($query ?? '') ?>">
                <button class="btn btn-outline-primary" type="submit">Buscar</button>
            </form>
            <a class="btn btn-primary" href="<?= e(url(($routeBase ?? '') . '/create')) ?>">
                <i class="bi bi-plus-circle"></i> Nuevo <?= e($singular) ?>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <?php foreach ($labels as $field => $label): ?>
                        <th><?= e($label) ?></th>
                    <?php endforeach; ?>
                    <?php if (!empty($extraColumns)): ?>
                        <?php foreach ($extraColumns as $column): ?>
                            <th><?= e($column) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['id'] ?? '-') ?></td>
                        <?php foreach ($labels as $field => $label): ?>
                            <td><?= e($item[$field] ?? '-') ?></td>
                        <?php endforeach; ?>
                        <?php if (!empty($extraColumns)): ?>
                            <?php foreach ($extraColumns as $column): ?>
                                <td><?= e($item[$column] ?? '-') ?></td>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(url(($routeBase ?? '') . '/edit&id=' . ($item['id'] ?? 0))) ?>">Editar</a>
                            <a class="btn btn-sm btn-outline-danger" href="<?= e(url(($routeBase ?? '') . '/destroy&id=' . ($item['id'] ?? 0))) ?>" onclick="return confirm('¿Eliminar este registro?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

