<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('index.php');
}

$user = getLoggedInUser($pdo);
$cart_items = [];
$total = 0;
foreach ($_SESSION['cart'] as $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p) {
        $cart_items[] = $p;
        $total += $p['price'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        foreach ($cart_items as $item) {
            // Create Order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, total_price, cibil_status_at_purchase, order_status) VALUES (?, ?, ?, ?, 'completed')");
            $stmt->execute([$user['id'], $item['id'], $item['price'], $user['cibil_status']]);
            $order_id = $pdo->lastInsertId();

            if ($item['category'] === 'solar_panel') {
                if ($user['cibil_status'] === 'good') {
                    // Profile A Logic

                    // 1. Company Revenue (Representing the manufacturer commission + subsidy incoming)
                    // We'll record the full transaction but then distribute
                    $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'company_revenue', 'Sale of Solar Panel (Good CIBIL)')");
                    $stmt->execute([$order_id, $user['id'], $item['price']]);

                    // 2. Level Income Distribution (5000 from Subsidy)
                    // Find uplines. For simplicity, we handle one level up (referrer).
                    if ($user['referrer_id']) {
                        $level_amount = 5000;
                        $stmt = $pdo->prepare("INSERT INTO level_income (user_id, source_order_id, amount, level, status) VALUES (?, ?, ?, 1, 'credited')");
                        $stmt->execute([$user['referrer_id'], $order_id, $level_amount]);

                        $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'subsidy_payout', 'Level Income payout from subsidy')");
                        $stmt->execute([$order_id, $user['referrer_id'], $level_amount]);
                    }

                    // 3. Referral Income (4500 from Manufacturer Commission)
                    if ($user['referrer_id']) {
                        $referral_bonus = 4500;
                        $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'referral_commission', 'Immediate Referral Bonus from manufacturer commission')");
                        $stmt->execute([$order_id, $user['referrer_id'], $referral_bonus]);
                    }

                } else {
                    // Profile B Logic
                    $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'third_party_subsidy', 'Processed via Third-Party Subsidy mechanism (Low CIBIL)')");
                    $stmt->execute([$order_id, $user['id'], $item['price']]);
                }
            } else {
                // Regular Battery Sale
                $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'company_revenue', 'Sale of Battery')");
                $stmt->execute([$order_id, $user['id'], $item['price']]);
            }
        }

        $pdo->commit();
        unset($_SESSION['cart']);
        redirect('dashboard.php?success=1');
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Transaction failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Solar Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        <nav>
            <div class="logo"><h2 class="neon-text">SOLAR SHOP</h2></div>
            <div class="links"><a href="cart.php">Back to Cart</a></div>
        </nav>

        <div class="glass-card">
            <h2>Order Summary</h2>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>CIBIL Status:</strong> <span class="neon-text"><?php echo strtoupper($user['cibil_status']); ?></span></p>

            <div style="margin: 20px 0; border: 1px solid var(--glass-border); padding: 15px; border-radius: 10px;">
                <h4>Financial Breakdown Logic:</h4>
                <?php if ($user['cibil_status'] === 'good'): ?>
                    <p>✅ <strong>Profile A:</strong> You qualify for Government Subsidies.</p>
                    <ul>
                        <li>Manufacturer Commission will be processed.</li>
                        <li>₹5,000 Level Income will be distributed to your network from subsidy.</li>
                        <li>₹4,500 Referral Income will be credited to your referrer.</li>
                    </ul>
                <?php else: ?>
                    <p>⚠️ <strong>Profile B:</strong> Standard Direct Subsidy not available.</p>
                    <ul>
                        <li>Order will be routed through <strong>Third-Party Subsidy</strong> mechanism.</li>
                    </ul>
                <?php endif; ?>
            </div>

            <table>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo formatPrice($item['price']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th>Total to Pay</th>
                    <th class="neon-text"><?php echo formatPrice($total); ?></th>
                </tr>
            </table>

            <form method="POST" style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.2em;">Confirm and Pay</button>
            </form>
        </div>
    </div>
</body>
</html>
