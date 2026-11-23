<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/articles.php');
}
verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$load = $pdo->prepare('SELECT image_url FROM articles WHERE id = ?');
$load->execute([$id]);
$article = $load->fetch();

if (!$article) {
    flash('error', 'Article not found.');
} else {
    $pdo->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
    delete_upload($article['image_url']);
    flash('success', 'Article deleted.');
}
redirect('admin/articles.php');
