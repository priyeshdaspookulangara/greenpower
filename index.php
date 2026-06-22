<?php
require_once 'includes/functions.php';

// Simple cart logic
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $product_id;
    redirect('cart.php');
}

$products = $pdo->query("SELECT * FROM products")->fetchAll();
include 'includes/header.php';
?>

<section class="hero" style="height: 80vh; display: flex; align-items: center; justify-content: center; text-align: center; background: radial-gradient(circle at center, #1a222d 0%, #0a0e14 100%);">
    <div class="hero-content">
        <h1 style="font-size: 4rem; margin-bottom: 1.5rem;" class="neon-text">Solar Energy, <br>Reimagined.</h1>
        <p style="font-size: 1.2rem; color: var(--text-dim); max-width: 600px; margin: 0 auto 2rem;">High-performance solar panels and battery storage with an automated financial ecosystem.</p>
        <a href="#products" class="btn btn-primary">Explore Products</a>
    </div>
</section>

<div class="container" id="products">
    <h2 class="section-title">Our Solar Collection</h2>

    <div class="product-grid">
        <?php foreach ($products as $p):
            $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ? LIMIT 3");
            $stmt->execute([$p['id']]);
            $props = $stmt->fetchAll();
        ?>
            <div class="glass-card product-card">
                <span class="badge badge-info" style="margin-bottom: 1rem; display: inline-block;">
                    <?php echo htmlspecialchars($p['category'] == 'solar_panel' ? 'Solar Panel' : 'Battery'); ?>
                </span>
                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                <div class="price neon-text" style="font-size: 1.8rem; font-weight: 800; margin: 1rem 0;">
                    <?php echo formatPrice($p['price']); ?>
                </div>

                <ul class="property-list">
                    <?php foreach ($props as $prop): ?>
                        <li>
                            <span><?php echo htmlspecialchars($prop['property_name']); ?></span>
                            <strong><?php echo htmlspecialchars($prop['property_value']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-outline" style="text-align: center; padding: 10px 5px; font-size: 0.8rem;">Details</a>
                    <a href="index.php?add=<?php echo $p['id']; ?>" class="btn btn-primary" style="text-align: center; padding: 10px 5px; font-size: 0.8rem;">Add to Cart</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<section style="background: rgba(255,255,255,0.02); padding: 80px 0; margin-top: 100px;">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-box">
                <div class="value">5000+</div>
                <div class="label">Homes Powered</div>
            </div>
            <div class="stat-box">
                <div class="value">₹10B+</div>
                <div class="label">Subsidies Processed</div>
            </div>
            <div class="stat-box">
                <div class="value">99%</div>
                <div class="label">Client Satisfaction</div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
