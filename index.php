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
include 'includes/header.php';
?>

<section class="hero" style="background-image: linear-gradient(rgba(10, 25, 41, 0.6), rgba(10, 25, 41, 0.8)), url('assets/images/solar-hero.jpg');">
    <div class="hero-content">
        <h1 class="neon-text">Solar Panels &<br>Batteries for Ambitious Homes.</h1>
        <p>Engineering excellence for a sustainable future.</p>
    </div>
</section>

<div class="container">
    <h2 class="neon-text" style="font-size: 2.5rem; margin-bottom: 2rem;">Our Products</h2>

    <div class="product-grid">
        <?php foreach ($products as $p):
            $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ?");
            $stmt->execute([$p['id']]);
            $props = $stmt->fetchAll();
        ?>
            <div class="glass-card product-card">
                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                <p class="category"><?php echo strtoupper(str_replace('_', ' ', $p['category'])); ?></p>
                <h4 class="neon-text" style="font-size: 1.5rem;"><?php echo formatPrice($p['price']); ?></h4>

                <ul class="property-list">
                    <?php foreach ($props as $prop): ?>
                        <li><strong><?php echo htmlspecialchars($prop['property_name']); ?>:</strong> <?php echo htmlspecialchars($prop['property_value']); ?></li>
                    <?php endforeach; ?>
                </ul>

                <a href="index.php?add=<?php echo $p['id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center;">Add to Cart</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
