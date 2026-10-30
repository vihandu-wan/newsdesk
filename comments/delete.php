<?php
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

