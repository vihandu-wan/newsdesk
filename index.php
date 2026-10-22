
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSBM NewsDesk</title>
</head>
<body>

    <h1>NSBM NewsDesk</h1>
    <p>Your campus. Your news.</p>

    <hr>

    <h2>Latest News</h2>

    <?php if (count($articles) === 0): ?>
        <p>No published news articles yet.</p>
    <?php else: ?>

        <?php foreach ($articles as $article): ?>
            <article>
                <h3>
                    <?= htmlspecialchars(
                        $article['title'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </h3>

                <p>
                    Category:
                    <?= htmlspecialchars(
                        $article['category_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <p>
                    <?= nl2br(htmlspecialchars(
                        $article['content'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) ?>
                </p>

                <small>
                    Published:
                    <?= htmlspecialchars($article['created_at']) ?>
                </small>

                <hr>
            </article>
        <?php endforeach; ?>

    <?php endif; ?>

</body>
</html>
