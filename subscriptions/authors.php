<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$uid = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $authorId = filter_input(INPUT_POST, 'author_id', FILTER_VALIDATE_INT) ?: 0;
    $action   = $_POST['action'] ?? '';

    $check = $pdo->prepare(
        "SELECT name FROM users u
         WHERE u.id = ? AND u.id <> ?
           AND EXISTS (SELECT 1 FROM articles a WHERE a.author_id = u.id AND a.status = 'published')"
    );
    $check->execute([$authorId, $uid]);
    $authorName = $check->fetchColumn();

    if (!$authorName) {
        flash('error', 'Author not found.');
    } elseif ($action === 'follow') {
        $pdo->prepare('INSERT IGNORE INTO author_subscriptions (student_id, author_id) VALUES (?, ?)')->execute([$uid, $authorId]);
        flash('success', 'You are now following ' . $authorName . '.');
    } elseif ($action === 'unfollow') {
        $pdo->prepare('DELETE FROM author_subscriptions WHERE student_id = ? AND author_id = ?')->execute([$uid, $authorId]);
        flash('success', 'You unfollowed ' . $authorName . '.');
    }
    redirect('subscriptions/authors.php');
}

$stmt = $pdo->prepare(
    "SELECT u.id, u.name, u.faculty,
            (s.id IS NOT NULL) AS is_following,
            (SELECT COUNT(*) FROM articles a WHERE a.author_id = u.id AND a.status = 'published') AS article_count
     FROM users u
     LEFT JOIN author_subscriptions s ON s.author_id = u.id AND s.student_id = ?
     WHERE u.id <> ?
       AND EXISTS (SELECT 1 FROM articles a WHERE a.author_id = u.id AND a.status = 'published')
     ORDER BY u.name"
);
$stmt->execute([$uid, $uid]);
$authors = $stmt->fetchAll();

$pageTitle = 'Follow authors | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="page-head">
        <div>
            <h1>Follow authors</h1>
            <p class="hint">Get articles from your favourite writers in your feed.</p>
        </div>
        <a class="btn" href="<?= url('subscriptions/categories.php') ?>">Follow categories</a>
    </div>

    <?php if (!$authors): ?>
        <div class="empty">No authors to follow yet.</div>
    <?php else: ?>
        <div class="sub-grid">
            <?php foreach ($authors as $author): ?>
                <div class="sub-card <?= $author['is_following'] ? 'on' : '' ?>">
                    <span class="avatar big"><?= e(mb_strtoupper(mb_substr($author['name'], 0, 1))) ?></span>
                    <h3><?= e($author['name']) ?></h3>
                    <small class="hint"><?= e($author['faculty'] ?? '') ?> · <?= (int) $author['article_count'] ?> articles</small>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="author_id" value="<?= (int) $author['id'] ?>">
                        <?php if ($author['is_following']): ?>
                            <button class="btn btn-outline btn-block" name="action" value="unfollow">✓ Following</button>
                        <?php else: ?>
                            <button class="btn btn-success btn-block" name="action" value="follow">+ Follow</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
