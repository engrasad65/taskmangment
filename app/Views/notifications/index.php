<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Notifications</h3>
    <form method="post" action="/notifications/read-all">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
        <button class="btn btn-sm btn-outline-primary">Mark all as read</button>
    </form>
</div>
<div class="list-group">
    <?php foreach ($notifications as $notification): ?>
        <div class="list-group-item <?= (int) $notification['is_read'] === 0 ? 'list-group-item-warning' : '' ?>">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><?= \App\Core\Validator::e($notification['title']) ?></h6>
                <small><?= \App\Core\Validator::e($notification['created_at']) ?></small>
            </div>
            <p class="mb-1"><?= \App\Core\Validator::e($notification['message']) ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
