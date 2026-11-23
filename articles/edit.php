<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$load = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
$load->execute([$id]);
$article = $load->fetch();

if (!$article) {
    flash('error', 'Article not found.');
    redirect('admin/articles.php');
}

$values = $article;
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
        $newImage = upload_image('image', $uploadError);
        if ($uploadError) {
            $errors['image'] = $uploadError;
        } elseif ($newImage) {
            delete_upload($article['image_url']);
            $values['image_url'] = $newImage;
        } elseif (!empty($_POST['remove_image'])) {
            delete_upload($article['image_url']);
            $values['image_url'] = null;
        }
    }

    if (!$errors) {
        $publishedAt = $article['published_at'];
        if ($values['status'] === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }
        $pdo->prepare(
            'UPDATE articles
             SET title = ?, content = ?, category_id = ?, image_url = ?, status = ?,
                 is_featured = ?, is_breaking = ?, published_at = ?
             WHERE id = ?'
        )->execute([
            $values['title'], $values['content'], $values['category_id'], $values['image_url'],
            $values['status'], $values['is_featured'], $values['is_breaking'], $publishedAt, $id,
        ]);
        flash('success', 'Article updated.');
        redirect('admin/articles.php');
    }
}

$submitLabel = 'Save changes';
$pageTitle   = 'Edit article';
$adminNav    = 'articles';
include __DIR__ . '/../admin/_header.php';
?>
<a class="back" href="<?= url('admin/articles.php') ?>">&larr; All articles</a>
<?php include __DIR__ . '/_form.php'; ?>
<?php include __DIR__ . '/../admin/_footer.php'; ?>
