<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Login</h4>
                <?php if (!empty($error)): ?><div class="alert alert-danger"><?= App\Core\Validator::e($error) ?></div><?php endif; ?>
                <form method="post" action="/login">
                    <input type="hidden" name="_csrf" value="<?= App\Core\Csrf::token() ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input class="form-control" type="password" name="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Sign in</button>
                </form>
                <hr>
                <small>Default admin: admin@school.local / admin12345</small><br>
                <small>Default school head: head@school.local / head12345</small>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
