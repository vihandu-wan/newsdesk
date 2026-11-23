<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$load = $pdo->prepare(
    'SELECT s.*, u.name AS author_name, u.email AS author_email, c.name AS category_name
     FROM article_submissions s
     JOIN users u ON u.id = s.author_id
     JOIN categories c ON c.id = s.category_id
     WHERE s.id = ?'
);
$load->execute([$id]);
$sub = $load->fetch();

if (!$sub) {
    flash('error', 'Submission not found.');
    redirect('admin/submissions.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sub['status'] === 'pending') {
    verify_csrf();
    $action  = $_POST['action'] ?? '';
    $note    = trim($_POST['review_note'] ?? '');
    $adminId = current_user_id();

    if ($action === 'approve') {

        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                "INSERT INTO articles (title, content, category_id, author_id, image_url, status, published_at)
                 VALUES (?, ?, ?, ?, ?, 'published', NOW())"
            )->execute([$sub['title'], $sub['content'], $sub['category_id'], $sub['author_id'], $sub['image_url']]);
            $articleId = (int) $pdo->lastInsertId();

            $pdo->prepare(
                "UPDATE article_submissions
                 SET status = 'approved', reviewed_by = ?, review_note = ?, reviewed_at = NOW(), article_id = ?
                 WHERE id = ?"
            )->execute([$adminId, $note ?: null, $articleId, $id]);

            $pdo->commit();
            flash('success', 'Submission approved and published.');
            redirect('admin/submissions.php');
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $error = 'Could not publish the submission. Please try again.';
        }
    } elseif ($action === 'reject') {
        if (mb_strlen($note) < 5) {
            $error = 'Please write a short note telling the student why it was rejected.';
        } else {
            $pdo->prepare(
                "UPDATE article_submissions
                 SET status = 'rejected', reviewed_by = ?, review_note = ?, reviewed_at = NOW()
                 WHERE id = ?"
            )->execute([$adminId, $note, $id]);
            flash('success', 'Submission rejected. The student can see your note.');
            redirect('admin/submissions.php');
        }
    }
}

$pageTitle = 'Review submission';
$adminNav  = 'submissions';
include __DIR__ . '/_header.php';
?>
<a class="back" href="<?= url('admin/submissions.php') ?>">&larr; All submissions</a>

<?php if ($error): ?><div class="alert alert-error inline"><?= e($error) ?></div><?php endif; ?>

<div class="admin-cols wide-left">
    <article class="panel">
        <div class="meta">
            <span class="tag"><?= e($sub['category_name']) ?></span>
            <span>by <strong><?= e($sub['author_name']) ?></strong> (<?= e($sub['author_email']) ?>)</span>
            <span>· <?= e(date('M j, Y g:i A', strtotime($sub['submitted_at']))) ?></span>
            <?= status_badge($sub['status']) ?>
        </div>
        <h2 style="margin-top:10px"><?= e($sub['title']) ?></h2>
        <?php if ($sub['image_url']): ?>
            <img class="photo" src="<?= e(imageSrc($sub['image_url'])) ?>" alt="">
        <?php endif; ?>
        <div class="article-body"><?= nl2br(e($sub['content'])) ?></div>
    </article>

    <div class="panel">
        <?php if ($sub['status'] === 'pending'): ?>
            <h2>Decision</h2>
            <form method="post" class="form">
                <?= csrf_field() ?>
                <label for="review_note">Note to the author</label>
                <textarea id="review_note" name="review_note" rows="5" maxlength="1000" data-counter
                          placeholder="Required when rejecting"><?= old('review_note') ?></textarea>
                <div class="actions" style="margin-top:12px">
                    <button class="btn btn-success" name="action" value="approve">✓ Approve &amp; publish</button>
                    <button class="btn btn-danger" name="action" value="reject">✗ Reject</button>
                </div>
            </form>
        <?php else: ?>
            <h2>Already <?= e($sub['status']) ?></h2>
            <p class="hint">Reviewed <?= $sub['reviewed_at'] ? timeAgo($sub['reviewed_at']) : '' ?>.</p>
            <?php if ($sub['review_note']): ?><p><strong>Note:</strong> <?= e($sub['review_note']) ?></p><?php endif; ?>
            <?php if ($sub['article_id']): ?>
                <a class="btn" href="<?= url('articles/edit.php?id=' . (int) $sub['article_id']) ?>">Edit published article</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
