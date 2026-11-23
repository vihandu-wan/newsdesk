<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$values = ['title' => '', 'content' => '', 'category_id' => 0, 'status' => 'draft',
           'image_url' => '', 'is_featured' => 0, 'is_breaking' => 0];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values['title']       = trim($_POST['title'] ?? '');
    $values['content']     = trim($_POST['content'] ?? '');
    $values['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $values['status']      = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
    $values['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $values['is_breaking'] = isset($_POST['is_breaking']) ? 1 : 0;

    if (mb_strlen($values['title']) < 5) {
        $errors['title'] = 'Title must be at least 5 characters.';
    }
    if (mb_strlen($values['content']) < 20) {
        $errors['content'] = 'Content must be at least 20 characters.';
    }
    $catOk = $pdo->prepare('SELECT 1 FROM categories WHERE id = ?');
    $catOk->execute([$values['category_id']]);
    if (!$catOk->fetchColumn()) {
        $errors['category_id'] = 'Please select a valid category.';
    }

    if (!$errors) {
        $values['image_url'] = upload_image('image', $uploadError);
        if ($uploadError) {
            $errors['image'] = $uploadError;
        }
    }

    if (!$errors) {
        $pdo->prepare(
            'INSERT INTO articles (title, content, category_id, author_id, image_url, status, is_featured, is_breaking, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $values['title'], $values['content'], $values['category_id'], current_user_id(),
            $values['image_url'], $values['status'], $values['is_featured'], $values['is_breaking'],
            $values['status'] === 'published' ? date('Y-m-d H:i:s') : null,
        ]);
        flash('success', 'Article created.');
        redirect('admin/articles.php');
    }
}

$submitLabel = 'Create article';
$pageTitle   = 'New article';
$adminNav    = 'articles';
include __DIR__ . '/../admin/_header.php';
?>
<a class="back" href="<?= url('admin/articles.php') ?>">&larr; All articles</a>
<?php include __DIR__ . '/_form.php'; ?>
<?php include __DIR__ . '/../admin/_footer.php'; ?>
