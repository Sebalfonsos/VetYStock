<?php
$money = fn ($value) => '$' . number_format((float) $value, 0, ',', '.');
$labels = [
    'pending' => ['Pendiente', 'text-bg-warning'],
    'delivered' => ['Entregado', 'text-bg-success'],
    'cancelled' => ['Cancelado', 'text-bg-secondary'],
];
$tabs = ['pending' => 'Pendientes', 'delivered' => 'Entregados', 'cancelled' => 'Cancelados', '' => 'Todos'];
?>
<div class="card content-card">
    <div class="card-body">
        <ul class="nav nav-pills mb-3">
            <?php foreach ($tabs as $value => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $status === $value ? 'active' : '' ?>" href="<?= e(url('orders/index&status=' . $value)) ?>"><?= e($label) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (!$orders): ?>
            <div class="alert alert-light border mb-0">No hay pedidos en esta lista.</div>
        <?php endif; ?>

        <div class="list-group">
            <?php foreach ($orders as $order): ?>
                <?php [$statusLabel, $statusClass] = $labels[$order['status']] ?? [$order['status'], 'text-bg-light']; ?>
                <div class="list-group-item">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <div>
                            <span class="fw-semibold">Pedido #<?= e($order['id']) ?></span>
                            <span class="badge <?= e($statusClass) ?> ms-1"><?= e($statusLabel) ?></span>
                            <div class="small text-muted">
                                <?= e($order['created_at']) ?> · <?= e($order['customer_name']) ?> · <?= e($order['customer_phone']) ?>
                                <?= $order['user_name'] ? ' · registrado por ' . e($order['user_name']) : ' · cliente de la tienda' ?>
                            </div>
                        </div>
                        <div class="fs-5 fw-bold"><?= e($money($order['total'])) ?></div>
                    </div>
                    <ul class="small mb-2 mt-2">
                        <?php foreach ($order['items'] as $line): ?>
                            <li><?= e($line['quantity']) ?> × <?= e($line['product_name']) ?> (<?= e($money($line['unit_price'])) ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($order['notes']): ?><div class="small mb-2"><i class="bi bi-chat-left-text"></i> <?= e($order['notes']) ?></div><?php endif; ?>
                    <?php if ($order['status'] === 'pending'): ?>
                        <div class="d-flex gap-2">
                            <form method="post" action="<?= e(url('orders/status')) ?>">
                                <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                                <input type="hidden" name="id" value="<?= e($order['id']) ?>">
                                <input type="hidden" name="status" value="delivered">
                                <button class="btn btn-sm btn-success"><i class="bi bi-check2"></i> Marcar entregado</button>
                            </form>
                            <form method="post" action="<?= e(url('orders/status')) ?>" onsubmit="return confirm('¿Cancelar el pedido #<?= e($order['id']) ?>? Los productos vuelven al inventario.')">
                                <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                                <input type="hidden" name="id" value="<?= e($order['id']) ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Cancelar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
