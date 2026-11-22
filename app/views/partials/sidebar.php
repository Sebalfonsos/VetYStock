<div class="d-flex align-items-center mb-4">
    <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center me-2" style="width:40px;height:40px;">
        <strong>LV</strong>
    </div>
    <div>
        <div class="fw-bold">Laudi Vet</div>
        <small class="text-white-50">MVC Demo</small>
    </div>
</div>

<nav class="nav flex-column gap-1">
    <a class="nav-link rounded" href="<?= e(url('dashboard/index')) ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a class="nav-link rounded" href="<?= e(url('users/index')) ?>"><i class="bi bi-people me-2"></i>Usuarios</a>
    <a class="nav-link rounded" href="<?= e(url('roles/index')) ?>"><i class="bi bi-shield-lock me-2"></i>Roles</a>
    <a class="nav-link rounded" href="<?= e(url('categories/index')) ?>"><i class="bi bi-tags me-2"></i>Categorías</a>
    <a class="nav-link rounded" href="<?= e(url('products/index')) ?>"><i class="bi bi-box-seam me-2"></i>Productos</a>
    <a class="nav-link rounded" href="<?= e(url('inventory/index')) ?>"><i class="bi bi-truck me-2"></i>Inventario</a>
    <a class="nav-link rounded" href="<?= e(url('animals/index')) ?>"><i class="bi bi-heart-pulse me-2"></i>Animales</a>
    <a class="nav-link rounded" href="<?= e(url('observations/index')) ?>"><i class="bi bi-journal-text me-2"></i>Observaciones</a>
    <a class="nav-link rounded" href="<?= e(url('treatments/index')) ?>"><i class="bi bi-clipboard2-pulse me-2"></i>Tratamientos</a>
    <a class="nav-link rounded" href="<?= e(url('vaccines/index')) ?>"><i class="bi bi-virus me-2"></i>Vacunas</a>
    <a class="nav-link rounded" href="<?= e(url('medical-history/index')) ?>"><i class="bi bi-file-medical me-2"></i>Historial médico</a>
    <a class="nav-link rounded d-flex align-items-center" href="<?= e(url('notifications/index')) ?>">
        <i class="bi bi-bell me-2"></i>Notificaciones
        <span id="notifications-badge" class="badge rounded-pill bg-danger ms-auto" hidden></span>
    </a>
    <?php if (Auth::role() === 'Administrador'): ?>
        <a class="nav-link rounded" href="<?= e(url('reports/index')) ?>"><i class="bi bi-bar-chart-line me-2"></i>Reportes</a>
    <?php endif; ?>
</nav>

<script>
fetch(<?= json_encode(url('notifications/count')) ?>)
    .then((response) => response.json())
    .then((result) => {
        const badge = document.getElementById('notifications-badge');
        if (result.unread > 0) {
            badge.textContent = result.unread;
            badge.hidden = false;
        }
    })
    .catch(() => {});
</script>

