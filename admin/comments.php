<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
 }
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Access denied.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
 }

function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
 }

$allowedStatuses = ['pending', 'approved', 'rejected', 'hidden'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Invalid request.');
    }

     $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    $newStatus = $_POST['new_status'] ?? '';

    if ($commentId && in_array($newStatus, $allowedStatuses, true)) {
         $update = $pdo->prepare("UPDATE comments SET status = :status WHERE id = :id");
        $update->execute([':status' => $newStatus, ':id' => $commentId]);
        $message = 'Comment #' . $commentId . ' marked as ' . $newStatus . '.';
    }
}

if (!empty($_SESSION['comment_flash'])) {
    $message = $_SESSION['comment_flash']['text'];
    unset($_SESSION['comment_flash']);
  }

$filter = $_GET['status'] ?? 'pending';
if ($filter !== 'all' && !in_array($filter, $allowedStatuses, true)) {
    $filter = 'pending';
}

$sql = "SELECT c.id, c.content, c.status, c.commented_at,
               u.name AS user_name, u.email AS user_email,
               a.id AS article_id, a.title AS article_title
        FROM comments c
        JOIN users u    ON u.id = c.user_id
        JOIN articles a ON a.id = c.article_id";

$params = [];
if ($filter !== 'all') {
    $sql .= " WHERE c.status = :status";
    $params[':status'] = $filter;
}
$sql .= " ORDER BY c.commented_at DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comments = $stmt->fetchAll();
?>
