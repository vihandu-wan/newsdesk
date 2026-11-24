<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

date_default_timezone_set('Asia/Colombo');

if (is_logged_in()) {
    $stmt = $pdo->prepare('SELECT name, role, is_active FROM users WHERE id = ?');
    $stmt->execute([current_user_id()]);
    $sessionUser = $stmt->fetch();

    if (!$sessionUser || !$sessionUser['is_active']) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('error', 'Your account is not active. Please contact the NewsDesk admin.');
    } else {
        $_SESSION['name'] = $sessionUser['name'];
        $_SESSION['role'] = $sessionUser['role'];
    }
}
