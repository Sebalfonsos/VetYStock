<?php
$pageTitle = ($title ?? 'Laudi') . ' | ' . config('app.name');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .sidebar { min-height: 100vh; background: #17223b; }
        .sidebar .nav-link { color: rgba(255,255,255,.82); }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.08); }
        .stat-card { border: 0; box-shadow: 0 8px 24px rgba(15, 23, 42, .08); }
        .content-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); }
    </style>
</head>
<body>
<?php if (!Auth::check()): ?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#17223b;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e(url('home/index')) ?>"><?= e(config('app.name')) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('home/index')) ?>">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('home/catalog')) ?>">Tienda</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('home/about')) ?>">Nosotros</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('home/contact')) ?>">Contacto</a></li>
                <li class="nav-item"><a class="btn btn-outline-light ms-lg-2" href="<?= e(url('auth/login')) ?>">Ingresar</a></li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="container-fluid">
    <div class="row">
        <?php if (Auth::check()): ?>
            <aside class="col-md-2 sidebar p-3 text-white">
                <?php require __DIR__ . '/../partials/sidebar.php'; ?>
            </aside>
            <main class="col-md-10 p-4">
        <?php else: ?>
            <main class="col-12 p-0">
                <div class="container py-4">
        <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1"><?= e($title ?? 'Aplicación') ?></h1>
                    <?php if (Auth::check()): ?>
                        <div class="text-muted">Sesión activa: <?= e(Auth::user()['name'] ?? '') ?> · <?= e(Auth::role()) ?></div>
                    <?php endif; ?>
                </div>
                <?php if (Auth::check()): ?>
                    <a class="btn btn-outline-danger" href="<?= e(url('auth/logout')) ?>"><i class="bi bi-box-arrow-right"></i> Salir</a>
                <?php endif; ?>
            </div>

            <?php require __DIR__ . '/../partials/flash.php'; ?>

            <?php require $viewFile; ?>
        <?php if (!Auth::check()): ?>
                </div>
            </main>
        <?php else: ?>
            </main>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!Auth::check()): ?>
<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/06/12/18/20260612182458-IZQ1YWST.js" defer></script>
<?php endif; ?>
</body>
</html>
