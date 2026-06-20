<?php
require_once 'includes/functions.php';

if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // re-index
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
    <div class="glass-card">
        <h2 class="neon-text" style="margin-bottom: 1.5rem;">Your Shopping Cart</h2>
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty. <a href="index.php" style="color: var(--neon-blue);">Go shopping</a></p>
        <?php else: ?>
            <div style="overflow-x: auto;">
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
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo strtoupper(str_replace('_', ' ', $item['category'])); ?></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><a href="cart.php?remove=<?php echo $index; ?>" style="color: #ff4d4d; text-decoration: none;">Remove</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="font-size: 1.2rem;">Total</th>
                            <th colspan="2" class="neon-text" style="font-size: 1.8rem;"><?php echo formatPrice($total); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="margin-top: 30px; text-align: right;">
                <a href="checkout.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 1.1rem;">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
