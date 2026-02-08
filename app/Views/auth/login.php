<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Login</h4>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= \App\Core\Validator::e($error) ?></div>
                <?php endif; ?>
                <form method="post" action="/login">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </form>
                <small class="text-muted d-block mt-3">Worker and Admin accounts are seeded via setup script.</small>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
