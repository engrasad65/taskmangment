<?php use App\Core\Csrf; use App\Core\Session; use App\Core\Validator; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= \App\Core\Validator::e($config['app']['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><?= \App\Core\Validator::e($config['app']['name']) ?></a>
        <div class="ms-auto">
            <?php if (\App\Core\Session::has('user')): ?>
                <form method="post" action="/logout" class="d-inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container pb-5">
