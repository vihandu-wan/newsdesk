<?php
session_start();
$conn = new mysqli("localhost", "root", "", "newsdesk_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id']) OR $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM article_submissions WHERE id = '$id'");
$submission = $result->fetch_assoc();

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $note = $_POST['review_note'];
    $admin_id = $_SESSION['user_id'];

    if ($action == 'approve') {

        $conn->query("UPDATE article_submissions SET status='approved', reviewer_id='$admin_id', review_note='$note' WHERE id='$id'");
        

        $uid = $submission['user_id'];
        $cid = $submission['category_id'];
        $title = $submission['title'];
        $content = $submission['content'];
        $conn->query("INSERT INTO articles (user_id, category_id, title, content, status) VALUES ('$uid', '$cid', '$title', '$content', 'published')");

    } else {
      
        $conn->query("UPDATE article_submissions SET status='rejected', reviewer_id='$admin_id', review_note='$note' WHERE id='$id'");
    }

    header("Location: submissions.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Submission</title>
</head>
<body>
    <h2>Review Submission</h2>
    <p><b>Title:</b> <?= $submission['title'] ?></p>
    <p><b>Content:</b><br><?= nl2br($submission['content']) ?></p>

    <form method="POST" action="">
        <label>Review Note:</label><br>
        <textarea name="review_note" rows="4" cols="40"></textarea><br><br>

        <button type="submit" name="action" value="approve">Approve & Publish</button>
        <button type="submit" name="action" value="reject">Reject</button>
    </form>
</body>
</html>
<?php $conn->close(); ?>