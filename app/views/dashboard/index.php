<div class="row g-3 mb-4">
    <?php foreach ($cards as $card): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card text-white bg-<?= e($card['color']) ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small opacity-75"><?= e($card['label']) ?></div>
                            <div class="display-6 fw-bold"><?= e($card['value']) ?></div>
                        </div>
                        <i class="bi <?= e($card['icon']) ?>" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card content-card">
    <div class="card-body">
        <h3 class="h5 mb-3">Animales recientes</h3>
        <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Identificación</th>
                    <th>Propietario</th>
                    <th>Especie</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentAnimals as $animal): ?>
                    <tr>
                        <td><?= e($animal['name'] ?? '') ?></td>
                        <td><?= e($animal['identification_code'] ?? '') ?></td>
                        <td><?= e($animal['owner_name'] ?? '-') ?></td>
                        <td><?= e($animal['species_name'] ?? '-') ?></td>
                        <td><?= e($animal['status'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

