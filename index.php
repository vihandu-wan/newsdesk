<?php
require_once __DIR__ . '/config/db.php';

$sql = "SELECT
            articles.id,
            articles.title,
            articles.content,
            articles.created_at,
            categories.name AS category_name
        FROM articles
        INNER JOIN categories
            ON articles.category_id = categories.id
        WHERE articles.status = 'published'
        ORDER BY articles.created_at DESC";

$stmt = $pdo->query($sql);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Home | NSBM NewsDesk</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <header>
            <div class="bar">
                <a href="index.php"><img class="logo" src="assets/logo.png" alt="NSBM"></a>
                <a class="brand" href="index.php">NewsDesk</a>

                <nav>
                    <a href="index.php" class="on">Home</a>
                    <a href="categories.php?name=Academic">Academic</a>
                    <a href="categories.php?name=Sports">Sports / Clubs</a>
                    <a href="categories.php?name=Events">Events</a>
                    <a href="categories.php?name=General">General</a>
                </nav>

                <form class="search-form" action="search.php" method="get">
                    <input class="search" name="q" placeholder="Search news">
                </form>
               <a class="login" href="auth/login.php">Log in</a>
            </div>
        </header>

        <main>
            
            <?php if (count($articles) === 0): ?>
                <p>No published news articles yet.</p>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <article>
                        <h3><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                        <p>Category: <?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?></p>

                        <p><?= nl2br(htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8')) ?></p>

                        <small>Published: <?= htmlspecialchars($article['created_at']) ?></small>

                        <hr>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </body>
</html>