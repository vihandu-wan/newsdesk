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
