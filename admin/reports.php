<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$isDate = fn($d) => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
$from = $isDate($_GET['from'] ?? '') ? $_GET['from'] : date('Y-m-d', strtotime('-29 days'));
$to   = $isDate($_GET['to'] ?? '')   ? $_GET['to']   : date('Y-m-d');
if ($from > $to) {
    [$from, $to] = [$to, $from];
}
$range = [$from . ' 00:00:00', $to . ' 23:59:59'];

$one = function ($sql, $params = []) use ($pdo) {
    $s = $pdo->prepare($sql);
    $s->execute($params);
    return (int) $s->fetchColumn();
};

$kpi = [
    'views'       => $one('SELECT COUNT(*) FROM article_views WHERE viewed_at BETWEEN ? AND ?', $range),
    'readers'     => $one('SELECT COUNT(DISTINCT user_id) FROM article_views WHERE user_id IS NOT NULL AND viewed_at BETWEEN ? AND ?', $range),
    'comments'    => $one('SELECT COUNT(*) FROM comments WHERE commented_at BETWEEN ? AND ?', $range),
    'bookmarks'   => $one('SELECT COUNT(*) FROM bookmarks WHERE created_at BETWEEN ? AND ?', $range),
    'new_users'   => $one("SELECT COUNT(*) FROM users WHERE role = 'student' AND created_at BETWEEN ? AND ?", $range),
    'published'   => $one("SELECT COUNT(*) FROM articles WHERE status = 'published' AND published_at BETWEEN ? AND ?", $range),
    'submissions' => $one('SELECT COUNT(*) FROM article_submissions WHERE submitted_at BETWEEN ? AND ?', $range),
    'approved'    => $one("SELECT COUNT(*) FROM article_submissions WHERE status = 'approved' AND submitted_at BETWEEN ? AND ?", $range),
];

