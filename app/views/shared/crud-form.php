<?php
$item = $item ?? [];
$backRoute = $backRoute ?? (($routeBase ?? '') . '/index');
?>
<div class="card content-card">
    <div class="card-body">
        <form method="post" action="<?= e(url($action ?? '')) ?>">
            <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
            <div class="row g-3">
                <?php foreach ($fields as $field): ?>
                    <?php
                    $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                    $type = $fieldTypes[$field] ?? 'text';
                    $value = old($field, $item[$field] ?? '');
                    ?>
                    <div class="col-md-6">
                        <label class="form-label"><?= e($label) ?></label>
                        <?php if ($type === 'textarea'): ?>
                            <textarea class="form-control" name="<?= e($field) ?>" rows="4"><?= e($value) ?></textarea>
                        <?php elseif ($type === 'select'): ?>
                            <select class="form-select" name="<?= e($field) ?>">
                                <option value="">Seleccione</option>
                                <?php foreach (($selectOptions[$field] ?? []) as $option): ?>
                                    <option value="<?= e($option['value']) ?>" <?= (string) $value === (string) $option['value'] ? 'selected' : '' ?>>
                                        <?= e($option['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input
                                class="form-control"
                                type="<?= e($type === 'number' ? 'number' : ($type === 'date' ? 'date' : ($type === 'password' ? 'password' : 'text'))) ?>"
                                name="<?= e($field) ?>"
                                value="<?= e($type === 'password' ? '' : $value) ?>"
                                <?= $type === 'number' ? 'step="0.01"' : '' ?>
                            >
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a class="btn btn-outline-secondary" href="<?= e(url($backRoute)) ?>">Volver</a>
            </div>
        </form>
    </div>
</div>

