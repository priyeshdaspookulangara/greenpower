<?php
// 🔧 DEBUG (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if we are in the sandbox environment or on user's server
$is_sandbox = (gethostname() === 'sandbox' || file_exists(__DIR__ . '/.sandbox_marker'));

if ($is_sandbox) {
    // For Jules' verification in sandbox
    $dsn = "sqlite:" . __DIR__ . "/solar_shop.sqlite";
    $user = null;
    $pass = null;
} else {
    // ✅ User's Production MySQL
    $host = 'localhost';
    $db   = 'jeoczvkk_zen';
    $user = 'jeoczvkk_jeoczvkk';
    $pass = 'pearl$Pearl$';
    $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($is_sandbox) {
        $pdo->exec("PRAGMA foreign_keys = ON;");
    }
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
