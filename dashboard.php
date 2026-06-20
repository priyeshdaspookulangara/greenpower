<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getLoggedInUser($pdo);

// Fetch User Orders
$stmt = $pdo->prepare("SELECT o.*, p.name as product_name, p.category FROM orders o JOIN products p ON o.product_id = p.id WHERE o.user_id = ? ORDER BY o.created_at DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

// Fetch Level Income Earned
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM level_income WHERE user_id = ?");
$stmt->execute([$user['id']]);
$level_income_total = $stmt->fetch()['total'] ?? 0;

// Fetch Referral Income (from transactions)
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'referral_commission'");
$stmt->execute([$user['id']]);
$referral_income_total = $stmt->fetch()['total'] ?? 0;

$referral_link = "http://" . $_SERVER['HTTP_HOST'] . "/solar/register.php?ref=" . $user['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard - Solar Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav>
            <div class="logo"><h2 class="neon-text">SOLAR SHOP</h2></div>
            <div class="links">
                <a href="index.php">Catalog</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div class="glass-card">
                <h3>Welcome, <?php echo htmlspecialchars($user['name']); ?></h3>
                <p>CIBIL Status: <span class="neon-text"><?php echo strtoupper($user['cibil_status']); ?></span></p>
                <p>Your Referral Link: <br>
                   <code style="background: #000; padding: 5px; display: block; margin-top: 5px;"><?php echo $referral_link; ?></code>
                </p>
            </div>
            <div class="glass-card">
                <h3>Earnings Summary</h3>
                <p>Total Level Income: <span class="neon-text"><?php echo formatPrice($level_income_total); ?></span></p>
                <p>Total Referral Commission: <span class="neon-text"><?php echo formatPrice($referral_income_total); ?></span></p>
            </div>
        </div>

        <div class="glass-card">
            <h3>My Orders</h3>
            <?php if (empty($orders)): ?>
                <p>No orders yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order):
                            $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ?");
                            $stmt->execute([$order['product_id']]);
                            $props = $stmt->fetchAll();
                        ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($order['product_name']); ?></strong><br>
                                <small>
                                    <?php foreach ($props as $pr) echo htmlspecialchars($pr['property_name'].": ".$pr['property_value']). " | "; ?>
                                </small>
                            </td>
                            <td><?php echo formatPrice($order['total_price']); ?></td>
                            <td><?php echo $order['created_at']; ?></td>
                            <td><?php echo strtoupper($order['order_status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
