<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$stmt = $pdo->prepare(
    'SELECT s.*, c.name AS category_name
     FROM article_submissions s JOIN categories c ON c.id = s.category_id
     WHERE s.author_id = ?
     ORDER BY s.submitted_at DESC'
);
$stmt->execute([current_user_id()]);
$rows = $stmt->fetchAll();

$pageTitle = 'My submissions | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="page-head">
        <h1>My submissions</h1>
        <a class="btn btn-primary" href="<?= url('submissions/create.php') ?>">+ Submit new article</a>
    </div>

    <?php if (!$rows): ?>
        <div class="empty">You haven't submitted any articles yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr><th>Title</th><th>Category</th><th>Submitted</th><th>Status</th><th>Reviewer note</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['title']) ?></td>
                        <td><?= e($row['category_name']) ?></td>
                        <td><?= e(date('M j, Y', strtotime($row['submitted_at']))) ?></td>
                        <td><?= status_badge($row['status']) ?></td>
                        <td><?= $row['review_note'] ? e($row['review_note']) : '<span class="hint">—</span>' ?></td>
                        <td class="actions">
                            <?php if ($row['status'] === 'approved' && $row['article_id']): ?>
                                <a class="btn btn-sm btn-success" href="<?= url('articles/view.php?id=' . (int) $row['article_id']) ?>">View article</a>
                            <?php else: ?>
                                <a class="btn btn-sm" href="<?= url('submissions/edit.php?id=' . (int) $row['id']) ?>">Edit</a>
                                <form method="post" action="<?= url('submissions/delete.php') ?>" data-confirm="Withdraw this submission?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
