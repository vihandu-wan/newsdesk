<?php
session_start();
require_once __DIR__. '/../config/db.php';

if(!isset($_SESSION['user_id'])) {
    die("Please login first.");
}
if(!isset($_POST['article_id'])) {
    die("Article ID is missing.");
}
$user_id = $_SESSION['user_id'];
$article_id = (int) $_POST['article_id'];

$stmt = $pdo->prepare(
    "SELECT id FROM bookmarks
    WHERE user_id = ? AND article_id = ?"
);
$stmt->excute([$user_id, $article_id]);
$bookmark = $stmt->fetch(PDO:FETCH_ASSOC);
if ($bookmark) {
    $stmt = $pdo->prepare(
        "DELETE FROM bookmarks
        WHERE user_id = ? AND article_id = ?"
    );
    $stmt->execute([$user_id, $article_id]);
    echo "Bookmark removed.";
} else{
    $stmt = $pdo->prepare(
        "INSERT INTO bookmarks (user_id, article_id)
        VALUES (?, ?)"
    );
    $stmt->execute([$user_id, $article_id]);
    echo "Article bookmarked.";
}
?>