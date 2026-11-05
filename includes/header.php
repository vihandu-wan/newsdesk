<?php
$pageTitle = $pageTitle ?? 'NSBM NewsDesk';
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/newsdesk/assets/style.css">
</head>
<body>
    <header>
        <div class="bar">
            <a href="/newsdesk/index.php"><img class="logo" src="/newsdesk/assets/logo.png" alt="NSBM"></a>
            <a class="brand" href="/newsdesk/index.php">NewsDesk</a>

            <nav>
                <a href="/newsdesk/index.php" class="<?= $activeNav === 'Home' ? 'on' : '' ?>">Home</a>
                <a href="/newsdesk/categories.php?name=Academic" class="<?= $activeNav === 'Academic' ? 'on' : '' ?>">Academic</a>
                <a href="/newsdesk/categories.php?name=Sports" class="<?= $activeNav === 'Sports' ? 'on' : '' ?>">Sports / Clubs</a>
                <a href="/newsdesk/categories.php?name=Events" class="<?= $activeNav === 'Events' ? 'on' : '' ?>">Events</a>
                <a href="/newsdesk/categories.php?name=General" class="<?= $activeNav === 'General' ? 'on' : '' ?>">General</a>
            </nav>

            <form class="search-form" action="/newsdesk/search.php" method="get">
                <input class="search" name="q" placeholder="Search news" value="<?= htmlspecialchars($q ?? '') ?>">
            </form>
            <a class="login" href="/newsdesk/auth/login.php">Log in</a>
        </div>
    </header>