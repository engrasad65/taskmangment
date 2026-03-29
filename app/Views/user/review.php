<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php use App\Core\Csrf; use App\Core\Validator; ?>
<h3>Review Paper: <?= Validator::e($paper['title']) ?></h3>
<p><strong>Class:</strong> <?= Validator::e($paper['class_name']) ?> | <strong>Subject:</strong> <?= Validator::e($paper['subject_name']) ?></p>
<form method="post" action="/paper/items/update">
    <input type="hidden" name="_csrf" value="<?= Csrf::token() ?>">
    <input type="hidden" name="paper_id" value="<?= (int)$paper['id'] ?>">
    <table class="table table-bordered"><tr><th>Order</th><th>Type</th><th>Question</th><th>Marks</th><th>Actions</th></tr>
    <?php $total = 0; foreach($items as $item): $total += (int)$item['marks']; ?>
        <tr>
            <td><input type="number" class="form-control" name="positions[<?= (int)$item['item_id'] ?>]" value="<?= (int)$item['position'] ?>"></td>
            <td><?= Validator::e($item['question_type']) ?></td>
            <td><?= Validator::e($item['question_text']) ?></td>
            <td><?= (int)$item['marks'] ?></td>
            <td>
                <button class="btn btn-warning btn-sm" name="replace_item_id" value="<?= (int)$item['item_id'] ?>">Replace</button>
                <button class="btn btn-danger btn-sm" name="remove_item_id" value="<?= (int)$item['item_id'] ?>">Remove</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </table>
    <p><strong>Total Marks:</strong> <?= $total ?></p>
    <button class="btn btn-primary">Apply Changes</button>
</form>
<form method="post" action="/paper/finalize" class="mt-3">
    <input type="hidden" name="_csrf" value="<?= Csrf::token() ?>">
    <input type="hidden" name="paper_id" value="<?= (int)$paper['id'] ?>">
    <button class="btn btn-success">Finalize Paper</button>
</form>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
