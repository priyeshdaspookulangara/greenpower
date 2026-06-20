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
            // Check and update stock
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - 1 WHERE id = ? AND stock > 0");
            $stmt->execute([$item['id']]);
            if ($stmt->rowCount() === 0) {
                throw new Exception("Product " . $item['name'] . " is out of stock.");
            }

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

<div class="container" style="max-width: 900px; padding-top: 40px; padding-bottom: 80px;">
    <div class="card" style="padding: 40px;">
        <h2 style="margin-bottom: 1.5rem; color: var(--brand-blue);">Order Confirmation</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <p style="margin-bottom: 10px;"><strong>Customer:</strong> <?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></p>
                <p><strong>CIBIL Status:</strong> <span style="font-weight: bold; color: var(--brand-green);"><?php echo strtoupper($user['cibil_status'] ?? 'N/A'); ?></span></p>
            </div>
            <div style="background: var(--light-bg); padding: 20px; border-radius: 8px; border-left: 4px solid var(--brand-green);">
                <h4 style="margin-bottom: 10px; color: var(--brand-blue);">Financial Processing Logic:</h4>
                <?php if (($user['cibil_status'] ?? '') === 'good'): ?>
                    <p style="font-size: 0.95rem; margin-bottom: 5px;">✅ <strong>Profile A:</strong> Direct Government Subsidy</p>
                    <p style="font-size: 0.85rem; color: #666; line-height: 1.4;">Subsidy-based Level Income and Manufacturer Commission bonuses will be automatically distributed.</p>
                <?php else: ?>
                    <p style="font-size: 0.95rem; margin-bottom: 5px;">⚠️ <strong>Profile B:</strong> Third-Party Routing</p>
                    <p style="font-size: 0.85rem; color: #666; line-height: 1.4;">Your order will be processed through our designated third-party subsidy channel for approval.</p>
                <?php endif; ?>
            </div>
        </div>

        <table class="table" style="margin-bottom: 2rem; width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #eee;">
                    <th style="padding: 15px; text-align: left;">Item Description</th>
                    <th style="padding: 15px; text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="padding: 15px; text-align: right;"><?php echo formatPrice($item['price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th style="padding: 20px 15px; font-size: 1.2rem; text-align: left;">Total Payable Amount</th>
                    <th style="padding: 20px 15px; text-align: right; font-size: 1.8rem; color: var(--brand-green);"><?php echo formatPrice($total); ?></th>
                </tr>
            </tfoot>
        </table>

        <?php if (isset($error)): ?>
            <div style="background: #fff5f5; color: #e53e3e; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #feb2b2;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 20px; font-size: 1.3rem; border-radius: 10px;">Complete Transaction</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