$top = $pdo->prepare(
    "SELECT a.id, a.title, c.name AS category,
            COUNT(v.id) AS views,
            (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.id AND cm.commented_at BETWEEN ? AND ?) AS comments,
            (SELECT COUNT(*) FROM bookmarks b WHERE b.article_id = a.id) AS bookmarks
     FROM articles a
     JOIN categories c ON c.id = a.category_id
     LEFT JOIN article_views v ON v.article_id = a.id AND v.viewed_at BETWEEN ? AND ?
     WHERE a.status = 'published'
     GROUP BY a.id, a.title, c.name
     ORDER BY views DESC, comments DESC
     LIMIT 10"
);
$top->execute([$range[0], $range[1], $range[0], $range[1]]);
$topArticles = $top->fetchAll();

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="newsdesk-report-' . $from . '-to-' . $to . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['NSBM NewsDesk report', $from . ' to ' . $to]);
    fputcsv($out, []);
    foreach ($kpi as $k => $v) {
        fputcsv($out, [ucfirst(str_replace('_', ' ', $k)), $v]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Rank', 'Article', 'Category', 'Views', 'Comments', 'Bookmarks']);
    foreach ($topArticles as $i => $t) {
        fputcsv($out, [$i + 1, $t['title'], $t['category'], $t['views'], $t['comments'], $t['bookmarks']]);
    }
    fclose($out);
    exit;
}

$d = $pdo->prepare('SELECT DATE(viewed_at) AS day, COUNT(*) AS n FROM article_views WHERE viewed_at BETWEEN ? AND ? GROUP BY DATE(viewed_at)');
$d->execute($range);
$byDay = array_column($d->fetchAll(), 'n', 'day');
$daily = [];
for ($t = strtotime($from); $t <= strtotime($to) && count($daily) < 92; $t = strtotime('+1 day', $t)) {
    $daily[date('Y-m-d', $t)] = (int) ($byDay[date('Y-m-d', $t)] ?? 0);
}
$maxDaily = max(1, max($daily ?: [0]));

$cat = $pdo->prepare(
    "SELECT c.name,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND a.status = 'published') AS articles,
            (SELECT COUNT(*) FROM article_views v JOIN articles a ON a.id = v.article_id
              WHERE a.category_id = c.id AND v.viewed_at BETWEEN ? AND ?) AS views,
            (SELECT COUNT(*) FROM comments cm JOIN articles a ON a.id = cm.article_id
              WHERE a.category_id = c.id AND cm.commented_at BETWEEN ? AND ?) AS comments,
            (SELECT COUNT(*) FROM subscriptions s WHERE s.category_id = c.id) AS subscribers
     FROM categories c ORDER BY views DESC"
);
$cat->execute([$range[0], $range[1], $range[0], $range[1]]);
$byCategory = $cat->fetchAll();
$maxCatViews = max(1, max(array_column($byCategory, 'views') ?: [0]));

$act = $pdo->prepare(
    'SELECT u.name, u.faculty, COUNT(c.id) AS comments
     FROM comments c JOIN users u ON u.id = c.user_id
     WHERE c.commented_at BETWEEN ? AND ?
     GROUP BY u.id, u.name, u.faculty ORDER BY comments DESC LIMIT 5'
);
$act->execute($range);
$activeUsers = $act->fetchAll();

$pageTitle = 'Readership & engagement report';
$adminNav  = 'reports';
include __DIR__ . '/_header.php';
?>
<form class="row filters no-print" method="get">
    <label>From <input type="date" name="from" value="<?= e($from) ?>" class="filter-select"></label>
    <label>To <input type="date" name="to" value="<?= e($to) ?>" class="filter-select"></label>
    <button class="btn btn-sm btn-primary">Apply</button>
    <a class="btn btn-sm" href="?from=<?= e($from) ?>&to=<?= e($to) ?>&export=csv">Download CSV</a>
    <button type="button" class="btn btn-sm" onclick="window.print()">Print / Save PDF</button>
</form>
<p class="hint">Period: <strong><?= e(date('M j, Y', strtotime($from))) ?> – <?= e(date('M j, Y', strtotime($to))) ?></strong></p>

<div class="stats">
    <div class="stat"><b><?= $kpi['views'] ?></b><span>Article views</span></div>
    <div class="stat"><b><?= $kpi['readers'] ?></b><span>Logged-in readers</span></div>
    <div class="stat green"><b><?= $kpi['comments'] ?></b><span>Comments</span></div>
    <div class="stat"><b><?= $kpi['bookmarks'] ?></b><span>Bookmarks</span></div>
    <div class="stat"><b><?= $kpi['new_users'] ?></b><span>New students</span></div>
    <div class="stat green"><b><?= $kpi['published'] ?></b><span>Articles published</span></div>
    <div class="stat warn"><b><?= $kpi['approved'] ?>/<?= $kpi['submissions'] ?></b><span>Submissions approved</span></div>
</div>

<div class="panel">
    <h2>Daily views</h2>
    <div class="bar-chart" role="img" aria-label="Daily article views">
        <?php foreach ($daily as $day => $n): ?>
            <div class="bar-col" title="<?= e(date('M j', strtotime($day))) ?>: <?= $n ?> views">
                <span class="bar-val"><?= $n ?: '' ?></span>
                <div class="bar" style="height: <?= round($n / $maxDaily * 100) ?>%"></div>
                <small><?= e(date('j/n', strtotime($day))) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="admin-cols">
    <div class="panel">
        <h2>Top 10 articles</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>#</th><th>Article</th><th>Views</th><th>Comments</th><th>Saves</th></tr></thead>
                <tbody>
                <?php foreach ($topArticles as $i => $t): ?>
                    <tr><td><?= $i + 1 ?></td>
                        <td><a href="<?= url('articles/view.php?id=' . (int) $t['id']) ?>" target="_blank"><?= e($t['title']) ?></a><br><small class="hint"><?= e($t['category']) ?></small></td>
                        <td><?= (int) $t['views'] ?></td><td><?= (int) $t['comments'] ?></td><td><?= (int) $t['bookmarks'] ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$topArticles): ?><tr><td colspan="5" class="hint">No data.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h2>By category</h2>
        <?php foreach ($byCategory as $c): ?>
            <div class="hbar">
                <div class="hbar-label"><strong><?= e($c['name']) ?></strong>
                    <small class="hint"><?= (int) $c['articles'] ?> articles · <?= (int) $c['comments'] ?> comments · <?= (int) $c['subscribers'] ?> subscribers</small></div>
                <div class="hbar-track"><div class="hbar-fill" style="width: <?= round($c['views'] / $maxCatViews * 100) ?>%"></div></div>
                <span class="hbar-num"><?= (int) $c['views'] ?> views</span>
            </div>
        <?php endforeach; ?>

        <h2 style="margin-top:24px">Most active commenters</h2>
        <?php if (!$activeUsers): ?><p class="hint">No comments in this period.</p><?php endif; ?>
        <ol class="side-list">
            <?php foreach ($activeUsers as $u): ?>
                <li><?= e($u['name']) ?> <small><?= e($u['faculty']) ?> · <?= (int) $u['comments'] ?> comments</small></li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
