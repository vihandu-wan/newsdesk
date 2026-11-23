<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $action = $_POST['action'] ?? '';

    if ($action === 'featured') {
        $pdo->prepare('UPDATE articles SET is_featured = 1 - is_featured WHERE id = ?')->execute([$id]);
        flash('success', 'Featured flag updated.');
    } elseif ($action === 'breaking') {
        $pdo->prepare('UPDATE articles SET is_breaking = 1 - is_breaking WHERE id = ?')->execute([$id]);
        flash('success', 'Breaking-news flag updated.');
    } elseif ($action === 'publish') {
        $pdo->prepare("UPDATE articles SET status = 'published', published_at = COALESCE(published_at, NOW()) WHERE id = ?")->execute([$id]);
        flash('success', 'Article published.');
    } elseif ($action === 'unpublish') {
        $pdo->prepare("UPDATE articles SET status = 'draft', is_breaking = 0, is_featured = 0 WHERE id = ?")->execute([$id]);
        flash('success', 'Article moved to drafts.');
    }
    redirect('admin/articles.php?' . http_build_query(array_intersect_key($_GET, array_flip(['status', 'category', 'q']))));
}

$status   = $_GET['status'] ?? '';
$category = (int) ($_GET['category'] ?? 0);
$q        = trim($_GET['q'] ?? '');

$where  = ['1 = 1'];
$params = [];
if (in_array($status, ['draft', 'published'], true)) {
    $where[]  = 'a.status = ?';
    $params[] = $status;
}
if ($category > 0) {
    $where[]  = 'a.category_id = ?';
    $params[] = $category;
}
if ($q !== '') {
    $where[]  = 'a.title LIKE ?';
    $params[] = '%' . $q . '%';
}

$stmt = $pdo->prepare(
    "SELECT a.id, a.title, a.status, a.is_featured, a.is_breaking, a.created_at, a.published_at,
            c.name AS category_name, u.name AS author_name,
            (SELECT COUNT(*) FROM article_views v WHERE v.article_id = a.id) AS views,
            (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.id) AS comment_count
     FROM articles a
     JOIN categories c ON c.id = a.category_id
     LEFT JOIN users u ON u.id = a.author_id
     WHERE " . implode(' AND ', $where) . "
     ORDER BY a.created_at DESC"
);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$pageTitle = 'Articles';
$adminNav  = 'articles';
include __DIR__ . '/_header.php';
?>
<div class="page-head">
    <form class="row filters" method="get">
        <input name="q" value="<?= e($q) ?>" placeholder="Search title" class="filter-select">
        <select name="status" class="filter-select" data-autosubmit>
            <option value="">All statuses</option>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
        <select name="category" class="filter-select" data-autosubmit>
            <option value="0">All categories</option>
            <?php foreach (get_categories($pdo) as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= $category === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-sm">Filter</button>
    </form>
    <a class="btn btn-primary" href="<?= url('articles/create.php') ?>">+ New article</a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Flags</th><th>Views</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$articles): ?>
            <tr><td colspan="8" class="hint">No articles found.</td></tr>
        <?php endif; ?>
        <?php foreach ($articles as $a): ?>
            <tr>
                <td><a href="<?= url('articles/view.php?id=' . (int) $a['id']) ?>" target="_blank"><?= e($a['title']) ?></a>
                    <br><small class="hint"><?= (int) $a['comment_count'] ?> comments</small></td>
                <td><?= e($a['category_name']) ?></td>
                <td><?= e($a['author_name'] ?? '—') ?></td>
                <td><?= status_badge($a['status']) ?></td>
                <td class="actions">
                    <?php foreach (['featured' => '★ Featured', 'breaking' => '⚡ Breaking'] as $flag => $label): ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button class="chip <?= $a['is_' . $flag] ? 'on' : '' ?>" name="action" value="<?= $flag ?>"
                                    title="Toggle <?= $flag ?>"><?= $label ?></button>
                        </form>
                    <?php endforeach; ?>
                </td>
                <td><?= (int) $a['views'] ?></td>
                <td><small><?= e(date('M j, Y', strtotime($a['published_at'] ?? $a['created_at']))) ?></small></td>
                <td class="actions">
                    <a class="btn btn-sm" href="<?= url('articles/edit.php?id=' . (int) $a['id']) ?>">Edit</a>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                        <?php if ($a['status'] === 'published'): ?>
                            <button class="btn btn-sm" name="action" value="unpublish">Unpublish</button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-success" name="action" value="publish">Publish</button>
                        <?php endif; ?>
                    </form>
                    <form method="post" action="<?= url('articles/delete.php') ?>" data-confirm="Delete this article permanently? Comments and bookmarks will also be removed.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
