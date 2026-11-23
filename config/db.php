
<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'newsdesk_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', '/newsdesk');

try {

    // Connect to MySQL database
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

    // TEMPORARY DATABASE DIAGNOSTICS
    echo "<h2>NewsDesk Database Diagnostics</h2>";

    // Check database name
    echo "<p><strong>Connected database:</strong> ";
    echo htmlspecialchars($pdo->query("SELECT DATABASE()")->fetchColumn());
    echo "</p>";

    // Check MySQL port
    echo "<p><strong>MySQL port:</strong> ";
    echo htmlspecialchars((string) $pdo->query("SELECT @@port")->fetchColumn());
    echo "</p>";

    // Check articles table
    $table = $pdo->query("SHOW TABLES LIKE 'articles'")->fetchColumn();

    if ($table) {
        echo "<p><strong>Articles table:</strong> Found</p>";

        // Check is_pinned column
        $column = $pdo->query(
            "SHOW COLUMNS FROM articles LIKE 'is_pinned'"
        )->fetch();

        echo "<p><strong>is_pinned column:</strong> ";
        echo $column ? "Found" : "NOT FOUND";
        echo "</p>";

    } else {
        echo "<p><strong>Articles table:</strong> NOT FOUND</p>";
    }

    echo "<h3>Diagnostic completed.</h3>";

    // Stop execution temporarily
    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    die('Database connection or diagnostic failed: '
        . htmlspecialchars($e->getMessage()));
}
