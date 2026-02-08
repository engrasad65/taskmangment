<?php require __DIR__ . '/../layouts/header.php'; ?>
<h3 class="mb-3">Worker Dashboard</h3>
<?php if (!empty($message)): ?><div class="alert alert-warning"><?= \App\Core\Validator::e($message) ?></div><?php endif; ?>
<div class="table-responsive">
<table class="table table-bordered bg-white">
    <thead class="table-light"><tr><th>Task</th><th>Site</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?= \App\Core\Validator::e($task['title']) ?><br><small><?= \App\Core\Validator::e($task['description'] ?? '') ?></small></td>
            <td><?= \App\Core\Validator::e($task['site_name'] ?? '-') ?></td>
            <td><span class="badge bg-secondary"><?= \App\Core\Validator::e($task['status']) ?></span></td>
            <td>
                <?php if ($task['status'] === 'assigned'): ?>
                    <form method="post" action="/worker/start" enctype="multipart/form-data" class="mb-2">
                        <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                        <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                        <input type="file" name="start_image" accept="image/*" class="form-control form-control-sm mb-1" required>
                        <button class="btn btn-sm btn-success">Start Work</button>
                    </form>
                <?php elseif ($task['status'] === 'in_progress'): ?>
                    <form method="post" action="/worker/end" enctype="multipart/form-data">
                        <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                        <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                        <input type="file" name="end_image" accept="image/*" class="form-control form-control-sm mb-1" required>
                        <textarea name="progress_note" class="form-control form-control-sm mb-1" placeholder="Progress note" required></textarea>
                        <button class="btn btn-sm btn-primary">Submit End of Day</button>
                    </form>
                <?php else: ?>
                    <span class="text-success fw-semibold">Completed</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
