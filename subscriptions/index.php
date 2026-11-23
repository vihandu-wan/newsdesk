<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$uid = current_user_id();

$stmt = $pdo->prepare(
    "SELECT DISTINCT a.id, a.title, a.content, a.image_url, a.created_at, a.published_at, c.name AS category_name
     FROM articles a
     JOIN categories c ON c.id = a.category_id
     WHERE a.status = 'published'
       AND (a.category_id IN (SELECT category_id FROM subscriptions WHERE user_id = ?)
            OR a.author_id IN (SELECT author_id FROM author_subscriptions WHERE student_id = ?))
     ORDER BY a.published_at DESC
     LIMIT 60"
);
$stmt->execute([$uid, $uid]);
$articles = $stmt->fetchAll();

$s = $pdo->prepare('SELECT c.name FROM subscriptions s JOIN categories c ON c.id = s.category_id WHERE s.user_id = ? ORDER BY c.name');
$s->execute([$uid]);
$followedCats = $s->fetchAll(PDO::FETCH_COLUMN);

$a = $pdo->prepare('SELECT u.name FROM author_subscriptions s JOIN users u ON u.id = s.author_id WHERE s.student_id = ? ORDER BY u.name');
$a->execute([$uid]);
$followedAuthors = $a->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'My feed | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="page-head">
        <h1>My news feed</h1>
        <div class="actions">
            <a class="btn" href="<?= url('subscriptions/categories.php') ?>">Manage categories</a>
            <a class="btn" href="<?= url('subscriptions/authors.php') ?>">Manage authors</a>
        </div>
    </div>

    <div class="row">
        <span class="hint">Following:</span>
        <?php foreach ($followedCats as $fc): ?><span class="tag"><?= e($fc) ?></span><?php endforeach; ?>
        <?php foreach ($followedAuthors as $fa): ?><span class="tag author"><?= e($fa) ?></span><?php endforeach; ?>
        <?php if (!$followedCats && !$followedAuthors): ?><span class="hint">nothing yet</span><?php endif; ?>
    </div>

    <?php if (!$articles): ?>
        <div class="empty">Your feed is empty. Follow a few categories or authors to get started.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($articles as $article): ?>
                <?php include __DIR__ . '/../includes/card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
