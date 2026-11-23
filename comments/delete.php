<?php
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
