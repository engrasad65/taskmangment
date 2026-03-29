<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php use App\Core\Csrf; use App\Core\Validator; ?>
<h3>School Head Dashboard</h3>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= Validator::e($error) ?></div><?php endif; ?>
<div class="card"><div class="card-body">
    <h5>Create Exam Paper</h5>
    <form method="post" action="/paper/generate" class="row g-2">
        <input type="hidden" name="_csrf" value="<?= Csrf::token() ?>">
        <div class="col-md-4"><input class="form-control" name="title" placeholder="Exam title" required></div>
        <div class="col-md-3"><select class="form-select" name="class_id" required><?php foreach($classes as $c): ?><option value="<?= (int)$c['id'] ?>"><?= Validator::e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select class="form-select" name="subject_id" required><?php foreach($subjects as $s): ?><option value="<?= (int)$s['id'] ?>"><?= Validator::e($s['class_name'].' - '.$s['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-12">
            <label class="form-label">Select Chapters</label>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach($chapters as $ch): ?>
                    <label class="border rounded p-2"><input type="checkbox" name="chapter_ids[]" value="<?= (int)$ch['id'] ?>"> <?= Validator::e($ch['class_name'].' / '.$ch['subject_name'].' / '.$ch['name']) ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-12"><label class="form-label">Question Quantity by Type</label><div class="row g-2">
            <?php foreach($questionTypeOptions as $type): ?>
                <div class="col-md-4"><label class="form-label small"><?= Validator::e($type) ?></label><input class="form-control" type="number" min="0" name="type_config[<?= Validator::e($type) ?>]" value="0"></div>
            <?php endforeach; ?>
        </div></div>
        <div class="col-12"><button class="btn btn-primary">Generate Paper</button></div>
    </form>
</div></div>

<div class="card mt-3"><div class="card-body">
    <h5>My Papers</h5>
    <table class="table table-sm"><tr><th>Title</th><th>Status</th><th>Created</th><th>Actions</th></tr>
        <?php foreach($papers as $paper): ?><tr><td><?= Validator::e($paper['title']) ?></td><td><?= Validator::e($paper['status']) ?></td><td><?= Validator::e($paper['created_at']) ?></td><td><a class="btn btn-sm btn-outline-primary" href="/paper/review?id=<?= (int)$paper['id'] ?>">Review</a> <a class="btn btn-sm btn-outline-secondary" target="_blank" href="/paper/print?id=<?= (int)$paper['id'] ?>">Print</a></td></tr><?php endforeach; ?>
    </table>
</div></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
