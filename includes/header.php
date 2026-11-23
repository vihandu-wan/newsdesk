<?php
$pageTitle = $pageTitle ?? 'NSBM NewsDesk';
$activeNav = $activeNav ?? '';
$navCategories = get_categories($pdo);
$breaking = $pdo->query(
    "SELECT id, title FROM articles
     WHERE status = 'published' AND is_breaking = 1
     ORDER BY published_at DESC LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= url('assets/logo.png') ?>">
    <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body>
<header>
    <div class="bar">
        <a href="<?= url('index.php') ?>"><img class="logo" src="<?= url('assets/logo.png') ?>" alt="NSBM"></a>
        <a class="brand" href="<?= url('index.php') ?>">NewsDesk</a>

        <button class="menu-toggle" type="button" data-toggle="main-nav" aria-label="Open menu">&#9776;</button>

        <nav id="main-nav">
            <a href="<?= url('index.php') ?>" class="<?= $activeNav === 'Home' ? 'on' : '' ?>">Home</a>
            <?php foreach (array_slice($navCategories, 0, 6) as $navCat): ?>
                <a href="<?= url('categories.php?name=' . urlencode($navCat['name'])) ?>"
                   class="<?= $activeNav === $navCat['name'] ? 'on' : '' ?>"><?= e($navCat['name']) ?></a>
            <?php endforeach; ?>
            <a href="<?= url('announcements/index.php') ?>" class="<?= $activeNav === 'Announcements' ? 'on' : '' ?>">Announcements</a>
        </nav>

        <form class="search-form" action="<?= url('search.php') ?>" method="get">
            <input class="search" name="q" placeholder="Search news" aria-label="Search news"
                   value="<?= e($_GET['q'] ?? '') ?>">
        </form>

        <?php if (is_logged_in()): ?>
            <div class="user-menu">
                <button class="login" type="button" data-toggle="user-dropdown">
                    <?= e(explode(' ', $_SESSION['name'])[0]) ?> &#9662;
                </button>
                <div class="dropdown" id="user-dropdown">
                    <?php if (is_admin()): ?>
                        <a href="<?= url('admin/index.php') ?>"><strong>Admin panel</strong></a>
                    <?php endif; ?>
                    <a href="<?= url('user/dashboard.php') ?>">My dashboard</a>
                    <a href="<?= url('submissions/create.php') ?>">Submit an article</a>
                    <a href="<?= url('submissions/my_submissions.php') ?>">My submissions</a>
                    <a href="<?= url('bookmarks/index.php') ?>">Saved articles</a>
                    <a href="<?= url('subscriptions/index.php') ?>">My subscriptions</a>
                    <a href="<?= url('user/profile.php') ?>">Profile</a>
                    <a href="<?= url('auth/logout.php') ?>">Log out</a>
                </div>
            </div>
        <?php else: ?>
            <a class="login" href="<?= url('auth/login.php') ?>">Log in</a>
            <a class="login outline" href="<?= url('auth/register.php') ?>">Register</a>
        <?php endif; ?>
    </div>

    <?php if ($breaking): ?>
        <div class="ticker">
            <span class="ticker-label">Breaking</span>
            <div class="ticker-track">
                <?php foreach ($breaking as $b): ?>
                    <a href="<?= url('articles/view.php?id=' . (int) $b['id']) ?>"><?= e($b['title']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</header>

<?php foreach (get_flashes() as $flashMsg): ?>
    <div class="alert alert-<?= e($flashMsg['type']) ?>" role="alert">
        <?= e($flashMsg['text']) ?>
        <button type="button" class="alert-close" aria-label="Close">&times;</button>
    </div>
<?php endforeach; ?>
