<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$num = fn($sql) => (int) $pdo->query($sql)->fetchColumn();

$stats = [
    'published'   => $num("SELECT COUNT(*) FROM articles WHERE status = 'published'"),
    'drafts'      => $num("SELECT COUNT(*) FROM articles WHERE status = 'draft'"),
    'pending'     => $num("SELECT COUNT(*) FROM article_submissions WHERE status = 'pending'"),
    'students'    => $num("SELECT COUNT(*) FROM users WHERE role = 'student'"),
    'comments'    => $num("SELECT COUNT(*) FROM comments"),
    'views7'      => $num("SELECT COUNT(*) FROM article_views WHERE viewed_at >= NOW() - INTERVAL 7 DAY"),
];

$latestSubs = $pdo->query(
    "SELECT s.id, s.title, s.submitted_at, u.name AS author
     FROM article_submissions s JOIN users u ON u.id = s.author_id
     WHERE s.status = 'pending' ORDER BY s.submitted_at ASC LIMIT 5"
)->fetchAll();

$latestComments = $pdo->query(
    "SELECT c.id, c.content, c.status, c.commented_at, u.name, a.title, a.id AS article_id
     FROM comments c JOIN users u ON u.id = c.user_id JOIN articles a ON a.id = c.article_id
     ORDER BY c.commented_at DESC LIMIT 5"
)->fetchAll();

$pageTitle = 'Dashboard';
$adminNav  = 'dashboard';
include __DIR__ . '/_header.php';
?>
<div class="stats">
    <a class="stat green" href="<?= url('admin/articles.php?status=published') ?>"><b><?= $stats['published'] ?></b><span>Published articles</span></a>
    <a class="stat" href="<?= url('admin/articles.php?status=draft') ?>"><b><?= $stats['drafts'] ?></b><span>Drafts</span></a>
    <a class="stat warn" href="<?= url('admin/submissions.php') ?>"><b><?= $stats['pending'] ?></b><span>Submissions to review</span></a>
    <a class="stat" href="<?= url('admin/users.php') ?>"><b><?= $stats['students'] ?></b><span>Registered students</span></a>
    <a class="stat" href="<?= url('admin/comments.php?status=all') ?>"><b><?= $stats['comments'] ?></b><span>Comments</span></a>
    <a class="stat" href="<?= url('admin/reports.php') ?>"><b><?= $stats['views7'] ?></b><span>Views (last 7 days)</span></a>
</div>

<div class="quick-actions">
    <a class="btn btn-primary" href="<?= url('articles/create.php') ?>">+ New article</a>
    <a class="btn" href="<?= url('admin/announcements.php') ?>">+ New announcement</a>
    <a class="btn" href="<?= url('admin/categories.php') ?>">Manage categories</a>
    <a class="btn" href="<?= url('admin/reports.php') ?>">View reports</a>
</div>

<div class="admin-cols">
    <div class="panel">
        <h2>Waiting for review</h2>
        <?php if (!$latestSubs): ?>
            <p class="hint">No pending submissions. 🎉</p>
        <?php else: ?>
            <ul class="side-list plain">
                <?php foreach ($latestSubs as $s): ?>
                    <li><a href="<?= url('admin/review_submission.php?id=' . (int) $s['id']) ?>"><?= e($s['title']) ?></a>
                        <small>by <?= e($s['author']) ?> · <?= timeAgo($s['submitted_at']) ?></small></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="panel">
        <h2>Latest comments</h2>
        <?php if (!$latestComments): ?>
            <p class="hint">No comments yet.</p>
        <?php else: ?>
            <ul class="side-list plain">
                <?php foreach ($latestComments as $c): ?>
                    <li><?= e(summary($c['content'], 80)) ?> <?= status_badge($c['status']) ?>
                        <small><?= e($c['name']) ?> on <a href="<?= url('articles/view.php?id=' . (int) $c['article_id']) ?>#comments"><?= e($c['title']) ?></a></small></li>
                <?php endforeach; ?>
            </ul>
            <a href="<?= url('admin/comments.php?status=all') ?>">Moderate comments &rarr;</a>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
