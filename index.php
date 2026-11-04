<?php
require_once __DIR__ . '/config/db.php';

$range = $_GET['range'] ?? 'all';
$extra = "";
if ($range === 'today') {
    $extra = "AND DATE(articles.created_at) = CURDATE()";
} elseif ($range === 'week') {
    $extra = "AND articles.created_at >= NOW() - INTERVAL 7 DAY";
} elseif ($range === 'month') {
    $extra = "AND articles.created_at >= NOW() - INTERVAL 1 MONTH";
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

$stmt = $pdo->query($sql);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <a class="filter <?php if ($range === 'today') echo 'active'; ?>" href="index.php?range=today">Today</a>
                <a class="filter <?php if ($range === 'week') echo 'active'; ?>" href="index.php?range=week">This week</a>
                <a class="filter <?php if ($range === 'month') echo 'active'; ?>" href="index.php?range=month">This month</a>
                <a class="filter <?php if ($range === 'all') echo 'active'; ?>" href="index.php">All</a>
                <select class="filter-select">
                    <option>All categories</option>
                    <option>Academic</option>
                    <option>Events</option>
                </select>
            </div>

            








            
            <?php if (count($articles) === 0): ?>
                <p>No published news articles yet.</p>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <article>
                        <h3><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                        <p>Category: <?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?></p>

                        <p><?= nl2br(htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8')) ?></p>

                        <small>Published: <?= htmlspecialchars($article['created_at']) ?></small>

                        <hr>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
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