<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$allowed = ['pending', 'approved', 'rejected', 'hidden'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    $newStatus = $_POST['new_status'] ?? '';
    if ($commentId && in_array($newStatus, $allowed, true)) {
        $pdo->prepare('UPDATE comments SET status = ? WHERE id = ?')->execute([$newStatus, $commentId]);
        flash('success', 'Comment marked as ' . $newStatus . '.');
    }
    redirect('admin/comments.php?status=' . urlencode($_GET['status'] ?? 'pending'));
}

$filter = $_GET['status'] ?? 'pending';
if ($filter !== 'all' && !in_array($filter, $allowed, true)) {
    $filter = 'pending';
}
$q = trim($_GET['q'] ?? '');

$sql = "SELECT c.id, c.content, c.status, c.commented_at,
               u.name AS user_name, u.email AS user_email,
               a.id AS article_id, a.title AS article_title
        FROM comments c
        JOIN users u    ON u.id = c.user_id
        JOIN articles a ON a.id = c.article_id
        WHERE 1 = 1";
$params = [];
if ($filter !== 'all') {
    $sql .= ' AND c.status = ?';
    $params[] = $filter;
}
if ($q !== '') {
    $sql .= ' AND (c.content LIKE ? OR u.name LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$sql .= ' ORDER BY c.commented_at DESC LIMIT 100';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comments = $stmt->fetchAll();

$pageTitle = 'Comment moderation';
$adminNav  = 'comments';
include __DIR__ . '/_header.php';
?>
<div class="page-head">
    <div class="tabs">
        <?php foreach (array_merge($allowed, ['all']) as $tab): ?>
            <a href="?status=<?= e($tab) ?>" class="<?= $tab === $filter ? 'active' : '' ?>"><?= e(ucfirst($tab)) ?></a>
        <?php endforeach; ?>
    </div>
    <form method="get" class="row">
        <input type="hidden" name="status" value="<?= e($filter) ?>">
        <input name="q" value="<?= e($q) ?>" class="filter-select" placeholder="Search comments or users">
        <button class="btn btn-sm">Search</button>
    </form>
</div>

<?php if (!$comments): ?>
    <div class="empty">No comments in this list.</div>
<?php endif; ?>

<?php foreach ($comments as $c): ?>
    <div class="panel comment-card">
        <div class="meta">
            <strong><?= e($c['user_name']) ?></strong> (<?= e($c['user_email']) ?>)
            on <a href="<?= url('articles/view.php?id=' . (int) $c['article_id']) ?>#comments" target="_blank"><?= e($c['article_title']) ?></a>
            · <?= e(date('M j, Y g:i A', strtotime($c['commented_at']))) ?>
            <?= status_badge($c['status']) ?>
        </div>
        <p class="comment-text"><?= nl2br(e($c['content'])) ?></p>
        <div class="actions">
            <?php foreach (['approved' => ['Approve', 'btn-success'], 'rejected' => ['Reject', 'btn-danger'], 'hidden' => ['Hide', '']] as $value => [$label, $cls]): ?>
                <?php if ($c['status'] !== $value): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                        <input type="hidden" name="new_status" value="<?= $value ?>">
                        <button class="btn btn-sm <?= $cls ?>"><?= $label ?></button>
                    </form>
                <?php endif; ?>
            <?php endforeach; ?>
            <form method="post" action="<?= url('comments/delete.php') ?>" data-confirm="Delete this comment permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                <input type="hidden" name="from" value="admin">
                <button class="btn btn-sm">Delete</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
<?php include __DIR__ . '/_footer.php'; ?>
