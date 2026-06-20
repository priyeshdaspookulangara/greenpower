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

    $user_id = $_SESSION['user_id'];

    if (strpos($user_id, 'BURFEE_') === 0) {
        // It's a Burfee member
        $real_id = substr($user_id, 7);
        $stmt = $pdo->prepare("SELECT id, name, MemberId as email, 'good' as cibil_status, NULL as referrer_id, 'customer' as role FROM customer WHERE id = ?");
        $stmt->execute([$real_id]);
        return $stmt->fetch();
    } else {
        // Regular user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
}

function formatPrice($amount) {
    return '₹' . number_format($amount, 2);
}
?>
