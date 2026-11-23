<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$values = ['title' => '', 'category_id' => 0, 'content' => '', 'image_url' => ''];
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
        $image = upload_image('image', $uploadError);
        if ($uploadError) {
            $errors['image'] = $uploadError;
        }
    }

    if (!$errors) {
        $pdo->prepare(
            "INSERT INTO article_submissions (title, content, category_id, author_id, image_url, status)
             VALUES (?, ?, ?, ?, ?, 'pending')"
        )->execute([$values['title'], $values['content'], $values['category_id'], current_user_id(), $image]);

        flash('success', 'Thanks! Your article was sent to the NewsDesk team for review.');
        redirect('submissions/my_submissions.php');
    }
}

$submitLabel = 'Submit for review';
$pageTitle   = 'Submit an article | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main class="narrow">
    <a class="back" href="<?= url('submissions/my_submissions.php') ?>">&larr; My submissions</a>
    <div class="panel">
        <h1>Submit an article</h1>
        <p class="hint">Clubs, societies and students can share news here. An admin will review it before it is published.</p>
        <?php include __DIR__ . '/_form.php'; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
