<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action      = $_POST['action'] ?? '';
    $id          = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($action === 'create' || $action === 'update') {
        if (!preg_match('/^[\p{L}0-9 &\/-]{2,50}$/u', $name)) {
            flash('error', 'Category name must be 2–50 letters/numbers.');
        } else {
            $dup = $pdo->prepare('SELECT id FROM categories WHERE name = ? AND id <> ?');
            $dup->execute([$name, $id]);
            if ($dup->fetch()) {
                flash('error', 'A category with that name already exists.');
            } elseif ($action === 'create') {
                $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)')->execute([$name, $description ?: null]);
                flash('success', 'Category "' . $name . '" created.');
            } else {
                $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?')->execute([$name, $description ?: null, $id]);
                flash('success', 'Category updated.');
            }
        }
    }

    if ($action === 'delete') {
        $used = $pdo->prepare('SELECT (SELECT COUNT(*) FROM articles WHERE category_id = ?) + (SELECT COUNT(*) FROM article_submissions WHERE category_id = ?)');
        $used->execute([$id, $id]);
        if ((int) $used->fetchColumn() > 0) {
            flash('error', 'This category still has articles or submissions. Move them to another category first.');
        } else {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            flash('success', 'Category deleted.');
        }
    }
    redirect('admin/categories.php');
}

$editId = (int) ($_GET['edit'] ?? 0);

$categories = $pdo->query(
    "SELECT c.*,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id) AS article_count,
            (SELECT COUNT(*) FROM subscriptions s WHERE s.category_id = c.id) AS subscriber_count
     FROM categories c ORDER BY c.name"
)->fetchAll();

$pageTitle = 'Categories';
$adminNav  = 'categories';
include __DIR__ . '/_header.php';
?>
<div class="admin-cols wide-left">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Name</th><th>Description</th><th>Articles</th><th>Subscribers</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <?php if ($editId === (int) $c['id']): ?>
                    <tr class="editing">
                        <td colspan="5">
                            <form method="post" class="form inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <input name="name" value="<?= e($c['name']) ?>" required maxlength="50">
                                <input name="description" value="<?= e($c['description']) ?>" maxlength="255" placeholder="Description">
                                <button class="btn btn-sm btn-primary">Save</button>
                                <a class="btn btn-sm" href="<?= url('admin/categories.php') ?>">Cancel</a>
                            </form>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td><strong><?= e($c['name']) ?></strong></td>
                        <td><?= e($c['description'] ?? '') ?></td>
                        <td><?= (int) $c['article_count'] ?></td>
                        <td><?= (int) $c['subscriber_count'] ?></td>
                        <td class="actions">
                            <a class="btn btn-sm" href="<?= url('admin/categories.php?edit=' . (int) $c['id']) ?>">Edit</a>
                            <form method="post" data-confirm="Delete category <?= e($c['name']) ?>?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button class="btn btn-sm btn-danger" <?= $c['article_count'] > 0 ? 'disabled title="Has articles"' : '' ?>>Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>Add category</h2>
        <form method="post" class="form" data-validate novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <label for="cname">Name *</label>
            <input id="cname" name="name" required minlength="2" maxlength="50">
            <label for="cdesc">Description</label>
            <input id="cdesc" name="description" maxlength="255">
            <button class="btn btn-primary btn-block">Add category</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
