<?php
session_start();
require_once "../db.php";

// Only administrators can access this page.
if (!isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin") {
    die("Access denied. Admin permission is required.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$sql = "SELECT a.id, a.title, a.status, a.created_at,
               a.author_id, c.category_name
        FROM articles AS a
        LEFT JOIN categories AS c ON a.category_id = c.id
        ORDER BY a.created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Articles</title>
</head>
<body>
<h2>Manage Articles</h2>

<p><a href="../articles/create.php">Create a new article</a></p>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Category</th>
        <th>Author ID</th>
        <th>Status</th>
        <th>Created</th>
        <th>Actions</th>
    </tr>

    <?php if ($result && $result->num_rows > 0) { ?>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo (int)$row["id"]; ?></td>
                <td><?php echo htmlspecialchars($row["title"]); ?></td>
                <td><?php echo htmlspecialchars($row["category_name"] ?? "No category"); ?></td>
                <td><?php echo (int)$row["author_id"]; ?></td>
                <td><?php echo htmlspecialchars($row["status"]); ?></td>
                <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                <td>
                    <a href="../articles/edit.php?id=<?php echo (int)$row["id"]; ?>">Edit</a>

                    <form method="POST" action="../articles/delete.php"
                          style="display:inline;"
                          onsubmit="return confirm('Are you sure you want to delete this article?');">
                        <input type="hidden" name="id" value="<?php echo (int)$row["id"]; ?>">
                        <input type="hidden" name="csrf_token"
                               value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td colspan="7">No articles found.</td></tr>
    <?php } ?>
</table>
</body>
</html>
<?php
$conn->close();
?>
