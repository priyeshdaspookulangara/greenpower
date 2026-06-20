<?php
session_start();
require_once __DIR__ . '/../config/db.php';

function redirect($path) {
    header("Location: $path");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getLoggedInUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function formatPrice($amount) {
    return '₹' . number_format($amount, 2);
}
?>
