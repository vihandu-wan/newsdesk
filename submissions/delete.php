<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('submissions/my_submissions.php');
}
verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$load = $pdo->prepare("SELECT image_url FROM article_submissions WHERE id = ? AND author_id = ? AND status <> 'approved'");
$load->execute([$id, current_user_id()]);
$row = $load->fetch();

if ($row) {
    $pdo->prepare('DELETE FROM article_submissions WHERE id = ?')->execute([$id]);
    delete_upload($row['image_url']);
    flash('success', 'Submission withdrawn.');
} else {
    flash('error', 'That submission cannot be deleted.');
}
redirect('submissions/my_submissions.php');
