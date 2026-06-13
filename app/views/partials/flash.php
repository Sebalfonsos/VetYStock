<?php if ($success = flash('success')): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error = flash('error')): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

