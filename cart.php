<?php
require_once 'includes/functions.php';

if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    redirect('cart.php');
}

$cart_items = [];
$total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p) {
            $cart_items[] = $p;
            $total += $p['price'];
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 class="section-title">Shopping Cart</h1>

    <div class="glass-card">
        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="color: var(--text-dim); font-size: 1.2rem; margin-bottom: 2rem;">Your cart is currently empty.</p>
                <a href="index.php" class="btn btn-outline">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $index => $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td><span class="badge badge-info"><?php echo strtoupper(str_replace('_', ' ', $item['category'])); ?></span></td>
                            <td class="neon-text"><?php echo formatPrice($item['price']); ?></td>
                            <td><a href="cart.php?remove=<?php echo $index; ?>" style="color: #ff4d4d;"><i class="fas fa-trash"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(255,255,255,0.05);">
                            <td colspan="2" style="font-weight: 800; font-size: 1.2rem;">TOTAL</td>
                            <td colspan="2" class="neon-text" style="font-size: 2rem; font-weight: 900;"><?php echo formatPrice($total); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="margin-top: 3rem; display: flex; justify-content: space-between; align-items: center;">
                <a href="index.php" style="color: var(--text-dim); text-decoration: none;"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                <a href="checkout.php" class="btn btn-primary" style="padding: 16px 40px;">PROCEED TO CHECKOUT</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
