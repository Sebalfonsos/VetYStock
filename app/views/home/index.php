<div class="bg-white rounded-4 p-4 p-lg-5 mb-4 shadow-sm">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="badge text-bg-primary mb-3">Tienda veterinaria y clínica</span>
            <h1 class="display-5 fw-bold mb-3">Cuida su salud y encuentra lo que necesita en un solo lugar</h1>
            <p class="lead text-muted mb-4">
                Productos, vacunas, tratamientos y atención clínica pensados para mascotas y animales de producción.
                Todo el catálogo visible aquí se alimenta desde el panel administrador.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary btn-lg" href="<?= e(url('home/catalog')) ?>">Ver tienda</a>
                <a class="btn btn-outline-secondary btn-lg" href="<?= e(url('auth/login')) ?>">Acceso administrador</a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Resumen del sistema</h2>
                    <div class="row g-3">
                        <?php foreach ($heroStats as $stat): ?>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <div class="fw-bold display-6 mb-1"><?= e($stat['value']) ?></div>
                                    <small class="text-muted"><?= e($stat['label']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-end mb-3">
    <div>
        <h2 class="h3 mb-1">Categorías destacadas</h2>
        <p class="text-muted mb-0">Lo que ves aquí se actualiza desde el administrador.</p>
    </div>
    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('home/catalog')) ?>">Ver todo</a>
</div>

<div class="row g-3 mb-5">
    <?php foreach ($categories as $category): ?>
        <div class="col-md-4 col-xl-3">
            <div class="card content-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge text-bg-success">Activo</span>
                        <i class="bi bi-tags fs-4 text-primary"></i>
                    </div>
                    <h3 class="h5 mb-2"><?= e($category['name']) ?></h3>
                    <p class="text-muted mb-0"><?= e($category['description'] ?? 'Categoría disponible en tienda.') ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-dark text-white rounded-4 p-4 p-lg-5">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <h2 class="h3 mb-2">Atención, inventario y expediente en un mismo flujo</h2>
            <p class="mb-0 text-white-50">Ideal para demostrar el proyecto universitario con una experiencia pública y otra administrativa.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn btn-light btn-lg" href="<?= e(url('home/contact')) ?>">Contactar</a>
        </div>
    </div>
</div>

