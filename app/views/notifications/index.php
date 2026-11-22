<?php
$icons = [
    'vaccine_due' => 'bi-virus text-success',
    'treatment_control' => 'bi-clipboard2-pulse text-primary',
    'low_stock' => 'bi-exclamation-triangle text-warning',
];
?>
<div class="card content-card">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <div class="text-muted">
                <?= e($unread) ?> sin leer · vacunas y controles de los próximos días, y productos con stock bajo
            </div>
            <div class="d-flex gap-2">
                <form method="post" action="<?= e(url('notifications/scan')) ?>">
                    <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-arrow-repeat"></i> Revisar ahora</button>
                </form>
                <?php if ($unread > 0): ?>
                    <form method="post" action="<?= e(url('notifications/readAll')) ?>">
                        <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-check2-all"></i> Marcar todas como leídas</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$notifications): ?>
            <div class="alert alert-light border mb-0">No hay notificaciones.</div>
        <?php endif; ?>

        <div class="list-group">
            <?php foreach ($notifications as $notification): ?>
                <?php $overdue = !empty($notification['due_date']) && $notification['due_date'] < date('Y-m-d') && $notification['type'] !== 'low_stock'; ?>
                <div class="list-group-item d-flex gap-3 align-items-start <?= $notification['is_read'] ? 'text-muted' : '' ?>">
                    <i class="bi <?= e($icons[$notification['type']] ?? 'bi-bell') ?> fs-4"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">
                            <?= e($notification['title']) ?>
                            <?php if ($overdue): ?><span class="badge bg-danger ms-1">Vencida</span><?php endif; ?>
                            <?php if (!$notification['is_read']): ?><span class="badge bg-primary ms-1">Nueva</span><?php endif; ?>
                        </div>
                        <div><?= e($notification['message']) ?></div>
                        <small class="text-muted">Generada el <?= e($notification['created_at']) ?></small>
                    </div>
                    <form method="post" action="<?= e(url('notifications/read')) ?>">
                        <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                        <input type="hidden" name="id" value="<?= e($notification['id']) ?>">
                        <input type="hidden" name="link" value="<?= e($notification['link'] ?? '') ?>">
                        <button class="btn btn-sm btn-outline-primary text-nowrap" type="submit">
                            <?= $notification['is_read'] ? 'Ver' : 'Ver y marcar leída' ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
