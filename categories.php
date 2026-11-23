<?php
require_once __DIR__ . '/includes/init.php';

$name = $_GET['name'] ?? '';

$cat = null;
if ($name !== '') {
    $s = $pdo->prepare('SELECT * FROM categories WHERE name = ?');
    $s->execute([$name]);
    $cat = $s->fetch();
}

$articles = [];
$isSubscribed = false;
if ($cat) {
    $stmt = $pdo->prepare(
        "SELECT a.id, a.title, a.content, a.image_url, a.created_at, a.published_at, c.name AS category_name
         FROM articles a JOIN categories c ON c.id = a.category_id
         WHERE a.status = 'published' AND a.category_id = ?
         ORDER BY a.published_at DESC"
    );
    $stmt->execute([$cat['id']]);
    $articles = $stmt->fetchAll();

    if (is_logged_in()) {
        $s = $pdo->prepare('SELECT 1 FROM subscriptions WHERE user_id = ? AND category_id = ?');
        $s->execute([current_user_id(), $cat['id']]);
        $isSubscribed = (bool) $s->fetchColumn();
    }
}

$pageTitle = ($cat ? $cat['name'] : 'Categories') . ' | NSBM NewsDesk';
$activeNav = $cat['name'] ?? '';
include __DIR__ . '/includes/header.php';
?>
<main>
    <div class="page-head">
        <div>
            <h1><?= $cat ? e($cat['name']) : 'Browse categories' ?></h1>
            <?php if (!empty($cat['description'])): ?><p class="hint"><?= e($cat['description']) ?></p><?php endif; ?>
        </div>
        <?php if ($cat && is_logged_in()): ?>
            <form method="post" action="<?= url('subscriptions/categories.php') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="category_id" value="<?= (int) $cat['id'] ?>">
                <input type="hidden" name="back" value="<?= e($cat['name']) ?>">
                <?php if ($isSubscribed): ?>
                    <button class="btn btn-outline" name="action" value="unsubscribe">✓ Following</button>
                <?php else: ?>
                    <button class="btn btn-success" name="action" value="subscribe">+ Follow <?= e($cat['name']) ?></button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php foreach (get_categories($pdo) as $c): ?>
            <a class="filter <?= $c['name'] === $name ? 'active' : '' ?>"
               href="<?= url('categories.php?name=' . urlencode($c['name'])) ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$cat): ?>
        <p class="hint">Choose a category above.</p>
    <?php elseif (!$articles): ?>
        <div class="empty">No articles in this category yet.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($articles as $article): ?>
                <?php include __DIR__ . '/includes/card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
