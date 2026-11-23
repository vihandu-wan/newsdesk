<?php
<<<<<<< HEAD
<<<<<<< HEAD
require_once __DIR__ . '/../includes/init.php';

const NEW_COMMENT_STATUS = 'approved';
const MAX_COMMENT_LENGTH = 1000;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
require_login();
verify_csrf();

=======
=======
>>>>>>> origin/UI-and-final
session_start();
require_once __DIR__ . '/../db.php';
const NEW_COMMENT_STATUS='approved';
const MAX_COMMENT_LENGTH = 1000;
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: ../index.php');
    exist;
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
<<<<<<< HEAD
>>>>>>> origin/newsdesk-admin-panel
=======
>>>>>>> origin/UI-and-final
$articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
$content   = trim($_POST['content'] ?? '');

if (!$articleId) {
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
=======
>>>>>>> origin/UI-and-final
    header('Location: ../index.php');
    exit;
}

$backUrl = '../articles/view.php?id=' . $articleId . '#comments';

if ($content === '') {
    $_SESSION['comment_flash'] = ['type' => 'error', 'text' => 'Comment cannot be empty.'];
    header('Location: ' . $backUrl);
    exit;
}

if (mb_strlen($content) > MAX_COMMENT_LENGTH) {
    $_SESSION['comment_flash'] = [
        'type' => 'error',
        'text' => 'Comment is too long (max ' . MAX_COMMENT_LENGTH . ' characters).'
    ];
    header('Location: ' . $backUrl);
    exit;
}

try {
    
    $check = $pdo->prepare("SELECT id FROM articles WHERE id = :id AND status = 'published'");
    $check->execute([':id' => $articleId]);

    if (!$check->fetch()) {
        $_SESSION['comment_flash'] = ['type' => 'error', 'text' => 'Article not found.'];
        header('Location: ../index.php');
        exit;
    }

    
    $insert = $pdo->prepare(
        "INSERT INTO comments (article_id, user_id, content, status)
         VALUES (:article_id, :user_id, :content, :status)"
    );
    $insert->execute([
        ':article_id' => $articleId,
        ':user_id'    => $userId,
        ':content'    => $content,
        ':status'     => NEW_COMMENT_STATUS,
    ]);

    if (NEW_COMMENT_STATUS === 'approved') {
        $message = 'Your comment has been posted.';
    } else {
        $message = 'Your comment was submitted and is waiting for moderator approval.';
    }
    $_SESSION['comment_flash'] = ['type' => 'success', 'text' => $message];

} catch (PDOException $e) {
    $_SESSION['comment_flash'] = ['type' => 'error', 'text' => 'Could not save your comment. Please try again.'];
}

header('Location: ' . $backUrl);
exit;
?>
<<<<<<< HEAD
>>>>>>> origin/newsdesk-admin-panel
=======
>>>>>>> origin/UI-and-final
