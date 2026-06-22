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
    $payment_method = $_POST['payment_method'] ?? 'online';
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
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, total_price, payment_method, cibil_status_at_purchase, order_status) VALUES (?, ?, ?, ?, ?, 'completed')");
            $stmt->execute([$user['id'], $item['id'], $item['price'], $payment_method, $user['cibil_status']]);
            $order_id = $pdo->lastInsertId();

            // Add Solar Package if successful
            $stmt = $pdo->prepare("INSERT INTO additional_packages (user_id, member_skyid, package_label, gross_amount) VALUES (?, ?, 'solar', ?)");
            $stmt->execute([$user['id'], $user['email'], 'solar', $item['price']]);

            if ($item['category'] === 'solar_panel') {
                if ($user['cibil_status'] === 'good') {
                    // Profile A Logic
                    // 1. Company receives full product price
                    $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'company_revenue', 'Sale of Solar Panel (Good CIBIL)')");
                    $stmt->execute([$order_id, $user['id'], $item['price']]);

                    // 2. Company receives Govt Subsidy (Implicit in logic, recorded as inflow for ledger clarity)
                    $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'company_revenue', 'Inflow: Government Subsidy Received')");
                    $stmt->execute([$order_id, $user['id'], 10000]); // Example inflow amount

                    // 3. Company receives Manufacturer Commission
                    $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'company_revenue', 'Inflow: Manufacturer Commission Received')");
                    $stmt->execute([$order_id, $user['id'], 8000]); // Example inflow amount

                    if ($user['referrer_id']) {
                        // 4. Extract 5000 for Level Income
                        $level_amount = 5000;
                        $stmt = $pdo->prepare("INSERT INTO level_income (user_id, source_order_id, amount, level, status) VALUES (?, ?, ?, 1, 'paid')");
                        $stmt->execute([$user['referrer_id'], $order_id, $level_amount]);

                        $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'subsidy_payout', 'Outflow: Level Income (Extracted from Subsidy)')");
                        $stmt->execute([$order_id, $user['referrer_id'], $level_amount]);

                        // 5. Extract 4500 for Referral Bonus
                        $referral_bonus = 4500;
                        $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'referral_commission', 'Outflow: Referral Bonus (Extracted from Commission)')");
                        $stmt->execute([$order_id, $user['referrer_id'], $referral_bonus]);
                    }
                } else {
                    // Profile B Logic
                    $stmt = $pdo->prepare("INSERT INTO transactions (order_id, user_id, amount, type, description) VALUES (?, ?, ?, 'third_party_subsidy', 'Processed via Third-Party Subsidy mechanism (Low CIBIL)')");
                    $stmt->execute([$order_id, $user['id'], $item['price']]);
                }
            } else {
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
    <h1 class="section-title">Finalize Order</h1>

    <div class="glass-card">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--glass-border);">
            <div>
                <p style="color: var(--text-dim); margin-bottom: 5px;">Customer</p>
                <h3 style="margin-bottom: 15px;"><?php echo htmlspecialchars($user['name']); ?></h3>
                <span class="badge <?php echo $user['cibil_status'] == 'good' ? 'badge-success' : 'badge-info'; ?>">
                    CIBIL: <?php echo strtoupper($user['cibil_status']); ?>
                </span>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--neon-green);">
                <h4 style="color: var(--neon-blue); margin-bottom: 10px;">Protocol Routing</h4>
                <?php if ($user['cibil_status'] === 'good'): ?>
                    <p style="font-size: 0.9rem; color: var(--text-dim);">Profile A triggered: Level Income and Referral Bonus distribution active.</p>
                <?php else: ?>
                    <p style="font-size: 0.9rem; color: var(--text-dim);">Profile B triggered: Third-Party Subsidy routing active.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td class="neon-text"><?php echo formatPrice($item['price']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="font-weight: 800; padding: 2rem;">Total Commitment</td>
                        <td class="neon-text" style="font-size: 2.2rem; font-weight: 900; padding: 2rem;"><?php echo formatPrice($total); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: rgba(255,0,0,0.1); border: 1px solid #ff4d4d; color: #ff4d4d; padding: 1rem; border-radius: 8px; margin: 2rem 0;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-top: 2rem;">
                <label style="display: block; margin-bottom: 1rem; font-weight: 700; color: var(--neon-blue);">SELECT PAYMENT METHOD</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <label class="glass-card" style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 1rem;">
                        <input type="radio" name="payment_method" value="online" checked style="width: auto; margin: 0;">
                        <span>Online Payment</span>
                    </label>
                    <label class="glass-card" style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 1rem;">
                        <input type="radio" name="payment_method" value="pod" style="width: auto; margin: 0;">
                        <span>Pay on Delivery</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 20px; font-size: 1.2rem; margin-top: 2rem;">AUTHORIZE & COMPLETE TRANSACTION</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
