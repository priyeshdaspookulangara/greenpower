<?php
require_once 'includes/functions.php';

// Simple cart logic
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $product_id;
    redirect('index.php');
}

$products = $pdo->query("SELECT * FROM products")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Solar Shop - Catalog</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav>
            <div class="logo">
                <h2 class="neon-text">SOLAR SHOP</h2>
            </div>
            <div class="links">
                <a href="index.php">Home</a>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php">Admin</a>
                    <?php endif; ?>
                    <a href="cart.php">Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
                    <a href="logout.php">Logout (<?php echo $_SESSION['name']; ?>)</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>

        <section>
            <h1 class="neon-text">Our Products</h1>
            <div class="product-grid">
                <?php foreach ($products as $p):
                    $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ?");
                    $stmt->execute([$p['id']]);
                    $props = $stmt->fetchAll();
                ?>
                    <div class="glass-card product-card">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p class="category"><?php echo strtoupper(str_replace('_', ' ', $p['category'])); ?></p>
                        <h4 class="neon-text"><?php echo formatPrice($p['price']); ?></h4>

                        <ul class="property-list">
                            <?php foreach ($props as $prop): ?>
                                <li><strong><?php echo htmlspecialchars($prop['property_name']); ?>:</strong> <?php echo htmlspecialchars($prop['property_value']); ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <a href="index.php?add=<?php echo $p['id']; ?>" class="btn btn-primary">Add to Cart</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</body>
</html>
