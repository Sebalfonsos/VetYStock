<?php
$groupedProducts = [];
foreach ($products as $product) {
    $groupedProducts[$product['category_name']][] = $product;
}
?>

<div class="d-flex justify-content-between align-items-end mb-3">
    <div>
        <h1 class="h3 mb-1">Tienda</h1>
        <p class="text-muted mb-0">Productos visibles para clientes, sincronizados con el inventario del administrador.</p>
    </div>
</div>

<?php foreach ($groupedProducts as $categoryName => $items): ?>
    <h2 class="h4 mt-4 mb-3"><?= e($categoryName) ?></h2>
    <div class="row g-3">
        <?php foreach ($items as $product): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card content-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge text-bg-primary"><?= e($product['code']) ?></span>
                            <span class="badge <?= ((int) $product['current_stock'] > 0) ? 'text-bg-success' : 'text-bg-danger' ?>">
                                <?= (int) $product['current_stock'] > 0 ? 'Disponible' : 'Agotado' ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <?php
                            $groupLabel = [
                                'domestic' => 'Doméstico',
                                'wild' => 'Salvaje',
                                'farm' => 'Granja',
                            ][$product['animal_group'] ?? 'domestic'] ?? 'Doméstico';
                            ?>
                            <span class="badge text-bg-secondary"><?= e($groupLabel) ?></span>
                        </div>
                        <h3 class="h5 mb-2"><?= e($product['name']) ?></h3>
                        <p class="text-muted mb-3"><?= e($product['description'] ?? 'Producto disponible en catálogo.') ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted">Precio</div>
                                <div class="fw-bold fs-5">$<?= number_format((float) $product['sale_price'], 0, ',', '.') ?></div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">Stock</div>
                                <div class="fw-semibold"><?= e($product['current_stock']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
