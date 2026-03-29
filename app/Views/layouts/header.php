<?php use App\Core\Csrf; use App\Core\Session; use App\Core\Validator; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Validator::e($config['app']['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><?= Validator::e($config['app']['name']) ?></a>
        <?php if (Session::has('user')): $user = Session::get('user'); ?>
            <div class="ms-auto d-flex gap-2 align-items-center">
                <span class="badge bg-light text-primary text-uppercase"><?= Validator::e($user['role']) ?></span>
                <span class="text-white small"><?= Validator::e($user['full_name']) ?></span>
                <form method="post" action="/logout" class="d-inline">
                    <input type="hidden" name="_csrf" value="<?= Csrf::token() ?>">
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</nav>
<div class="container pb-5">
