<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$range = $_GET['range'] ?? 'all';
if (!in_array($range, ['today', 'week', 'month', 'all'])) {
    $range = 'all';
}
$category = $_GET['category'] ?? '';

$extra = "";
if ($range === 'today') {
    $extra = "AND DATE(articles.created_at) = CURDATE()";
} elseif ($range === 'week') {
    $extra = "AND articles.created_at >= NOW() - INTERVAL 7 DAY";
} elseif ($range === 'month') {
    $extra = "AND articles.created_at >= NOW() - INTERVAL 1 MONTH";
}

$params = [];
if ($category !== '') {
    $extra = $extra . " AND categories.name = ?";
    $params[] = $category;
}

$sql = "SELECT
            articles.id,
            articles.title,
            articles.content,
            articles.created_at,
            categories.name AS category_name
        FROM articles
        INNER JOIN categories
            ON articles.category_id = categories.id
        WHERE articles.status = 'published'
        $extra
        ORDER BY articles.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$keep = "";
if ($category !== '') {
    $keep = "&category=" . urlencode($category);
}

$pageTitle = 'Home | NSBM NewsDesk'; 
$activeNav = 'Home';

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Home | NSBM NewsDesk</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>



    <body>
        <header>
            <div class="bar">
                <a href="index.php"><img class="logo" src="assets/logo.png" alt="NSBM"></a>
                <a class="brand" href="index.php">NewsDesk</a>

                <nav>
                    <a href="index.php" class="on">Home</a>
                    <a href="categories.php?name=Academic">Academic</a>
                    <a href="categories.php?name=Sports">Sports / Clubs</a>
                    <a href="categories.php?name=Events">Events</a>
                    <a href="categories.php?name=General">General</a>
                </nav>

                <form class="search-form" action="search.php" method="get">
                    <input class="search" name="q" placeholder="Search news">
                </form>
               <a class="login" href="auth/login.php">Log in</a>
            </div>
        </header>

        <main>
            <div class="slider">
                <div class="slide on">
                    <img src="assets/slide1.jpg" alt="Campus">
                    <div class="caption">Welcome to NSBM Green University</div>
                </div>

                <div class="slide">
                    <img src="assets/slide2.jpg" alt="events">
                    <div class="caption">Upcoming Events</div>
                </div>

                <div class="slide">
                    <img src="assets/slide3.jpg" alt="sports">
                    <div class="caption">Sports and Clubs</div>
                </div>

                <div class="slide">
                    <img src="assets/slide4.jpeg" alt="academic">
                    <div class="caption">Academic News</div>
                </div>


                <button class="arrow left" onclick="prevSlide()">&#10094;</button>
                <button class="arrow right" onclick="nextSlide()">&#10095;</button>

            </div>


            <div class="row">
                <a class="filter <?php if ($range === 'today') echo 'active'; ?>" href="index.php?range=today<?= $keep ?>">Today</a>
                <a class="filter <?php if ($range === 'week') echo 'active'; ?>" href="index.php?range=week<?= $keep ?>">This week</a>
                <a class="filter <?php if ($range === 'month') echo 'active'; ?>" href="index.php?range=month<?= $keep ?>">This month</a>
                <a class="filter <?php if ($range === 'all') echo 'active'; ?>" href="index.php?range=all<?= $keep ?>">All</a>


                <form action="index.php" method="get">
                    <input type="hidden" name="range" value="<?= htmlspecialchars($range) ?>">
                    <select class="filter-select" name="category" onchange="this.form.submit()">
                        <option value="">All categories</option>
                        <option value="Academic" <?php if ($category === 'Academic') echo 'selected'; ?>>Academic</option>
                        <option value="Sports"   <?php if ($category === 'Sports')   echo 'selected'; ?>>Sports</option>
                        <option value="Events"   <?php if ($category === 'Events')   echo 'selected'; ?>>Events</option>
                        <option value="General"  <?php if ($category === 'General')  echo 'selected'; ?>>General</option>
                    </select>
                </form>
            </div>

            





            <div class="home">
                <div>
                    <?php if (count($articles) === 0): ?>
                        <p>No articles found for this filter.</p>
                    <?php else: ?>
                        <?php
                        $featured = $articles[0];
                        $others   = array_slice($articles, 1);?>

                        <a class="hero" href="article/view.php?id=<?= (int)$featured['id'] ?>">
                            <h2><?= htmlspecialchars($featured['title']) ?></h2>
                            <div class="meta">
                                <span><?= htmlspecialchars($featured['category_name']) ?></span>
                                <span>· <?= timeAgo($featured['created_at']) ?></span>
                            </div>
                        </a>

                        <div class="grid">
                            <?php foreach ($others as $article): ?>
                                <a class="card" href="article/view.php?id=<?= (int)$article['id'] ?>">
                                    <div class="thumb placeholder">Photo</div>
                                    <h3><?= htmlspecialchars($article['title']) ?></h3>
                                    <p><?= htmlspecialchars(summary($article['content'])) ?></p>
                                    <div class="meta">
                                        <span class="tag"><?= htmlspecialchars($article['category_name']) ?></span>
                                        <span><?= timeAgo($article['created_at']) ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="side">
                    <h3>Your subscriptions</h3>
                    <p class="hint">Log in to see your subscriptions.</p>
                    <a class="filter" href="auth/login.php">Log in</a>
                 </aside>

            </div>


        </main>

         <script>
        var slides = document.querySelectorAll(".slide");
        var current = 0;
        var timer = setInterval(nextSlide, 4000);

        function nextSlide() {
            slides[current].classList.remove("on");
            current = current + 1;
            if (current >= slides.length) {
            current = 0;
            }
            slides[current].classList.add("on");
            restartTimer();
            }

        function prevSlide() {
            slides[current].classList.remove("on");
            current = current - 1;
            if(current < 0) {
                current = slides.length - 1;
            }
            slides[current].classList.add("on");
            restartTimer();
        }

        function restartTimer() {
            clearInterval(timer);
            timer = setInterval(nextSlide, 4000);
        }

    </script>

    </body>
</html>