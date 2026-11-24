<?php
require_once __DIR__ . '/includes/init.php';

$q        = trim($_GET['q'] ?? '');
$category = (int) ($_GET['category'] ?? 0);
$from     = $_GET['from'] ?? '';
$to       = $_GET['to'] ?? '';
$sort     = ($_GET['sort'] ?? 'new') === 'old' ? 'ASC' : 'DESC';

$isDate = fn($d) => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);

$where  = ["a.status = 'published'"];
$params = [];

if ($q !== '') {
    $where[]  = '(a.title LIKE ? OR a.content LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($category > 0) {
    $where[]  = 'a.category_id = ?';
    $params[] = $category;
}
if ($isDate($from)) {
    $where[]  = 'DATE(a.published_at) >= ?';
    $params[] = $from;
}
if ($isDate($to)) {
    $where[]  = 'DATE(a.published_at) <= ?';
    $params[] = $to;
}

$searched = $q !== '' || $category > 0 || $isDate($from) || $isDate($to);
$articles = [];

if ($searched) {
    $stmt = $pdo->prepare(
        "SELECT a.id, a.title, a.content, a.image_url, a.created_at, a.published_at, c.name AS category_name
         FROM articles a JOIN categories c ON c.id = a.category_id
         WHERE " . implode(' AND ', $where) . "
         ORDER BY a.published_at $sort
         LIMIT 60"
    );
    $stmt->execute($params);
    $articles = $stmt->fetchAll();
}

$pageTitle = 'Search | NSBM NewsDesk';
include __DIR__ . '/includes/header.php';
?>
<main>
    <h1>Search news</h1>

    <form class="panel search-panel form" method="get" action="<?= url('search.php') ?>">
        <div class="search-grid">
            <div>
                <label for="sq">Keywords</label>
                <input id="sq" name="q" value="<?= e($q) ?>" placeholder="e.g. convocation, hackathon">
            </div>
            <div>
                <label for="scat">Category</label>
                <select id="scat" name="category">
                    <option value="0">All categories</option>
                    <?php foreach (get_categories($pdo) as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= $category === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="sfrom">From</label>
                <input type="date" id="sfrom" name="from" value="<?= e($from) ?>">
            </div>
            <div>
                <label for="sto">To</label>
                <input type="date" id="sto" name="to" value="<?= e($to) ?>">
            </div>
            <div>
                <label for="ssort">Sort</label>
                <select id="ssort" name="sort">
                    <option value="new">Newest first</option>
                    <option value="old" <?= $sort === 'ASC' ? 'selected' : '' ?>>Oldest first</option>
                </select>
            </div>
            <div class="search-actions">
                <button class="btn btn-primary" type="submit">Search</button>
                <a class="btn" href="<?= url('search.php') ?>">Clear</a>
            </div>
        </div>
    </form>

    <?php if (!$searched): ?>
        <p class="hint">Type a keyword or choose a category / date range to find articles.</p>
    <?php elseif (!$articles): ?>
        <div class="empty">No articles match your search.</div>
    <?php else: ?>
        <p class="hint"><?= count($articles) ?> result(s)<?= $q !== '' ? ' for "' . e($q) . '"' : '' ?></p>
        <div class="grid">
            <?php foreach ($articles as $article): ?>
                <?php include __DIR__ . '/includes/card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
