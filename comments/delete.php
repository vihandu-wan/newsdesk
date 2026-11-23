<?php
<<<<<<< HEAD
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
require_login();
verify_csrf();

$commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
$fromAdmin = ($_POST['from'] ?? '') === 'admin';

$stmt = $pdo->prepare('SELECT id, user_id, article_id FROM comments WHERE id = ?');
$stmt->execute([$commentId ?: 0]);
$comment = $stmt->fetch();

if (!$comment) {
    flash('error', 'Comment not found.');
    redirect($fromAdmin ? 'admin/comments.php' : 'index.php');
}

if ((int) $comment['user_id'] !== current_user_id() && !is_admin()) {
    http_response_code(403);
    die('You are not allowed to delete this comment.');
}

$pdo->prepare('DELETE FROM comments WHERE id = ?')->execute([$comment['id']]);
flash('success', 'Comment deleted.');

if ($fromAdmin && is_admin()) {
    redirect('admin/comments.php');
}
redirect('articles/view.php?id=' . (int) $comment['article_id'] . '#comments');
=======
session_start();
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    die('Invalid request.');
}

$userId    = (int) $_SESSION['user_id'];
$isAdmin   = ($_SESSION['role'] ?? '') === 'admin';
$commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
$fromAdmin = ($_POST['from'] ?? '') === 'admin';

if (!$commentId) {
    header('Location: ../index.php');
    exit;

}

try {
    $stmt = $pdo->prepare("SELECT id, user_id, article_id FROM comments WHERE id = :id");
    $stmt->execute([':id' => $commentId]);
    $comment = $stmt->fetch();

    if (!$comment) {
        $_SESSION['comment_flash'] = ['type' => 'error', 'text' => 'Comment not found.'];
        header('Location: ../index.php');
        exit;
     }

    $isOwner = ((int) $comment['user_id'] === $userId);

    if (!$isOwner && !$isAdmin) {
        http_response_code(403);
        die('You are not allowed to delete this comment.');
        }

    $delete = $pdo->prepare("DELETE FROM comments WHERE id = :id");
    $delete->execute([':id' => $commentId]);

    $_SESSION['comment_flash'] = ['type' => 'success', 'text' => 'Comment deleted.'];

   }

   catch (PDOException $e) {
    $_SESSION['comment_flash'] = ['type' => 'error', 'text' => 'Could not delete the comment.'];
}

if ($fromAdmin && $isAdmin) {
    header('Location: ../admin/comments.php');
  } else
   {
    header('Location: ../articles/view.php?id=' . (int) $comment['article_id'] . '#comments');
}
exit;


>>>>>>> origin/newsdesk-admin-panel
