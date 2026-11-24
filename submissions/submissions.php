<?php
session_start();
$conn = new mysqli("localhost", "root", "", "newsdesk_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$sql = "SELECT s.*, u.name AS author_name, c.name AS category_name 
        FROM article_submissions s 
        JOIN users u ON s.user_id = u.id 
        JOIN categories c ON s.category_id = c.id 
        WHERE s.status = 'pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Submissions Queue</title>
</head>
<body>
    <h2>Pending Article Submissions</h2>

    <table border="1" cellpadding="5">
        <tr>
            <th>Author</th>
            <th>Title</th>
            <th>Category</th>
            <th>Action</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['author_name'] ?></td>
                <td><?= $row['title'] ?></td>
                <td><?= $row['category_name'] ?></td>
                <td><a href="review_submission.php?id=<?= $row['id'] ?>">Review</a></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No pending submissions.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
<?php $conn->close(); ?>