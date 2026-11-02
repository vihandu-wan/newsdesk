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
