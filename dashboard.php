<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getLoggedInUser($pdo);

// Fetch User Orders
$stmt = $pdo->prepare("SELECT o.*, p.name as product_name, p.id as product_id, p.category FROM orders o JOIN products p ON o.product_id = p.id WHERE o.user_id = ? ORDER BY o.created_at DESC");
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

$referral_link = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/register.php?ref=" . ($user['email'] ?? '');

include 'includes/header.php';
?>

<div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        <div class="glass-card">
            <h3 class="neon-text" style="margin-bottom: 1rem;">Profile Info</h3>
            <p style="font-size: 1.2rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></p>
            <p>Status: <span class="neon-text" style="font-weight: bold;"><?php echo strtoupper($user['cibil_status'] ?? 'N/A'); ?></span></p>
            <div style="margin-top: 1.5rem; padding: 10px; background: rgba(0,0,0,0.3); border-radius: 5px;">
                <p style="font-size: 0.8rem; color: #aaa; margin-bottom: 5px;">Your Unique Referral Link:</p>
                <code style="word-break: break-all; color: var(--neon-blue); font-size: 0.85rem;"><?php echo $referral_link; ?></code>
            </div>
        </div>

        <div class="glass-card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
            <h3 style="margin-bottom: 1.5rem;">Total Earnings</h3>
            <div style="display: flex; gap: 2rem;">
                <div>
                    <p style="font-size: 0.8rem; color: #aaa;">Level Income</p>
                    <p class="neon-text" style="font-size: 1.5rem; font-weight: bold;"><?php echo formatPrice($level_income_total); ?></p>
                </div>
                <div style="border-left: 1px solid var(--glass-border); padding-left: 2rem;">
                    <p style="font-size: 0.8rem; color: #aaa;">Referral Bonus</p>
                    <p class="neon-text" style="font-size: 1.5rem; font-weight: bold;"><?php echo formatPrice($referral_income_total); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <h3 class="neon-text" style="margin-bottom: 1.5rem;">Purchase History & Technical Specs</h3>
        <?php if (empty($orders)): ?>
            <p style="color: #aaa;">No orders found. Explore our <a href="index.php" style="color: var(--neon-blue);">products</a>.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product Details</th>
                            <th>Amount</th>
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
                                <strong style="color: #fff;"><?php echo htmlspecialchars($order['product_name']); ?></strong><br>
                                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                    <?php foreach ($props as $pr): ?>
                                        <span style="font-size: 0.7rem; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 3px;">
                                            <?php echo htmlspecialchars($pr['property_name'].": ".$pr['property_value']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td><?php echo formatPrice($order['total_price']); ?></td>
                            <td style="font-size: 0.8rem; color: #aaa;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><span style="background: #2e7d32; color: #fff; padding: 3px 8px; border-radius: 20px; font-size: 0.7rem;"><?php echo strtoupper($order['order_status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
