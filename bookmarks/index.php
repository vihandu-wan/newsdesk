<?php
session_start();

require_once __DIR__ .'/../config/db.php';
if(!isset($_SESSION['user_id'])) {
    die("please login first.");
}
$user_id = $_SESIION['user_id'];
$stmt = $pdo->prepare(
    "SELECT articles.id,
            articles.title,
            articles.content,
            articles.image_url,
            articles.published_at,
            bookmarks.created_at AS bookmarked_at
        FROM bookmarks
        INNER JOIN articles
          ON bookmarks.article_id = articles.id
        WHERE bookmarks.user_id = ?
        ORDER BY bookmarks.created_at DESC"

);
$stmt->execute([$user_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>

<head>
    <title>My Bookmark</title>
</head>

<body>
    <h1>My Saved Articles</h1>
    <?php if (count($articles == 0)): ?>
    <?php else: ?>
    <?php foreach ($articles as $article): ?>
    <div>
        <h2>
            <?php echo htmlspecialchard($article['title']);?>
        </h2>
        <?php if (!empty($article['image_url'])): ?>
        <img src="<?php echo htmlspecialchars($article['image_url']);?>" width="200" alt="Article image">
        <?php endif; ?>
        <p>
            <?php echo htmlspecialcharms(substr($article['content'],0,200)); ?>
        </p>
        <p>
            Bookmark on:
            <?php echo htmlspecialchars($article['bookmarked']); ?>
        </p>
    </div>
    <hr>
    <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>