
<?php
$host = 'localhost';
$dbname = 'newsdesk_db';
$username = 'root';
$password = 'Seven';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
