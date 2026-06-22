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
    <h1 class="section-title">My Dashboard</h1>

    <div class="stats-grid">
        <div class="glass-card stat-box">
            <div class="label">User Profile</div>
            <div class="value" style="font-size: 1.8rem;"><?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></div>
            <p style="color: var(--neon-blue); font-weight: 700; margin-top: 10px;">CIBIL: <?php echo strtoupper($user['cibil_status'] ?? 'N/A'); ?></p>
        </div>
        <div class="glass-card stat-box">
            <div class="label">Level Income</div>
            <div class="value"><?php echo formatPrice($level_income_total); ?></div>
        </div>
        <div class="glass-card stat-box">
            <div class="label">Referral Bonus</div>
            <div class="value"><?php echo formatPrice($referral_income_total); ?></div>
        </div>
    </div>

    <div class="glass-card" style="margin-bottom: 2rem;">
        <div class="label" style="margin-bottom: 10px;">Unique Referral Link</div>
        <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; border: 1px dashed var(--neon-green);">
            <code style="color: var(--neon-green); font-size: 1.1rem;"><?php echo $referral_link; ?></code>
        </div>
    </div>

    <div class="glass-card">
        <h3 style="margin-bottom: 1.5rem; color: var(--neon-blue);">Purchase History & Technical Specs</h3>
        <?php if (empty($orders)): ?>
            <p style="color: var(--text-dim);">No orders found. Explore our <a href="index.php" style="color: var(--neon-green);">products</a>.</p>
        <?php else: ?>
            <div class="table-container">
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
                            <td style="color: var(--neon-blue);">#<?php echo $order['id']; ?></td>
                            <td>
                                <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($order['product_name']); ?></strong><br>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                                    <?php foreach ($props as $pr): ?>
                                        <span class="badge" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border); font-size: 0.65rem;">
                                            <?php echo htmlspecialchars($pr['property_name'].": ".$pr['property_value']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="neon-text"><?php echo formatPrice($order['total_price']); ?></td>
                            <td style="color: var(--text-dim);"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><span class="badge badge-success"><?php echo strtoupper($order['order_status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
