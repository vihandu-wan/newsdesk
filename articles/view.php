<?php
require_once __DIR__ . '/../includes/init.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('index.php');
}

$stmt = $pdo->prepare(
    "SELECT a.*, c.name AS category_name, u.name AS author_name
     FROM articles a
     JOIN categories c ON c.id = a.category_id
     LEFT JOIN users u ON u.id = a.author_id
     WHERE a.id = ?"
);
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article || ($article['status'] !== 'published' && !is_admin())) {
    http_response_code(404);
    flash('error', 'Article not found.');
    redirect('index.php');
}

if (empty($_SESSION['viewed'][$id]) && $article['status'] === 'published') {
    $pdo->prepare('INSERT INTO article_views (article_id, user_id) VALUES (?, ?)')
        ->execute([$id, is_logged_in() ? current_user_id() : null]);
    $_SESSION['viewed'][$id] = true;
}

$views = $pdo->prepare('SELECT COUNT(*) FROM article_views WHERE article_id = ?');
$views->execute([$id]);
$viewCount = (int) $views->fetchColumn();

$isSaved = false;
if (is_logged_in()) {
    $b = $pdo->prepare('SELECT 1 FROM bookmarks WHERE user_id = ? AND article_id = ?');
    $b->execute([current_user_id(), $id]);
    $isSaved = (bool) $b->fetchColumn();
}

$c = $pdo->prepare(
    "SELECT cm.id, cm.content, cm.status, cm.commented_at, cm.user_id, u.name
     FROM comments cm JOIN users u ON u.id = cm.user_id
     WHERE cm.article_id = ? AND (cm.status = 'approved' OR cm.user_id = ?)
     ORDER BY cm.commented_at ASC"
);
$c->execute([$id, current_user_id()]);
$comments = $c->fetchAll();

$r = $pdo->prepare(
    "SELECT a.id, a.title, a.content, a.image_url, a.created_at, a.published_at, c.name AS category_name
     FROM articles a JOIN categories c ON c.id = a.category_id
     WHERE a.status = 'published' AND a.category_id = ? AND a.id <> ?
     ORDER BY a.published_at DESC LIMIT 3"
);
$r->execute([$article['category_id'], $id]);
$related = $r->fetchAll();

$pageTitle = $article['title'] . ' | NSBM NewsDesk';
$activeNav = $article['category_name'];
include __DIR__ . '/../includes/header.php';
?>
<main>
    <article class="article">
        <a class="back" href="<?= url('categories.php?name=' . urlencode($article['category_name'])) ?>">&larr; <?= e($article['category_name']) ?></a>

        <?php if ($article['status'] !== 'published'): ?>
            <div class="alert alert-info inline">Admin preview – this article is <strong><?= e($article['status']) ?></strong>.</div>
        <?php endif; ?>

        <h1><?= e($article['title']) ?></h1>
        <div class="meta">
            <span class="tag"><?= e($article['category_name']) ?></span>
            <span>By <?= e($article['author_name'] ?? 'NewsDesk') ?></span>
            <span>· <?= e(date('M j, Y g:i A', strtotime($article['published_at'] ?? $article['created_at']))) ?></span>
            <span>· <?= $viewCount ?> views</span>
        </div>

        <div class="article-tools">
            <?php if (is_logged_in()): ?>
                <form class="bookmark-form" method="post" action="<?= url('bookmarks/toggle.php') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="article_id" value="<?= (int) $id ?>">
                    <button type="submit" class="btn btn-outline btn-sm <?= $isSaved ? 'saved' : '' ?>">
                        <?= $isSaved ? '★ Saved' : '☆ Save' ?>
                    </button>
                </form>
            <?php else: ?>
                <a class="btn btn-outline btn-sm" href="<?= url('auth/login.php') ?>">☆ Log in to save</a>
            <?php endif; ?>
            <button type="button" class="btn btn-sm" data-share>Share link</button>
            <?php if (is_admin()): ?>
                <a class="btn btn-sm" href="<?= url('articles/edit.php?id=' . (int) $id) ?>">Edit</a>
            <?php endif; ?>
        </div>

        <?php if ($article['image_url']): ?>
            <img class="photo" src="<?= e(imageSrc($article['image_url'])) ?>" alt="<?= e($article['title']) ?>">
        <?php endif; ?>

        <div class="article-body"><?= nl2br(e($article['content'])) ?></div>

        <!-- ================= comments ================= -->
        <section class="comments" id="comments">
            <h2>Comments (<?= count(array_filter($comments, fn($x) => $x['status'] === 'approved')) ?>)</h2>

            <?php if (is_logged_in()): ?>
                <form method="post" action="<?= url('comments/add.php') ?>" class="form" data-validate novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="article_id" value="<?= (int) $id ?>">
                    <label for="comment">Add a comment</label>
                    <textarea id="comment" name="content" rows="3" maxlength="1000" required data-counter
                              placeholder="Share your thoughts (be respectful)"></textarea>
                    <button type="submit" class="btn btn-primary">Post comment</button>
                </form>
            <?php else: ?>
                <p class="hint"><a href="<?= url('auth/login.php') ?>">Log in</a> to join the discussion.</p>
            <?php endif; ?>

            <?php if (!$comments): ?>
                <p class="hint">No comments yet. Be the first!</p>
            <?php endif; ?>

            <?php foreach ($comments as $cm): ?>
                <div class="comment">
                    <div class="comment-head">
                        <span class="avatar"><?= e(mb_strtoupper(mb_substr($cm['name'], 0, 1))) ?></span>
                        <strong><?= e($cm['name']) ?></strong>
                        <small><?= timeAgo($cm['commented_at']) ?></small>
                        <?php if ($cm['status'] !== 'approved'): ?><?= status_badge($cm['status']) ?><?php endif; ?>
                    </div>
                    <p><?= nl2br(e($cm['content'])) ?></p>
                    <?php if (is_logged_in() && ((int) $cm['user_id'] === current_user_id() || is_admin())): ?>
                        <form method="post" action="<?= url('comments/delete.php') ?>" data-confirm="Delete this comment?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="comment_id" value="<?= (int) $cm['id'] ?>">
                            <button type="submit" class="link-btn">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    </article>

    <?php if ($related): ?>
        <section class="related">
            <h2>More in <?= e($article['category_name']) ?></h2>
            <div class="grid">
                <?php foreach ($related as $article): ?>
                    <?php include __DIR__ . '/../includes/card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
