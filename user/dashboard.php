<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$uid = current_user_id();

$s = $pdo->prepare('SELECT status, COUNT(*) AS n FROM article_submissions WHERE author_id = ? GROUP BY status');
$s->execute([$uid]);
$subCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
foreach ($s->fetchAll() as $row) {
    $subCounts[$row['status']] = (int) $row['n'];
}

$count = function ($sql) use ($pdo, $uid) {
    $st = $pdo->prepare($sql);
    $st->execute([$uid]);
    return (int) $st->fetchColumn();
};
$bookmarkCount = $count('SELECT COUNT(*) FROM bookmarks WHERE user_id = ?');
$subsCount     = $count('SELECT COUNT(*) FROM subscriptions WHERE user_id = ?');
$commentCount  = $count('SELECT COUNT(*) FROM comments WHERE user_id = ?');

$r = $pdo->prepare(
    'SELECT s.id, s.title, s.status, s.submitted_at, s.article_id, c.name AS category_name
     FROM article_submissions s JOIN categories c ON c.id = s.category_id
     WHERE s.author_id = ? ORDER BY s.submitted_at DESC LIMIT 5'
);
$r->execute([$uid]);
$recentSubs = $r->fetchAll();

$f = $pdo->prepare(
    "SELECT a.id, a.title, a.content, a.image_url, a.created_at, a.published_at, c.name AS category_name
     FROM articles a
     JOIN categories c ON c.id = a.category_id
     JOIN subscriptions sb ON sb.category_id = a.category_id AND sb.user_id = ?
     WHERE a.status = 'published'
     ORDER BY a.published_at DESC LIMIT 6"
);
$f->execute([$uid]);
$feed = $f->fetchAll();

$pageTitle = 'My dashboard | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="page-head">
        <div>
            <h1>Hi, <?= e($_SESSION['name']) ?> 👋</h1>
            <p class="hint">Here's what's happening with your NewsDesk account.</p>
        </div>
        <a class="btn btn-primary" href="<?= url('submissions/create.php') ?>">+ Submit an article</a>
    </div>

    <div class="stats">
        <a class="stat" href="<?= url('submissions/my_submissions.php') ?>"><b><?= $subCounts['pending'] ?></b><span>Pending submissions</span></a>
        <a class="stat green" href="<?= url('submissions/my_submissions.php') ?>"><b><?= $subCounts['approved'] ?></b><span>Published by me</span></a>
        <a class="stat" href="<?= url('bookmarks/index.php') ?>"><b><?= $bookmarkCount ?></b><span>Saved articles</span></a>
        <a class="stat" href="<?= url('subscriptions/categories.php') ?>"><b><?= $subsCount ?></b><span>Categories followed</span></a>
        <div class="stat"><b><?= $commentCount ?></b><span>Comments written</span></div>
    </div>

    <div class="home">
        <div>
            <div class="page-head"><h2>From your subscriptions</h2>
                <a href="<?= url('subscriptions/index.php') ?>">See full feed &rarr;</a></div>
            <?php if (!$feed): ?>
                <div class="empty">Follow some categories to build your personal feed.
                    <br><br><a class="btn btn-success" href="<?= url('subscriptions/categories.php') ?>">Choose categories</a></div>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($feed as $article): ?>
                        <?php include __DIR__ . '/../includes/card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside>
            <div class="side">
                <h3>My latest submissions</h3>
                <?php if (!$recentSubs): ?>
                    <p class="hint">You haven't submitted anything yet.</p>
                <?php else: ?>
                    <ul class="side-list plain">
                        <?php foreach ($recentSubs as $sub): ?>
                            <li>
                                <?= e($sub['title']) ?> <?= status_badge($sub['status']) ?>
                                <small><?= e($sub['category_name']) ?> · <?= timeAgo($sub['submitted_at']) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= url('submissions/my_submissions.php') ?>">Manage submissions &rarr;</a>
                <?php endif; ?>
            </div>
            <div class="side">
                <h3>Quick links</h3>
                <ul class="side-list plain">
                    <li><a href="<?= url('user/profile.php') ?>">Edit my profile</a></li>
                    <li><a href="<?= url('subscriptions/authors.php') ?>">Follow authors</a></li>
                    <li><a href="<?= url('announcements/index.php') ?>">Announcements</a></li>
                </ul>
            </div>
        </aside>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
