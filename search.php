<?php
require_once __DIR__ . '/includes/init.php';

$q = trim($_GET['q'] ?? '');
$articles = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("SELECT articles.id, articles.title, articles.content,
                                  articles.created_at, categories.name AS category_name
                           FROM articles
                           INNER JOIN categories ON articles.category_id = categories.id
                           WHERE articles.status = 'published'
                             AND (articles.title LIKE ? OR articles.content LIKE ?)
                           ORDER BY articles.created_at DESC");
    $stmt->execute([$like, $like]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = 'Search | NSBM NewsDesk';
include __DIR__ . '/includes/header.php';
?>

<main>
    <h1>Search results</h1>

    <?php if ($q === ''): ?>
        <p class="hint">Type something in the search box to find articles.</p>
    <?php elseif (count($articles) === 0): ?>
        <p>No articles found for "<?= htmlspecialchars($q) ?>".</p>
    <?php else: ?>
        <p class="hint"><?= count($articles) ?> result(s) for "<?= htmlspecialchars($q) ?>"</p>
        <div class="grid">
            <?php foreach ($articles as $article): ?>
                <?php include __DIR__ . '/includes/card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
