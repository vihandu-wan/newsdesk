<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$uid = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: 0;
    $action     = $_POST['action'] ?? '';

    $exists = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
    $exists->execute([$categoryId]);
    $catName = $exists->fetchColumn();

    if (!$catName) {
        flash('error', 'Invalid category.');
    } elseif ($action === 'subscribe') {
        $pdo->prepare('INSERT IGNORE INTO subscriptions (user_id, category_id) VALUES (?, ?)')->execute([$uid, $categoryId]);
        flash('success', 'You are now following ' . $catName . '.');
    } elseif ($action === 'unsubscribe') {
        $pdo->prepare('DELETE FROM subscriptions WHERE user_id = ? AND category_id = ?')->execute([$uid, $categoryId]);
        flash('success', 'You unfollowed ' . $catName . '.');
    }

    if (!empty($_POST['back'])) {
        redirect('categories.php?name=' . urlencode($_POST['back']));
    }
    redirect('subscriptions/categories.php');
}

$stmt = $pdo->prepare(
    "SELECT c.id, c.name, c.description,
            (s.id IS NOT NULL) AS is_subscribed,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND a.status = 'published') AS article_count
     FROM categories c
     LEFT JOIN subscriptions s ON s.category_id = c.id AND s.user_id = ?
     ORDER BY c.name"
);
$stmt->execute([$uid]);
$categories = $stmt->fetchAll();

$pageTitle = 'Follow categories | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="page-head">
        <div>
            <h1>Follow categories</h1>
            <p class="hint">Articles from the categories you follow appear in your personal feed.</p>
        </div>
        <div class="actions">
            <a class="btn" href="<?= url('subscriptions/authors.php') ?>">Follow authors</a>
            <a class="btn btn-primary" href="<?= url('subscriptions/index.php') ?>">My feed &rarr;</a>
        </div>
    </div>

    <div class="sub-grid">
        <?php foreach ($categories as $cat): ?>
            <div class="sub-card <?= $cat['is_subscribed'] ? 'on' : '' ?>">
                <h3><?= e($cat['name']) ?></h3>
                <p class="hint"><?= e($cat['description'] ?: 'Campus news') ?></p>
                <small class="hint"><?= (int) $cat['article_count'] ?> articles</small>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="category_id" value="<?= (int) $cat['id'] ?>">
                    <?php if ($cat['is_subscribed']): ?>
                        <button class="btn btn-outline btn-block" name="action" value="unsubscribe">✓ Following</button>
                    <?php else: ?>
                        <button class="btn btn-success btn-block" name="action" value="subscribe">+ Follow</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
