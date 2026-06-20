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

<div class="container" style="margin-top: 150px; margin-bottom: 80px;">
    <div class="card">
        <h2 style="margin-bottom: 2rem; color: var(--brand-blue); font-weight: 900;">Your Shopping Cart</h2>
        <?php if (empty($cart_items)): ?>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Your cart is empty. <a href="index.php" style="color: var(--brand-green); font-weight: 700; text-decoration: none;">Go shopping</a></p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $index => $item): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><span style="font-size: 0.75rem; color: var(--text-muted); background: var(--light-bg); padding: 4px 10px; border-radius: 4px; border: 1px solid var(--border-color);"><?php echo strtoupper(str_replace('_', ' ', $item['category'])); ?></span></td>
                            <td style="font-weight: 700; color: var(--brand-blue);"><?php echo formatPrice($item['price']); ?></td>
                            <td style="text-align: right;"><a href="cart.php?remove=<?php echo $index; ?>" style="color: #e74c3c; text-decoration: none; font-weight: 600; font-size: 0.9rem;"><i class="fas fa-trash"></i> Remove</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="font-size: 1.2rem; background: var(--white); border-top: 2px solid var(--border-color);">Total Amount</th>
                            <th colspan="2" style="font-size: 2rem; background: var(--white); border-top: 2px solid var(--border-color); color: var(--brand-green); font-weight: 900; text-align: right;"><?php echo formatPrice($total); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="margin-top: 40px; text-align: right;">
                <a href="checkout.php" class="btn btn-primary" style="padding: 18px 50px; font-size: 1rem; font-weight: 800; border-radius: 4px;">PROCEED TO CHECKOUT</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
