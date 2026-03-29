<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php use App\Core\Csrf; use App\Core\Validator; ?>
<h3 class="mb-3">Admin Dashboard</h3>
<?php if (!empty($message)): ?><div class="alert alert-info"><?= Validator::e($message) ?></div><?php endif; ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h5>Create User</h5>
            <form method="post" action="/admin/users/create" class="row g-2">
                <input type="hidden" name="_csrf" value="<?= Csrf::token() ?>">
                <div class="col-12"><input class="form-control" name="full_name" placeholder="Full name" required></div>
                <div class="col-12"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
                <div class="col-12"><input class="form-control" type="password" name="password" placeholder="Password (min 8)" required></div>
                <div class="col-12"><select class="form-select" name="role"><option value="user">School Head</option><option value="admin">Admin</option></select></div>
                <div class="col-12"><button class="btn btn-primary">Create</button></div>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <h5>Users</h5>
            <div class="table-responsive"><table class="table table-sm">
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <form method="post" action="/admin/users/update">
                            <input type="hidden" name="_csrf" value="<?= Csrf::token() ?>">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <td><input class="form-control form-control-sm" name="full_name" value="<?= Validator::e($u['full_name']) ?>"></td>
                            <td><input class="form-control form-control-sm" name="email" value="<?= Validator::e($u['email']) ?>"></td>
                            <td><select class="form-select form-select-sm" name="role"><option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option><option value="user" <?= $u['role']==='user'?'selected':'' ?>>School Head</option></select></td>
                            <td class="d-flex gap-1">
                                <button class="btn btn-success btn-sm">Update</button>
                        </form>
                        <form method="post" action="/admin/users/delete" onsubmit="return confirm('Delete user?')">
                            <input type="hidden" name="_csrf" value="<?= Csrf::token() ?>"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                            </td>
                    </tr>
                <?php endforeach; ?>
            </table></div>
        </div></div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Add Class</h6><form method="post" action="/admin/classes/create"><input type="hidden" name="_csrf" value="<?= Csrf::token() ?>"><input class="form-control mb-2" name="name" placeholder="Grade e.g. Grade 6"><button class="btn btn-primary btn-sm">Add</button></form></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Add Subject</h6><form method="post" action="/admin/subjects/create"><input type="hidden" name="_csrf" value="<?= Csrf::token() ?>"><select class="form-select mb-2" name="class_id"><?php foreach ($classes as $c): ?><option value="<?= (int)$c['id'] ?>"><?= Validator::e($c['name']) ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="name" placeholder="Subject"><button class="btn btn-primary btn-sm">Add</button></form></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Add Chapter</h6><form method="post" action="/admin/chapters/create"><input type="hidden" name="_csrf" value="<?= Csrf::token() ?>"><select class="form-select mb-2" name="subject_id"><?php foreach ($subjects as $s): ?><option value="<?= (int)$s['id'] ?>"><?= Validator::e($s['class_name'].' - '.$s['name']) ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="name" placeholder="Chapter"><button class="btn btn-primary btn-sm">Add</button></form></div></div></div>
</div>

<div class="card mt-3"><div class="card-body">
    <h5>Question Bank</h5>
    <form method="post" action="/admin/questions/create" class="row g-2">
        <input type="hidden" name="_csrf" value="<?= Csrf::token() ?>">
        <div class="col-md-2"><select class="form-select" name="class_id"><?php foreach($classes as $c): ?><option value="<?= (int)$c['id'] ?>"><?= Validator::e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="subject_id"><?php foreach($subjects as $s): ?><option value="<?= (int)$s['id'] ?>"><?= Validator::e($s['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="chapter_id"><?php foreach($chapters as $ch): ?><option value="<?= (int)$ch['id'] ?>"><?= Validator::e($ch['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="question_type"><?php foreach($questionTypeOptions as $t): ?><option><?= Validator::e($t) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-1"><input class="form-control" type="number" name="marks" placeholder="Marks" min="1" required></div>
        <div class="col-md-1"><input class="form-control" name="difficulty_level" placeholder="Level"></div>
        <div class="col-md-2"><input class="form-control" name="slo_reference" placeholder="SLO ref"></div>
        <div class="col-12"><textarea class="form-control" name="question_text" placeholder="Question" required></textarea></div>
        <div class="col-12"><button class="btn btn-primary">Save Question</button></div>
    </form>

    <form method="get" class="row g-2 mt-3">
        <div class="col-md-2"><input class="form-control" name="q" placeholder="Search text" value="<?= Validator::e($filters['q']) ?>"></div>
        <div class="col-md-2"><select class="form-select" name="class_id"><option value="">All classes</option><?php foreach($classes as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (string)$filters['class_id']===(string)$c['id']?'selected':'' ?>><?= Validator::e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="subject_id"><option value="">All subjects</option><?php foreach($subjects as $s): ?><option value="<?= (int)$s['id'] ?>" <?= (string)$filters['subject_id']===(string)$s['id']?'selected':'' ?>><?= Validator::e($s['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="chapter_id"><option value="">All chapters</option><?php foreach($chapters as $ch): ?><option value="<?= (int)$ch['id'] ?>" <?= (string)$filters['chapter_id']===(string)$ch['id']?'selected':'' ?>><?= Validator::e($ch['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="question_type"><option value="">All types</option><?php foreach($questionTypeOptions as $t): ?><option value="<?= Validator::e($t) ?>" <?= $filters['question_type']===$t?'selected':'' ?>><?= Validator::e($t) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
    </form>

    <div class="table-responsive mt-3"><table class="table table-sm align-middle"><tr><th>ID</th><th>Question</th><th>Tags</th><th>Marks</th><th>Actions</th></tr>
        <?php foreach ($questions as $q): ?>
        <tr>
            <td><?= (int)$q['id'] ?></td><td><?= Validator::e($q['question_text']) ?></td>
            <td><small><?= Validator::e($q['class_name'].' / '.$q['subject_name'].' / '.$q['chapter_name'].' / '.$q['question_type']) ?></small></td>
            <td><?= (int)$q['marks'] ?></td>
            <td><form method="post" action="/admin/questions/delete" onsubmit="return confirm('Delete question?')"><input type="hidden" name="_csrf" value="<?= Csrf::token() ?>"><input type="hidden" name="id" value="<?= (int)$q['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form></td>
        </tr>
        <?php endforeach; ?>
    </table></div>

    <nav><ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul></nav>
</div></div>

<div class="card mt-3"><div class="card-body">
    <h5>All Generated Papers</h5>
    <table class="table table-sm"><tr><th>Title</th><th>Class</th><th>Subject</th><th>Created By</th><th>Status</th><th>Print</th></tr>
        <?php foreach($papers as $paper): ?><tr><td><?= Validator::e($paper['title']) ?></td><td><?= Validator::e($paper['class_name']) ?></td><td><?= Validator::e($paper['subject_name']) ?></td><td><?= Validator::e($paper['created_by_name']) ?></td><td><?= Validator::e($paper['status']) ?></td><td><a class="btn btn-outline-secondary btn-sm" target="_blank" href="/paper/print?id=<?= (int)$paper['id'] ?>">Print</a></td></tr><?php endforeach; ?>
    </table>
</div></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
