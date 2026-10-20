
<?php
$host = 'localhost';
$dbname = 'newsdesk_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    echo "Database connection successful!";
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
