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
