<?php
session_start();

$conn = new mysqli("localhost", "root", "", "school");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO article_submissions (user_id, category_id, title, content, status) 
            VALUES ('$user_id', '$category_id', '$title', '$content', 'pending')";
    
    if ($conn->query($sql) === TRUE) {
        $success = "Article submitted successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Article</title>
</head>
<body>
    <h2>Submit an Article</h2>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" action="">
        <label>Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Category:</label><br>
        <select name="category_id" required>
            <option value="">Select Category</option>
            <?php while($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Content:</label><br>
        <textarea name="content" rows="6" cols="40" required></textarea><br><br>

        <input type="submit" name="submit" value="Submit Article">
    </form>
    <br>
    <a href="my_submissions.php">View My Submissions</a>
</body>
</html>
<?php $conn->close(); ?>

