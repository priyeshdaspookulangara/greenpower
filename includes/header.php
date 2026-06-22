<?php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolarShop - Sustainable Energy Solutions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="index.php" class="logo-text neon-text">SolarShop</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="cart.php">Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a></li>
                    <li><a href="logout.php" class="btn btn-outline" style="padding: 8px 20px;">Logout</a></li>
                <?php else: ?>
                    <li><a href="burfee_login.php" class="neon-text">Burfee Login</a></li>
                    <li><a href="register.php">Sign Up</a></li>
                    <li><a href="login.php" class="btn btn-primary">Sign In</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
