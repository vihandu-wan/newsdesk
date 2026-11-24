<?php
require_once __DIR__ . '/includes/init.php';

$range = $_GET['range'] ?? 'all';
if (!in_array($range, ['today', 'week', 'month', 'all'], true)) {
    $range = 'all';
}
$category = $_GET['category'] ?? '';

$where  = "a.status = 'published'";
$params = [];
if ($range === 'today') {
    $where .= ' AND DATE(a.published_at) = CURDATE()';
} elseif ($range === 'week') {
    $where .= ' AND a.published_at >= NOW() - INTERVAL 7 DAY';
} elseif ($range === 'month') {
    $where .= ' AND a.published_at >= NOW() - INTERVAL 1 MONTH';
}
if ($category !== '') {
    $where .= ' AND c.name = ?';
    $params[] = $category;
}

$stmt = $pdo->prepare(
    "SELECT a.id, a.title, a.content, a.image_url, a.created_at, a.published_at, a.is_featured,
            c.name AS category_name
     FROM articles a
     JOIN categories c ON c.id = a.category_id
     WHERE $where
     ORDER BY a.is_featured DESC, a.published_at DESC
     LIMIT 31"
);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$featured = $articles[0] ?? null;
$others   = array_slice($articles, 1);

$announcements = $pdo->query(
    "SELECT id, title, published_at FROM announcements
     WHERE status = 'published'
     ORDER BY is_pinned DESC, published_at DESC LIMIT 4"
)->fetchAll();

$mySubs = [];
if (is_logged_in()) {
    $s = $pdo->prepare(
        'SELECT c.name FROM subscriptions s JOIN categories c ON c.id = s.category_id
         WHERE s.user_id = ? ORDER BY c.name'
    );
    $s->execute([current_user_id()]);
    $mySubs = $s->fetchAll(PDO::FETCH_COLUMN);
}

$popular = $pdo->query(
    "SELECT a.id, a.title, COUNT(v.id) AS views
     FROM articles a JOIN article_views v ON v.article_id = a.id
     WHERE a.status = 'published' AND v.viewed_at >= NOW() - INTERVAL 30 DAY
     GROUP BY a.id, a.title ORDER BY views DESC LIMIT 5"
)->fetchAll();

$keep = $category !== '' ? '&category=' . urlencode($category) : '';

$pageTitle = 'Home | NSBM NewsDesk';
$activeNav = 'Home';
include __DIR__ . '/includes/header.php';
?>
<main>
    <div class="slider" id="slider">
        <div class="slide on"><img src="<?= url('assets/slide1.jpg') ?>" alt="Campus"><div class="caption">Welcome to NSBM Green University</div></div>
        <div class="slide"><img src="<?= url('assets/slide2.jpg') ?>" alt="Events"><div class="caption">Upcoming Events</div></div>
        <div class="slide"><img src="<?= url('assets/slide3.jpg') ?>" alt="Sports"><div class="caption">Sports and Clubs</div></div>
        <div class="slide"><img src="<?= url('assets/slide4.jpeg') ?>" alt="Academic"><div class="caption">Academic News</div></div>
        <button class="arrow left" type="button" data-slide="-1" aria-label="Previous slide">&#10094;</button>
        <button class="arrow right" type="button" data-slide="1" aria-label="Next slide">&#10095;</button>
    </div>

    <div class="row">
        <?php foreach (['today' => 'Today', 'week' => 'This week', 'month' => 'This month', 'all' => 'All'] as $key => $label): ?>
            <a class="filter <?= $range === $key ? 'active' : '' ?>" href="<?= url('index.php?range=' . $key . $keep) ?>"><?= $label ?></a>
        <?php endforeach; ?>

        <form action="<?= url('index.php') ?>" method="get">
            <input type="hidden" name="range" value="<?= e($range) ?>">
            <select class="filter-select" name="category" data-autosubmit aria-label="Category">
                <option value="">All categories</option>
                <?php foreach (get_categories($pdo) as $cat): ?>
                    <option value="<?= e($cat['name']) ?>" <?= $category === $cat['name'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="home">
        <div>
            <?php if (!$featured): ?>
                <div class="empty">No articles found for this filter.</div>
            <?php else: ?>
                <a class="hero <?= $featured['image_url'] ? 'with-image' : '' ?>"
                   href="<?= url('articles/view.php?id=' . (int) $featured['id']) ?>"
                   <?php if ($featured['image_url']): ?>style="background-image:linear-gradient(90deg,rgba(0,83,168,.92),rgba(0,150,88,.55)),url('<?= e(imageSrc($featured['image_url'])) ?>')"<?php endif; ?>>
                    <?php if ($featured['is_featured']): ?><span class="pill">Featured</span><?php endif; ?>
                    <h2><?= e($featured['title']) ?></h2>
                    <p><?= e(summary($featured['content'], 180)) ?></p>
                    <div class="meta">
                        <span><?= e($featured['category_name']) ?></span>
                        <span>· <?= timeAgo($featured['published_at']) ?></span>
                    </div>
                </a>

                <div class="grid">
                    <?php foreach ($others as $article): ?>
                        <?php include __DIR__ . '/includes/card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside>
            <div class="side">
                <h3>Your subscriptions</h3>
                <?php if (!is_logged_in()): ?>
                    <p class="hint">Log in to follow categories and get a personal feed.</p>
                    <a class="filter" href="<?= url('auth/login.php') ?>">Log in</a>
                <?php elseif (!$mySubs): ?>
                    <p class="hint">You are not following any category yet.</p>
                    <a class="filter" href="<?= url('subscriptions/categories.php') ?>">Choose categories</a>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($mySubs as $sub): ?>
                            <a class="filter" href="<?= url('categories.php?name=' . urlencode($sub)) ?>"><?= e($sub) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= url('subscriptions/index.php') ?>">Open my feed &rarr;</a>
                <?php endif; ?>
            </div>

            <div class="side">
                <h3>Announcements</h3>
                <?php if (!$announcements): ?>
                    <p class="hint">No announcements right now.</p>
                <?php else: ?>
                    <ul class="side-list">
                        <?php foreach ($announcements as $an): ?>
                            <li>
                                <a href="<?= url('announcements/index.php#a' . (int) $an['id']) ?>"><?= e($an['title']) ?></a>
                                <small><?= timeAgo($an['published_at']) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <?php if ($popular): ?>
                <div class="side">
                    <h3>Most read this month</h3>
                    <ol class="side-list">
                        <?php foreach ($popular as $p): ?>
                            <li><a href="<?= url('articles/view.php?id=' . (int) $p['id']) ?>"><?= e($p['title']) ?></a>
                                <small><?= (int) $p['views'] ?> views</small></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
