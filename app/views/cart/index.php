<?php
$money = fn ($value) => '$' . number_format((float) $value, 0, ',', '.');
$hasProblems = (bool) array_filter($cart['items'], fn ($item) => $item['problem'] !== null);
?>

<?php if ($lastOrder): ?>
    <div class="alert alert-success d-flex gap-3 align-items-center">
        <i class="bi bi-bag-check fs-3"></i>
        <div>
            <div class="fw-semibold">Pedido #<?= e($lastOrder['order_id']) ?> registrado por <?= e($money($lastOrder['total'])) ?>.</div>
            <div>Te contactaremos al teléfono que dejaste para coordinar la entrega o recogida.</div>
        </div>
    </div>
<?php endif; ?>

<?php if (!$cart['items']): ?>
    <div class="card content-card">
        <div class="card-body text-center py-5">
            <i class="bi bi-cart3 fs-1 text-muted"></i>
            <p class="mt-2 mb-3 text-muted">Tu carrito está vacío. Puedes agregar productos desde la tienda o desde las recomendaciones del asistente.</p>
            <a class="btn btn-primary" href="<?= e(url('home/catalog')) ?>">Ir a la tienda</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card content-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr><th>Producto</th><th class="text-end">Precio</th><th style="width:9rem">Cantidad</th><th class="text-end">Subtotal</th><th></th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cart['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= e($item['name']) ?></div>
                                        <div class="small text-muted"><?= e($item['code']) ?> · <?= e($item['category']) ?></div>
                                        <?php if ($item['problem']): ?><div class="small text-danger"><?= e($item['problem']) ?></div><?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap"><?= e($money($item['price'])) ?></td>
                                    <td>
                                        <form method="post" action="<?= e(url('cart/update')) ?>" class="d-flex gap-1">
                                            <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                                            <input type="hidden" name="product_id" value="<?= e($item['product_id']) ?>">
                                            <input type="number" name="quantity" class="form-control form-control-sm" min="0" max="<?= e(max($item['stock'], $item['quantity'])) ?>" value="<?= e($item['quantity']) ?>" aria-label="Cantidad de <?= e($item['name']) ?>" onchange="this.form.submit()">
                                            <noscript><button class="btn btn-sm btn-outline-secondary">OK</button></noscript>
                                        </form>
                                    </td>
                                    <td class="text-end text-nowrap fw-semibold"><?= e($money($item['subtotal'])) ?></td>
                                    <td class="text-end">
                                        <form method="post" action="<?= e(url('cart/remove')) ?>">
                                            <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                                            <input type="hidden" name="product_id" value="<?= e($item['product_id']) ?>">
                                            <button class="btn btn-sm btn-outline-danger" title="Quitar"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <form method="post" action="<?= e(url('cart/clear')) ?>" onsubmit="return confirm('¿Vaciar el carrito?')">
                            <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i> Vaciar carrito</button>
                        </form>
                        <a class="btn btn-sm btn-link" href="<?= e(url('home/catalog')) ?>">Seguir comprando</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card content-card">
                <div class="card-body">
                    <h2 class="h5">Resumen</h2>
                    <div class="d-flex justify-content-between"><span><?= e($cart['count']) ?> unidades</span><span class="fs-4 fw-bold"><?= e($money($cart['total'])) ?></span></div>
                    <hr>
                    <h3 class="h6">Datos para el pedido</h3>
                    <form method="post" action="<?= e(url('cart/checkout')) ?>">
                        <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                        <div class="mb-2">
                            <label class="form-label small" for="customer_name">Nombre</label>
                            <input class="form-control" id="customer_name" name="customer_name" maxlength="120" required value="<?= e(old('customer_name', Auth::user()['name'] ?? '')) ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small" for="customer_phone">Teléfono</label>
                            <input class="form-control" id="customer_phone" name="customer_phone" maxlength="40" required inputmode="tel" value="<?= e(old('customer_phone')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small" for="notes">Notas (opcional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" maxlength="500"><?= e(old('notes')) ?></textarea>
                        </div>
                        <button class="btn btn-success w-100" <?= $hasProblems ? 'disabled' : '' ?>><i class="bi bi-bag-check"></i> Confirmar pedido</button>
                        <?php if ($hasProblems): ?>
                            <div class="small text-danger mt-2">Ajusta las cantidades marcadas en rojo para continuar.</div>
                        <?php endif; ?>
                        <div class="small text-muted mt-2">El pago se hace al recoger o recibir el pedido.</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
