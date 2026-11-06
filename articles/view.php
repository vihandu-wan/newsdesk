<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT articles.id, articles.title, articles.content, articles.image_url,
                              articles.created_at, categories.name AS category_name,
                              users.name AS author_name
                       FROM articles
                       INNER JOIN categories ON articles.category_id = categories.id
                       LEFT JOIN users ON articles.author_id = users.id
                       WHERE articles.id = ? AND articles.status = 'published'");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Article not found | NSBM NewsDesk';
    include __DIR__ . '/../includes/header.php';
    echo '<main><p>Sorry, that article could not be found.</p>'
       . '<a href="/newsdesk/index.php">Back to home</a></main>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle = $article['title'] . ' | NSBM NewsDesk';
$activeNav = $article['category_name'];
include __DIR__ . '/../includes/header.php';
?>

<main>
    <div class="article">
        <a class="back" href="/newsdesk/index.php">&larr; Back to news</a>

        <div class="meta">
            <a class="tag" href="/newsdesk/categories.php?name=<?= urlencode($article['category_name']) ?>">
                <?= htmlspecialchars($article['category_name']) ?>
            </a>
            <span><?= date('j F Y', strtotime($article['created_at'])) ?></span>
            <?php if (!empty($article['author_name'])): ?>
                <span>· By <?= htmlspecialchars($article['author_name']) ?></span>
            <?php endif; ?>
        </div>

        <h1><?= htmlspecialchars($article['title']) ?></h1>

        <?php if (!empty($article['image_url'])): ?>
            <img class="photo" src="<?= htmlspecialchars(imageSrc($article['image_url'])) ?>" alt="">
        <?php endif; ?>

        <div class="article-body">
            <?= nl2br(htmlspecialchars($article['content'])) ?>
        </div>

        <!-- Member 7: record the article view here -->
        <!-- Member 5: comment form and comment list go here -->
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>