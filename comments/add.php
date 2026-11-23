<?php
require_once __DIR__ . '/../includes/init.php';

const NEW_COMMENT_STATUS = 'approved';
const MAX_COMMENT_LENGTH = 1000;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
require_login();
verify_csrf();

$articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
$content   = trim($_POST['content'] ?? '');

if (!$articleId) {
    redirect('index.php');
}
$back = 'articles/view.php?id=' . $articleId . '#comments';

if ($content === '') {
    flash('error', 'Comment cannot be empty.');
    redirect($back);
}
if (mb_strlen($content) > MAX_COMMENT_LENGTH) {
    flash('error', 'Comment is too long (max ' . MAX_COMMENT_LENGTH . ' characters).');
    redirect($back);
}

$check = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published'");
$check->execute([$articleId]);
if (!$check->fetch()) {
    flash('error', 'Article not found.');
    redirect('index.php');
}

$pdo->prepare('INSERT INTO comments (article_id, user_id, content, status) VALUES (?, ?, ?, ?)')
    ->execute([$articleId, current_user_id(), $content, NEW_COMMENT_STATUS]);

flash('success', NEW_COMMENT_STATUS === 'approved'
    ? 'Your comment has been posted.'
    : 'Your comment is waiting for moderator approval.');
redirect($back);
