<?php
$pageTitle = $pageTitle ?? 'Admin';
$adminNav  = $adminNav ?? '';

$pendingSubs     = (int) $pdo->query("SELECT COUNT(*) FROM article_submissions WHERE status = 'pending'")->fetchColumn();
$pendingComments = (int) $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();

$adminLinks = [
    'dashboard'     => ['admin/index.php', 'Dashboard', 0],
    'articles'      => ['admin/articles.php', 'Articles', 0],
    'submissions'   => ['admin/submissions.php', 'Submissions', $pendingSubs],
    'categories'    => ['admin/categories.php', 'Categories', 0],
    'announcements' => ['admin/announcements.php', 'Announcements', 0],
    'comments'      => ['admin/comments.php', 'Comments', $pendingComments],
    'users'         => ['admin/users.php', 'Users', 0],
    'reports'       => ['admin/reports.php', 'Reports', 0],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | NewsDesk Admin</title>
    <link rel="icon" href="<?= url('assets/logo.png') ?>">
    <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body class="admin-body">
<div class="admin-layout">
    <aside class="admin-side" id="admin-side">
        <a class="admin-brand" href="<?= url('admin/index.php') ?>">
            <img src="<?= url('assets/logo.png') ?>" alt="NSBM"> <span>NewsDesk<br><small>Admin panel</small></span>
        </a>
        <nav class="admin-nav">
            <?php foreach ($adminLinks as $key => [$link, $label, $count]): ?>
                <a href="<?= url($link) ?>" class="<?= $adminNav === $key ? 'on' : '' ?>">
                    <?= e($label) ?>
                    <?php if ($count > 0): ?><span class="count"><?= $count ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-side-foot">
            <a href="<?= url('index.php') ?>" target="_blank">View site ↗</a>
            <a href="<?= url('user/profile.php') ?>">My profile</a>
            <a href="<?= url('auth/logout.php') ?>">Log out</a>
        </div>
    </aside>

    <div class="admin-main">
        <div class="admin-top">
            <button class="menu-toggle admin-toggle" type="button" data-toggle="admin-side" aria-label="Menu">&#9776;</button>
            <h1><?= e($pageTitle) ?></h1>
            <span class="hint">Signed in as <strong><?= e($_SESSION['name']) ?></strong></span>
        </div>

        <?php foreach (get_flashes() as $flashMsg): ?>
            <div class="alert alert-<?= e($flashMsg['type']) ?> inline" role="alert">
                <?= e($flashMsg['text']) ?>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        <?php endforeach; ?>
