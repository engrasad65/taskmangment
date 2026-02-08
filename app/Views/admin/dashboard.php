<?php require __DIR__ . '/../layouts/header.php'; ?>
<h3 class="mb-3">Admin Dashboard</h3>
<?php if (!empty($message)): ?><div class="alert alert-warning"><?= \App\Core\Validator::e($message) ?></div><?php endif; ?>
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h5>Create Worker</h5>
            <form method="post" action="/admin/users/create">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                <input class="form-control mb-2" name="username" placeholder="Username" required>
                <input class="form-control mb-2" name="full_name" placeholder="Full name" required>
                <input class="form-control mb-2" name="password" type="password" minlength="8" placeholder="Password (min 8 chars)" required>
                <button class="btn btn-dark btn-sm">Create Worker</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h5>Add Site</h5>
            <form method="post" action="/admin/sites/create">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                <input class="form-control mb-2" name="name" placeholder="Site name" required>
                <input class="form-control mb-2" name="location" placeholder="Location" required>
                <button class="btn btn-primary btn-sm">Save Site</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h5>Create Task</h5>
            <form method="post" action="/admin/tasks/create">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                <input class="form-control mb-2" name="title" placeholder="Task title" required>
                <textarea class="form-control mb-2" name="description" placeholder="Description"></textarea>
                <select class="form-select mb-2" name="user_id" required>
                    <option value="">Worker</option>
                    <?php foreach ($workers as $worker): ?>
                        <option value="<?= (int) $worker['id'] ?>"><?= \App\Core\Validator::e($worker['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-select mb-2" name="site_id" required>
                    <option value="">Site</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= (int) $site['id'] ?>"><?= \App\Core\Validator::e($site['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-success btn-sm">Assign Task</button>
            </form>
        </div></div>
    </div>
</div>

<div class="card mb-4"><div class="card-body">
    <h5>Reports (All Types)</h5>
    <div class="row g-2 mb-2">
        <div class="col-md-3"><a href="/admin/reports/tasks-detailed" class="btn btn-outline-primary btn-sm w-100">Detailed Tasks</a></div>
        <div class="col-md-3"><a href="/admin/reports/site-progress" class="btn btn-outline-primary btn-sm w-100">Site Progress</a></div>
        <div class="col-md-3"><a href="/admin/reports/worker-performance" class="btn btn-outline-primary btn-sm w-100">Worker Performance</a></div>
        <div class="col-md-3"><a href="/admin/reports/daily-summary" class="btn btn-outline-primary btn-sm w-100">Daily Summary</a></div>
    </div>
    <form method="post" action="/admin/tasks/import" enctype="multipart/form-data" class="d-flex gap-2">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
        <input type="file" class="form-control" name="import_file" accept=".csv,text/csv" required>
        <button class="btn btn-outline-secondary btn-sm">Import CSV</button>
    </form>
    <small class="text-muted">CSV headers: title,description,user_id,site_id</small>
</div></div>

<div class="card mb-4"><div class="card-body">
    <h5>Recent Notifications</h5>
    <ul class="list-group">
        <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
            <li class="list-group-item d-flex justify-content-between">
                <span><?= \App\Core\Validator::e($notification['title']) ?> - <?= \App\Core\Validator::e($notification['message']) ?></span>
                <small><?= \App\Core\Validator::e($notification['created_at']) ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
</div></div>

<div class="table-responsive">
<table class="table table-striped table-bordered bg-white">
    <thead class="table-light"><tr><th>ID</th><th>Task</th><th>Site</th><th>Worker</th><th>Status</th><th>Started</th><th>Ended</th><th>Progress</th><th>Action</th></tr></thead>
    <tbody>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?= (int) $task['id'] ?></td>
                <td><?= \App\Core\Validator::e($task['title']) ?></td>
                <td><?= \App\Core\Validator::e($task['site_name'] ?? '-') ?></td>
                <td><?= \App\Core\Validator::e($task['full_name'] ?? '-') ?></td>
                <td><?= \App\Core\Validator::e($task['status']) ?></td>
                <td><?= \App\Core\Validator::e($task['started_at'] ?? '-') ?></td>
                <td><?= \App\Core\Validator::e($task['ended_at'] ?? '-') ?></td>
                <td><?= \App\Core\Validator::e($task['progress_note'] ?? '-') ?></td>
                <td>
                    <form method="post" action="/admin/tasks/delete" onsubmit="return confirm('Delete this task?')">
                        <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                        <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
