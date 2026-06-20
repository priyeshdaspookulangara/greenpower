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

                    // 1. Company Revenue
                    $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'company_revenue', 'Sale of Solar Panel (Good CIBIL)')");
                    $stmt->execute([$order_id, $user['id'], $item['price']]);

                    // 2. Level Income Distribution (5000 from Subsidy)
                    if ($user['referrer_id']) {
                        $level_amount = 5000;
                        $stmt = $pdo->prepare("INSERT INTO level_income (user_id, source_order_id, amount, level, status) VALUES (?, ?, ?, 1, 'paid')");
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
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Transaction failed: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 900px;">
    <div class="glass-card">
        <h2 class="neon-text" style="margin-bottom: 1.5rem;">Order Confirmation</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></p>
                <p><strong>CIBIL Status:</strong> <span class="neon-text" style="font-weight: bold;"><?php echo strtoupper($user['cibil_status'] ?? 'N/A'); ?></span></p>
            </div>
            <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; border-left: 4px solid var(--neon-blue);">
                <h4 style="margin-bottom: 10px;">Processing Logic:</h4>
                <?php if (($user['cibil_status'] ?? '') === 'good'): ?>
                    <p style="font-size: 0.9rem;">✅ <strong>Profile A:</strong> Direct Subsidy Active.</p>
                    <p style="font-size: 0.8rem; color: #aaa;">Commissions and Level Income will be distributed as per system rules.</p>
                <?php else: ?>
                    <p style="font-size: 0.9rem;">⚠️ <strong>Profile B:</strong> Third-Party Routing.</p>
                    <p style="font-size: 0.8rem; color: #aaa;">Transaction will be processed through the alternative subsidy channel.</p>
                <?php endif; ?>
            </div>
        </div>

        <table style="margin-bottom: 2rem;">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="text-align: right;"><?php echo formatPrice($item['price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th style="font-size: 1.2rem;">Total Amount</th>
                    <th class="neon-text" style="text-align: right; font-size: 1.8rem;"><?php echo formatPrice($total); ?></th>
                </tr>
            </tfoot>
        </table>

        <?php if (isset($error)): ?>
            <p style="color: #ff4d4d; margin-bottom: 20px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 18px; font-size: 1.3rem; letter-spacing: 1px;">Confirm and Pay</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
