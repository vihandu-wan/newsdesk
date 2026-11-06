<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$name = $_GET['name'] ?? '';
$allCategories = $pdo->query("SELECT name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

$articles = [];
if ($name !== '') {
    $stmt = $pdo->prepare("SELECT articles.id, articles.title, articles.content,
                                  articles.created_at, categories.name AS category_name
                           FROM articles
                           INNER JOIN categories ON articles.category_id = categories.id
                           WHERE articles.status = 'published' AND categories.name = ?
                           ORDER BY articles.created_at DESC");
    $stmt->execute([$name]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = ($name !== '' ? $name : 'Categories') . ' | NSBM NewsDesk';
$activeNav = $name;
include __DIR__ . '/includes/header.php';
?>

<main>
    <h1><?= $name !== '' ? htmlspecialchars($name) : 'Browse categories' ?></h1>

    <div class="row">
        <?php foreach ($allCategories as $c): ?>
            <a class="filter <?= $c === $name ? 'active' : '' ?>"
               href="categories.php?name=<?= urlencode($c) ?>"><?= htmlspecialchars($c) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if ($name === ''): ?>
        <p class="hint">Choose a category above.</p>
    <?php elseif (count($articles) === 0): ?>
        <p>No articles in this category yet.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($articles as $article): ?>
                <?php include __DIR__ . '/includes/card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>