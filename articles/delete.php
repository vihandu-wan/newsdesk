<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION["user_id"])) {
    die("Please log in first.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request. Articles must be deleted using the POST method.");
}

if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
    die("Invalid request.");
}

$id = (int)($_POST["id"] ?? 0);
$user_id = (int)$_SESSION["user_id"];
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

if ($id <= 0) {
    die("Invalid article ID.");
}

// Find the article first so its uploaded image can also be removed.
if ($is_admin) {
    $stmt = $conn->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT image FROM articles WHERE id = ? AND author_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
}
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$article) {
    die("Article not found or you are not authorized to delete it.");
}

if ($is_admin) {
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ? AND author_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
}

if ($stmt->execute() && $stmt->affected_rows > 0) {
    if (!empty($article["image"])) {
        $image_path = "../uploads/" . basename($article["image"]);
        if (is_file($image_path)) {
            unlink($image_path);
        }
    }
    echo "Article deleted successfully. <a href='../admin/articles.php'>Back to articles</a>";
} else {
    echo "Could not delete the article.";
}

$stmt->close();
$conn->close();
?>
