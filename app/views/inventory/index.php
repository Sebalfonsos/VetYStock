<div class="row g-3">
    <div class="col-lg-4">
        <div class="card content-card">
            <div class="card-body">
                <h3 class="h5">Nuevo movimiento</h3>
                <?php if (!empty($serviceError)): ?>
                    <div class="alert alert-warning">
                        <?= e($serviceError) ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="<?= e(url('inventory/store')) ?>">
                    <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= e($product['id']) ?>"><?= e($product['name']) ?> (Stock: <?= e($product['current_stock'] ?? '-') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="movement_type" class="form-select" required>
                            <option value="input">Entrada</option>
                            <option value="output">Salida</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <textarea name="reason" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referencia</label>
                        <input type="text" name="reference" class="form-control">
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Guardar movimiento</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card content-card mb-3">
            <div class="card-body">
                <h3 class="h5">Alertas de stock</h3>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Mensaje</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($alerts as $alert): ?>
                            <tr>
                                <td><?= e($alert['product_name'] ?? '-') ?></td>
                                <td><?= e($alert['message'] ?? '-') ?></td>
                                <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-body">
                <h3 class="h5">Movimientos recientes</h3>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Antes</th>
                            <th>Después</th>
                            <th>Responsable</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= e($item['product_name'] ?? '-') ?></td>
                                <td><?= e($item['movement_type'] ?? '-') ?></td>
                                <td><?= e($item['quantity'] ?? '-') ?></td>
                                <td><?= e($item['before_stock'] ?? '-') ?></td>
                                <td><?= e($item['after_stock'] ?? '-') ?></td>
                                <td><?= e($item['user_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

