<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$editId = (int) ($_GET['edit'] ?? 0);
$values = ['title' => '', 'content' => '', 'status' => 'published', 'is_pinned' => 0, 'media_url' => ''];
$errors = [];

if ($editId) {
    $load = $pdo->prepare('SELECT * FROM announcements WHERE id = ?');
    $load->execute([$editId]);
    $existing = $load->fetch();
    if (!$existing) {
        flash('error', 'Announcement not found.');
        redirect('admin/announcements.php');
    }
    $values = $existing;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'save';
    $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    if ($action === 'delete') {
        $row = $pdo->prepare('SELECT media_url FROM announcements WHERE id = ?');
        $row->execute([$id]);
        delete_upload($row->fetchColumn() ?: null);
        $pdo->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
        flash('success', 'Announcement deleted.');
        redirect('admin/announcements.php');
    }
    if ($action === 'pin') {
        $pdo->prepare('UPDATE announcements SET is_pinned = 1 - is_pinned WHERE id = ?')->execute([$id]);
        redirect('admin/announcements.php');
    }
    if ($action === 'archive' || $action === 'publish') {
        $pdo->prepare('UPDATE announcements SET status = ?, published_at = COALESCE(published_at, NOW()) WHERE id = ?')
            ->execute([$action === 'archive' ? 'archived' : 'published', $id]);
        flash('success', 'Announcement ' . ($action === 'archive' ? 'archived.' : 'published.'));
        redirect('admin/announcements.php');
    }

    $values['title']     = trim($_POST['title'] ?? '');
    $values['content']   = trim($_POST['content'] ?? '');
    $values['status']    = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft';
    $values['is_pinned'] = isset($_POST['is_pinned']) ? 1 : 0;

    if (mb_strlen($values['title']) < 5) {
        $errors['title'] = 'Title must be at least 5 characters.';
    }
    if (mb_strlen($values['content']) < 10) {
        $errors['content'] = 'Content must be at least 10 characters.';
    }
    if (!$errors) {
        $img = upload_image('media', $uploadError);
        if ($uploadError) {
            $errors['media'] = $uploadError;
        } elseif ($img) {
            delete_upload($values['media_url'] ?? null);
            $values['media_url'] = $img;
        }
    }

    if (!$errors) {
        $publishedAt = $values['published_at'] ?? null;
        if ($values['status'] === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }
        if ($editId) {
            $pdo->prepare('UPDATE announcements SET title = ?, content = ?, media_url = ?, status = ?, is_pinned = ?, published_at = ? WHERE id = ?')
                ->execute([$values['title'], $values['content'], $values['media_url'] ?: null, $values['status'], $values['is_pinned'], $publishedAt, $editId]);
            flash('success', 'Announcement updated.');
        } else {
            $pdo->prepare('INSERT INTO announcements (title, content, media_url, author_id, approved_by, status, is_pinned, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$values['title'], $values['content'], $values['media_url'] ?: null, current_user_id(), current_user_id(), $values['status'], $values['is_pinned'], $publishedAt]);
            flash('success', 'Announcement created.');
        }
        redirect('admin/announcements.php');
    }
}

$list = $pdo->query(
    'SELECT an.*, u.name AS author_name FROM announcements an LEFT JOIN users u ON u.id = an.author_id
     ORDER BY an.is_pinned DESC, an.created_at DESC'
)->fetchAll();

$pageTitle = 'Announcements';
$adminNav  = 'announcements';
include __DIR__ . '/_header.php';
?>
<div class="admin-cols wide-left">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Title</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (!$list): ?><tr><td colspan="4" class="hint">No announcements yet.</td></tr><?php endif; ?>
            <?php foreach ($list as $an): ?>
                <tr>
                    <td><?= $an['is_pinned'] ? '📌 ' : '' ?><strong><?= e($an['title']) ?></strong>
                        <br><small class="hint"><?= e(summary($an['content'], 80)) ?></small></td>
                    <td><?= status_badge($an['status']) ?></td>
                    <td><small><?= e(date('M j, Y', strtotime($an['created_at']))) ?></small></td>
                    <td class="actions">
                        <a class="btn btn-sm" href="?edit=<?= (int) $an['id'] ?>">Edit</a>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $an['id'] ?>">
                            <button class="btn btn-sm" name="action" value="pin"><?= $an['is_pinned'] ? 'Unpin' : 'Pin' ?></button>
                            <?php if ($an['status'] === 'published'): ?>
                                <button class="btn btn-sm" name="action" value="archive">Archive</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-success" name="action" value="publish">Publish</button>
                            <?php endif; ?>
                        </form>
                        <form method="post" data-confirm="Delete this announcement?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $an['id'] ?>">
                            <button class="btn btn-sm btn-danger" name="action" value="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2><?= $editId ? 'Edit announcement' : 'New announcement' ?></h2>
        <form method="post" enctype="multipart/form-data" class="form" data-validate novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <label for="atitle">Title *</label>
            <input id="atitle" name="title" required minlength="5" maxlength="255" value="<?= e($values['title']) ?>">
            <?php if (isset($errors['title'])): ?><small class="field-error"><?= e($errors['title']) ?></small><?php endif; ?>

            <label for="acontent">Message *</label>
            <textarea id="acontent" name="content" rows="6" required minlength="10" maxlength="5000" data-counter><?= e($values['content']) ?></textarea>
            <?php if (isset($errors['content'])): ?><small class="field-error"><?= e($errors['content']) ?></small><?php endif; ?>

            <label for="amedia">Image (optional)</label>
            <input type="file" id="amedia" name="media" accept=".jpg,.jpeg,.png,.webp,.gif">
            <?php if (isset($errors['media'])): ?><small class="field-error"><?= e($errors['media']) ?></small><?php endif; ?>

            <label for="astatus">Status</label>
            <select id="astatus" name="status">
                <?php foreach (['published', 'draft', 'archived'] as $st): ?>
                    <option value="<?= $st ?>" <?= $values['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="check"><input type="checkbox" name="is_pinned" value="1" <?= $values['is_pinned'] ? 'checked' : '' ?>> Pin to top</label>

            <button class="btn btn-primary btn-block"><?= $editId ? 'Save changes' : 'Create announcement' ?></button>
            <?php if ($editId): ?><a class="btn btn-block center" href="<?= url('admin/announcements.php') ?>">Cancel</a><?php endif; ?>
        </form>
    </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
