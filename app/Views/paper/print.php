<!doctype html>
<html><head><meta charset="utf-8"><title>Print Paper</title><style>body{font-family:Arial,sans-serif;margin:30px} .q{margin-bottom:18px} .meta{margin-bottom:20px}</style></head><body>
<h2><?= htmlspecialchars($paper['title'], ENT_QUOTES, 'UTF-8') ?></h2>
<div class="meta">Class: <?= htmlspecialchars($paper['class_name'], ENT_QUOTES, 'UTF-8') ?> | Subject: <?= htmlspecialchars($paper['subject_name'], ENT_QUOTES, 'UTF-8') ?></div>
<?php $total=0; foreach($items as $idx => $item): $total += (int)$item['marks']; ?>
<div class="q"><strong>Q<?= $idx+1 ?> (<?= (int)$item['marks'] ?> marks - <?= htmlspecialchars($item['question_type'], ENT_QUOTES, 'UTF-8') ?>):</strong><br><?= nl2br(htmlspecialchars($item['question_text'], ENT_QUOTES, 'UTF-8')) ?></div>
<?php endforeach; ?>
<hr><strong>Total Marks: <?= $total ?></strong>
<script>window.print()</script>
</body></html>
