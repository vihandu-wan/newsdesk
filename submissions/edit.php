<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$load = $pdo->prepare("SELECT * FROM article_submissions WHERE id = ? AND author_id = ? AND status IN ('pending','rejected')");
$load->execute([$id, current_user_id()]);
$submission = $load->fetch();

if (!$submission) {
    flash('error', 'You can only edit your own pending or rejected submissions.');
    redirect('submissions/my_submissions.php');
}

$values = $submission;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values['title']       = trim($_POST['title'] ?? '');
    $values['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $values['content']     = trim($_POST['content'] ?? '');

    if (mb_strlen($values['title']) < 10 || mb_strlen($values['title']) > 255) {
        $errors['title'] = 'Headline must be 10–255 characters.';
    }
    $catOk = $pdo->prepare('SELECT 1 FROM categories WHERE id = ?');
    $catOk->execute([$values['category_id']]);
    if (!$catOk->fetchColumn()) {
        $errors['category_id'] = 'Please choose a category.';
    }
    if (mb_strlen($values['content']) < 50) {
        $errors['content'] = 'Article must be at least 50 characters.';
    }

    if (!$errors) {
        $newImage = upload_image('image', $uploadError);
        if ($uploadError) {
            $errors['image'] = $uploadError;
        } elseif ($newImage) {
            delete_upload($submission['image_url']);
            $values['image_url'] = $newImage;
        }
    }

    if (!$errors) {

        $pdo->prepare(
            "UPDATE article_submissions
             SET title = ?, content = ?, category_id = ?, image_url = ?, status = 'pending',
                 review_note = NULL, reviewed_by = NULL, reviewed_at = NULL
             WHERE id = ? AND author_id = ?"
        )->execute([$values['title'], $values['content'], $values['category_id'], $values['image_url'], $id, current_user_id()]);

        flash('success', 'Submission updated and sent for review.');
        redirect('submissions/my_submissions.php');
    }
}

$submitLabel = 'Save & resubmit';
$pageTitle   = 'Edit submission | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main class="narrow">
    <a class="back" href="<?= url('submissions/my_submissions.php') ?>">&larr; My submissions</a>
    <div class="panel">
        <h1>Edit submission</h1>
        <?php if ($submission['status'] === 'rejected' && $submission['review_note']): ?>
            <div class="alert alert-info inline"><strong>Reviewer note:</strong> <?= e($submission['review_note']) ?></div>
        <?php endif; ?>
        <?php include __DIR__ . '/_form.php'; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
