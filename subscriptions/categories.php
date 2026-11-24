<?php
session_start();

require_once __DIR__ . '/../config/db.php';
if(!isset($_SESSION['user_id'])){
    die("please login first.");
}
$user_id = $_SESSION['user_id'];
if(empty($_SESSION['category_csrf'])){
$_SESSION['category_csrf'] = bin2hex(random_bytes(32));
}
$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if(!is_string($token) ||
    !hash_equals($_SESSION['category_csrf'],$token)) {
        die("Invalid request");
    }
    $category_id = filter_input(
        INPUT_POST,
        'category_id',
        FILTER_VALIDATE_INT
    );
    
    $action = $_POST['action'] ?? '';
    
    if(!$category_id) {
        $message = "Invalid category.";
    }elseif($action === 'subscribe') {
        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO subscriptions
            (user_id, category_id)
            VALUES(?, ?)"
        );
        $stmt->execute([$user_id, $category_id]);
        $message = "Subscribed successfully.";
    } elseif($action === 'unsubscribe') {
        $stmt = $pdo->prepare(
            "DELETE FROM subscriptions
            WHERE user_id = ? AND category_id = ?"
        );
        $stmt->execute([$user_id, $category_id]);
        $message = "Unsubscribed successfully.";
    }
}
 
$stmt = $pdo->prepare(
    "SELECT categories.id,
    categories.name,
    CASE
        WHEN subscriptions.id IS NULL THEN 0
        ELSE 1
        END AS is_subscribed
        FROM categories
        LEFT JOIN subscriptions
         ON categories.id = subscriptions.category_id
        AND subscriptions.user_id =  ?
        ORDER BY categories.name"
);
 
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<html>

<head>
    <title>Category Subscriptions</title>
</head>

<body>
    <h1>Category Subscriptions</h1>
    <?php if(!empty($message)): ?>
    <P><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if (empty($categories)): ?>
    <p> No categories available.</p>
    <?php else: ?>
    <?php foreach($categories as $category): ?>
    <div>
        <h3>
            <?php echo htmlspecialchars($category['name']); ?>
        </h3>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['category_csrf']); ?>">
            <input type="hidden" name="category_id" value="<?php echo (int)$category['id']; ?>">
            <?php if($category['is_subscribed']): ?>
            <button type="submit" name="action" value="unsubscribe">
                unsubscribe</button>
            <?php else: ?>
            <button type="submit" name="action" value="subscribe">
                subscribe</button>
            <?php endif; ?>
        </form>
    </div>
    <hr>
    <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>