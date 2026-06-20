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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - Solar Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav>
            <div class="logo"><h2 class="neon-text">SOLAR SHOP</h2></div>
            <div class="links">
                <a href="index.php">Home</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>

        <div class="glass-card">
            <h2>Your Shopping Cart</h2>
            <?php if (empty($cart_items)): ?>
                <p>Your cart is empty. <a href="index.php" style="color: var(--neon-blue);">Go shopping</a></p>
            <?php else: ?>
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
                            <td><a href="cart.php?remove=<?php echo $index; ?>" style="color: #ff4d4d;">Remove</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total</th>
                            <th colspan="2" class="neon-text" style="font-size: 1.5em;"><?php echo formatPrice($total); ?></th>
                        </tr>
                    </tfoot>
                </table>
                <div style="margin-top: 20px; text-align: right;">
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
