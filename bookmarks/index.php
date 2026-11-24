<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$stmt = $pdo->prepare(
    "SELECT a.id, a.title, a.content, a.image_url, a.created_at, a.published_at,
            c.name AS category_name, b.created_at AS saved_at
     FROM bookmarks b
     JOIN articles a   ON a.id = b.article_id
     JOIN categories c ON c.id = a.category_id
     WHERE b.user_id = ? AND a.status = 'published'
     ORDER BY b.created_at DESC"
);
$stmt->execute([current_user_id()]);
$articles = $stmt->fetchAll();

$pageTitle = 'Saved articles | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="page-head">
        <h1>Saved articles</h1>
        <span class="hint"><?= count($articles) ?> saved</span>
    </div>

    <?php if (!$articles): ?>
        <div class="empty">You haven't saved any articles yet. Tap <strong>☆ Save</strong> on any article to read it later.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($articles as $article): ?>
                <div class="card-wrap">
                    <?php include __DIR__ . '/../includes/card.php'; ?>
                    <form method="post" action="<?= url('bookmarks/toggle.php') ?>" class="card-action">
                        <?= csrf_field() ?>
                        <input type="hidden" name="article_id" value="<?= (int) $article['id'] ?>">
                        <input type="hidden" name="back" value="list">
                        <small class="hint">Saved <?= timeAgo($article['saved_at']) ?></small>
                        <button type="submit" class="link-btn danger">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
