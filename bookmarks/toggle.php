<?php
require_once __DIR__ . '/../includes/init.php';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_logged_in()) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false]);
        exit;
    }
    require_login();
    redirect('bookmarks/index.php');
}
verify_csrf();

$userId    = current_user_id();
$articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT) ?: 0;

$exists = $pdo->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND article_id = ?');
$exists->execute([$userId, $articleId]);

if ($exists->fetch()) {
    $pdo->prepare('DELETE FROM bookmarks WHERE user_id = ? AND article_id = ?')->execute([$userId, $articleId]);
    $saved = false;
} else {
    $check = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published'");
    $check->execute([$articleId]);
    if (!$check->fetch()) {
        flash('error', 'Article not found.');
        redirect('index.php');
    }
    $pdo->prepare('INSERT INTO bookmarks (user_id, article_id) VALUES (?, ?)')->execute([$userId, $articleId]);
    $saved = true;
}

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'saved' => $saved]);
    exit;
}

flash('success', $saved ? 'Article saved.' : 'Article removed from saved list.');
$back = $_POST['back'] ?? '';
redirect($back === 'list' ? 'bookmarks/index.php' : 'articles/view.php?id=' . $articleId);
