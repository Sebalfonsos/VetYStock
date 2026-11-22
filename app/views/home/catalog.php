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
                        <?php if ((int) $product['current_stock'] > 0 && ($product['status'] ?? 'active') === 'active'): ?>
                            <div class="d-flex gap-2 mt-3">
                                <input type="number" class="form-control form-control-sm cart-qty" style="width:5rem" min="1" max="<?= e($product['current_stock']) ?>" value="1" aria-label="Cantidad de <?= e($product['name']) ?>">
                                <button type="button" class="btn btn-sm btn-success flex-grow-1 cart-add" data-product="<?= e($product['id']) ?>">
                                    <i class="bi bi-cart-plus"></i> Agregar al carrito
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<script>
document.querySelectorAll('.cart-add').forEach((button) => {
    button.addEventListener('click', async () => {
        const quantity = parseInt(button.parentElement.querySelector('.cart-qty').value, 10) || 1;
        button.disabled = true;
        await window.VetCart.add([{ product_id: Number(button.dataset.product), quantity }]);
        button.disabled = false;
    });
});
</script>
