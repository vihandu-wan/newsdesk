<?php
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function timeAgo($date)
{
    if (!$date) {
        return '';
    }
    $seconds = time() - strtotime($date);
    if ($seconds < 60) {
        return 'just now';
    }
    if ($seconds < 3600) {
        return floor($seconds / 60) . ' min ago';
    }
    if ($seconds < 86400) {
        $h = floor($seconds / 3600);
        return $h . ' hour' . ($h > 1 ? 's' : '') . ' ago';
    }
    if ($seconds < 604800) {
        $d = floor($seconds / 86400);
        return $d . ' day' . ($d > 1 ? 's' : '') . ' ago';
    }
    return date('M j, Y', strtotime($date));
}

function summary($text, $length = 110)
{
    return mb_strimwidth(strip_tags($text), 0, $length, '...');
}

function imageSrc($url)
{
    if (!$url) {
        return '';
    }
    if (preg_match('#^https?://#', $url)) {
        return $url;
    }
    return url($url);
}

function status_badge($status)
{
    return '<span class="badge badge-' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}

function old($key, $default = '')
{
    return e($_POST[$key] ?? $default);
}

function is_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function is_admin()
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

function current_user_id()
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function require_login()
{
    if (!is_logged_in()) {
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        flash('error', 'Please log in to continue.');
        redirect('auth/login.php');
    }
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        flash('error', 'That page is for administrators only.');
        redirect('index.php');
    }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('This form has expired. Please go back, refresh the page and try again.');
    }
}

function flash($type, $text)
{
    $_SESSION['flash'][] = ['type' => $type, 'text' => $text];
}

function get_flashes()
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function get_categories(PDO $pdo)
{
    static $cache = null;
    if ($cache === null) {
        $cache = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    }
    return $cache;
}

function upload_image($field, &$error)
{
    $error = '';
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed. Please try again.';
        return null;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        $error = 'Image must be 2 MB or smaller.';
        return null;
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        $error = 'Only JPG, PNG, WEBP or GIF images are allowed.';
        return null;
    }
    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        $error = 'Could not save the uploaded image.';
        return null;
    }
    return 'uploads/' . $name;
}

function delete_upload($path)
{
    if ($path && strpos($path, 'uploads/') === 0) {
        $full = __DIR__ . '/../uploads/' . basename($path);
        if (is_file($full)) {
            unlink($full);
        }
    }
}
