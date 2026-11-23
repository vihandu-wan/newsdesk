<?php
<<<<<<< HEAD
<<<<<<< HEAD
require_once __DIR__ . '/../includes/init.php';
require_login();

$uid = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: 0;
    $action     = $_POST['action'] ?? '';

    $exists = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
    $exists->execute([$categoryId]);
    $catName = $exists->fetchColumn();

    if (!$catName) {
        flash('error', 'Invalid category.');
    } elseif ($action === 'subscribe') {
        $pdo->prepare('INSERT IGNORE INTO subscriptions (user_id, category_id) VALUES (?, ?)')->execute([$uid, $categoryId]);
        flash('success', 'You are now following ' . $catName . '.');
    } elseif ($action === 'unsubscribe') {
        $pdo->prepare('DELETE FROM subscriptions WHERE user_id = ? AND category_id = ?')->execute([$uid, $categoryId]);
        flash('success', 'You unfollowed ' . $catName . '.');
    }

    if (!empty($_POST['back'])) {
        redirect('categories.php?name=' . urlencode($_POST['back']));
    }
    redirect('subscriptions/categories.php');
}

$stmt = $pdo->prepare(
    "SELECT c.id, c.name, c.description,
            (s.id IS NOT NULL) AS is_subscribed,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND a.status = 'published') AS article_count
     FROM categories c
     LEFT JOIN subscriptions s ON s.category_id = c.id AND s.user_id = ?
     ORDER BY c.name"
);
$stmt->execute([$uid]);
$categories = $stmt->fetchAll();

$pageTitle = 'Follow categories | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="page-head">
        <div>
            <h1>Follow categories</h1>
            <p class="hint">Articles from the categories you follow appear in your personal feed.</p>
        </div>
        <div class="actions">
            <a class="btn" href="<?= url('subscriptions/authors.php') ?>">Follow authors</a>
            <a class="btn btn-primary" href="<?= url('subscriptions/index.php') ?>">My feed &rarr;</a>
        </div>
    </div>

    <div class="sub-grid">
        <?php foreach ($categories as $cat): ?>
            <div class="sub-card <?= $cat['is_subscribed'] ? 'on' : '' ?>">
                <h3><?= e($cat['name']) ?></h3>
                <p class="hint"><?= e($cat['description'] ?: 'Campus news') ?></p>
                <small class="hint"><?= (int) $cat['article_count'] ?> articles</small>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="category_id" value="<?= (int) $cat['id'] ?>">
                    <?php if ($cat['is_subscribed']): ?>
                        <button class="btn btn-outline btn-block" name="action" value="unsubscribe">✓ Following</button>
                    <?php else: ?>
                        <button class="btn btn-success btn-block" name="action" value="subscribe">+ Follow</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
=======
=======
>>>>>>> origin/UI-and-final
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

<<<<<<< HEAD
</html>
>>>>>>> origin/newsdesk-admin-panel
=======
</html>
>>>>>>> origin/UI-and-final
