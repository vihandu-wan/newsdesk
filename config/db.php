
<?php

define('DB_HOST', 'sql205.infinityfree.com');
define('DB_NAME', 'if0_43530375_newsdesk');
define('DB_USER', 'if0_43530375');
define('DB_PASS', 'PASTE_YOUR_INFINITYFREE_MYSQL_PASSWORD');

define('BASE_URL', '');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Database connection failed. Please try again later.');
}
