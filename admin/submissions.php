<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$tab = $_GET['status'] ?? 'pending';
if (!in_array($tab, ['pending', 'approved', 'rejected', 'all'], true)) {
    $tab = 'pending';
}

$sql = "SELECT s.id, s.title, s.status, s.submitted_at, s.reviewed_at, s.article_id,
               u.name AS author_name, u.student_id, c.name AS category_name, r.name AS reviewer_name
        FROM article_submissions s
        JOIN users u ON u.id = s.author_id
        JOIN categories c ON c.id = s.category_id
        LEFT JOIN users r ON r.id = s.reviewed_by";
$params = [];
if ($tab !== 'all') {
    $sql .= ' WHERE s.status = ?';
    $params[] = $tab;
}
$sql .= $tab === 'pending' ? ' ORDER BY s.submitted_at ASC' : ' ORDER BY s.submitted_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pageTitle = 'Article submissions';
$adminNav  = 'submissions';
include __DIR__ . '/_header.php';
?>
<div class="tabs">
    <?php foreach (['pending', 'approved', 'rejected', 'all'] as $t): ?>
        <a href="?status=<?= $t ?>" class="<?= $tab === $t ? 'active' : '' ?>"><?= ucfirst($t) ?></a>
    <?php endforeach; ?>
</div>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="6" class="hint">Nothing here.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['title']) ?></td>
                <td><?= e($r['author_name']) ?><br><small class="hint"><?= e($r['student_id']) ?></small></td>
                <td><?= e($r['category_name']) ?></td>
                <td><small><?= timeAgo($r['submitted_at']) ?></small></td>
                <td><?= status_badge($r['status']) ?>
                    <?php if ($r['reviewer_name']): ?><br><small class="hint">by <?= e($r['reviewer_name']) ?></small><?php endif; ?></td>
                <td class="actions">
                    <a class="btn btn-sm <?= $r['status'] === 'pending' ? 'btn-primary' : '' ?>"
                       href="<?= url('admin/review_submission.php?id=' . (int) $r['id']) ?>"><?= $r['status'] === 'pending' ? 'Review' : 'Open' ?></a>
                    <?php if ($r['article_id']): ?>
                        <a class="btn btn-sm" href="<?= url('articles/view.php?id=' . (int) $r['article_id']) ?>" target="_blank">Article</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
