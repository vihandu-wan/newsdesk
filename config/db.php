
<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "newsdesk_db";

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=newsdesk_db;charset=utf8mb4",
        "root",
        ""
    );

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
