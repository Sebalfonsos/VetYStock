<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card content-card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h2 class="h4 mb-1"><?= e(config('app.name')) ?></h2>
                    <p class="text-muted mb-0">Acceso al sistema académico</p>
                </div>
                <form method="post" action="<?= e(url('auth/authenticate')) ?>">
                    <input type="hidden" name="_token" value="<?= e(Csrf::token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@laudi.test" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Admin1234!" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Ingresar</button>
                </form>
            </div>
        </div>
    </div>
</div>

