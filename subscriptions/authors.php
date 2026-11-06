<?php
session_start();

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}
require_once __DIR__ . '/../config/db.php';
$student_id = $_SESSION['user_id'];

if(empty($_SESSION['author_csrf'])) {
   $_SESSION['author_csrf'] = bin2hex(random_bytes(32));
}
$message= '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if(!is_string($token) || !hash_equals($_SESSION['author_csrf'], $token)) {
        die("Invalid request");
    }
    $author_id = filter_input(
        INPUT_POST,
        'author_id',
        FILTER_VALIDATE_INT
    );
    $action = $_POST['action'] ?? '';
    if(!$author_id) {
        $message = "Invalid author.";
    } else{
        $check = $pdo->prepare(
            "SELECT id FROM users
            WHERE ID = ?
            AND ID <> ?
            AND EXISTS (
            SELECT 1 FROM articles
            WHERE articles.author_id = users.id
            AND articles.status = 'published'
        )"
        );
        $check->execute([$author_id,$student_id]);
        if(!$check->fetch(PDO::FETCH_ASSOC)) {
            $message = "Author not found.";

        } elseif($action === 'follow') {
            $stmt =$pdo->prepare(
                "INSERT IGNORE INTO author_subscriptions
                (student_id, author_id)
                VALUES(?,?)"
            );
            $stmt->execute([$student_id,$author_id]);
            $message = "Author followed succesfully.";
        } elseif ($action === 'unfollow') {
            $stmt = $pdo->prepare(
                "DELETE FROM author_subscriptions
                WHERE student_id = ? AND author_id = ?"
            );
            $stmt->execute([$student_id, $author_id]);
            $message = "Author unfollowed succesfully.";
        } else {
            $message = "Invalid action.";
        }
    }

}
$stmt = $pdo->prepare(
    "SELECT users.id,
    users.name,
    CASE
        WHEN author_subscriptions.id IS NULL THEN 0
        ELSE 1
        END AS is_following
    FROM users
    LEFT JOIN author_subscriptions ON users.id = author_subscriptions.author_id AND author_subscriptions.student_id = ?
    WHERE users.id <> ?
    AND EXISTS (
        SELECT 1 FROM articles
        WHERE articles.author_id = users.id
        AND articles.status = 'published'
    )
    ORDER BY users.name"
);
$stmt->execute([$student_id, $student_id]);
$authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Author subscriptions</title>
</head>

<body>
    <h1>Manage Author Subscriptions</h1>
    <?php if (!empty($message)): ?>
    <p>
        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
    </p>
    <?php endif; ?>
    <?php if (empty($authors)): ?>
    <p> No authors available.</p>
    <?php else: ?>
    <?php foreach($authors as $author): ?>
    <div>
        <h3>
            <?php echo htmlspecialchars($author['name'],ENT_QUOTES, 'UTF-8'); ?>
        </h3>
        <form method="POST">
            <input type="hidden" name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['author_csrf'],ENT_QUOTES,'UTF-8'); ?>">
            <input type="hidden" name="author_id" value="<?php echo (int) $author['id']; ?>">
            <?php if ($author['is_following']): ?>
            <button type="submit" name="action" value="unfollow">
                unfollow
            </button>
            <?php else: ?>
            <button type="submit" name="action" value="follow">
                follow
            </button>
            <?php endif; ?>
        </form>
    </div>
    <hr>
    <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>