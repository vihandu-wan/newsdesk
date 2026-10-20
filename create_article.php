
<?php
require_once __DIR__ . '/config/db.php';

$message = '';
$categories = $pdo->query(
    "SELECT id, name FROM categories ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = filter_input(
        INPUT_POST,
        'category_id',
        FILTER_VALIDATE_INT
    );

    if ($title === '' || $content === '' || !$categoryId) {
        $message = 'Please fill in all fields.';
    } else {
        $check = $pdo->prepare(
            "SELECT id FROM categories WHERE id = ?"
        );
        $check->execute([$categoryId]);

        if (!$check->fetch()) {
            $message = 'Please select a valid category.';
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO articles
                (title, content, category_id, status)
                VALUES (?, ?, ?, 'draft')"
            );

            $stmt->execute([
                $title,
                $content,
                $categoryId
            ]);

            $message = 'Article saved successfully!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create News | NSBM NewsDesk</title>
</head>
<body>
    <h1>NSBM NewsDesk</h1>
    <h2>Create a News Article</h2>

    <p>
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <form method="POST">
        <label for="title">Article Title</label><br>
        <input
            type="text"
            id="title"
            name="title"
            maxlength="255"
            required
        ><br><br>

        <label for="content">Article Content</label><br>
        <textarea
            id="content"
            name="content"
            rows="7"
            required
        ></textarea><br><br>

        <label for="category_id">Category</label><br>
        <select id="category_id" name="category_id" required>
            <option value="">Choose a category</option>

            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>">
                    <?= htmlspecialchars(
                        $category['name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Save Article as Draft</button>
    </form>
</body>
</html>
