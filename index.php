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

<section class="hero">
    <div class="hero-content">
        <h1>Sustainable Energy for<br>Ambitious Homes.</h1>
        <p>Engineering excellence for a cleaner, brighter future.</p>
    </div>
    <div class="scroll-indicator">SCROLL DOWN</div>
</section>

<section class="grid-section">
    <div class="grid-item">
        <img src="assets/images/solar-panel-grid.jpg" alt="Solar Panels">
        <div class="grid-overlay">
            <h3>Innovative Solar Solutions</h3>
            <p>Maximizing energy yield with cutting-edge technology.</p>
        </div>
    </div>
    <div class="grid-item">
        <img src="assets/images/battery-grid.jpg" alt="Batteries">
        <div class="grid-overlay">
            <h3>Advanced Energy Storage</h3>
            <p>High-capacity battery systems for uninterrupted power.</p>
        </div>
    </div>
</section>

<section class="redefining-section">
    <div class="redefining-content">
        <div class="redefining-title"><h2>Redefining Home Energy</h2></div>
        <div class="redefining-text"><p>In the modern energy landscape, your home is more than just a shelter—it is a power plant. We integrate structural integrity with energy ambition.</p></div>
    </div>
</section>

<section class="stats">
    <div class="stats-container">
        <div class="stat-item"><h2>5000+</h2><p>HOMES POWERED</p></div>
        <div class="stat-item"><h2>200+</h2><p>EXPERT ENGINEERS</p></div>
        <div class="stat-item"><h2>₹10B+</h2><p>SUBSIDIES PROCESSED</p></div>
    </div>
</section>

<section class="projects-gallery" id="products" style="padding: 10rem 5%;">
    <h2 style="font-size: 4rem; color: var(--brand-blue); font-weight: 900; letter-spacing: -2px;">Our Latest Products</h2>
    <p style="font-size: 1.2rem; color: var(--text-muted); margin-bottom: 5rem; max-width: 800px; margin-left: auto; margin-right: auto;">Our products stem from a commitment to sustainability and the power of renewable ideas.</p>

    <div class="product-grid">
        <?php foreach ($products as $p):
            $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ? LIMIT 3");
            $stmt->execute([$p['id']]);
            $props = $stmt->fetchAll();
        ?>
            <div class="glass-card product-card" style="padding: 3rem;">
                <h3 style="color: var(--brand-blue); font-size: 1.8rem; font-weight: 800; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($p['name']); ?></h3>
                <p style="color: var(--brand-green); font-size: 0.8rem; font-weight: 800; margin-bottom: 1.5rem; letter-spacing: 1px;"><?php echo strtoupper(str_replace('_', ' ', $p['category'])); ?></p>
                <h4 style="font-size: 2.2rem; margin-bottom: 2rem; color: var(--brand-blue); font-weight: 900;"><?php echo formatPrice($p['price']); ?></h4>

                <ul class="property-list" style="margin-bottom: 2.5rem;">
                    <?php foreach ($props as $prop): ?>
                        <li><span><?php echo htmlspecialchars($prop['property_name']); ?></span> <strong><?php echo htmlspecialchars($prop['property_value']); ?></strong></li>
                    <?php endforeach; ?>
                </ul>

                <div style="display: flex; gap: 15px;">
                    <a href="product.php?id=<?php echo $p['id']; ?>" class="btn" style="background: var(--light-bg); color: var(--brand-blue); flex: 1; text-align: center; font-weight: 800;">DETAILS</a>
                    <a href="index.php?add=<?php echo $p['id']; ?>" class="btn btn-primary" style="flex: 1; text-align: center; font-weight: 800;">BUY NOW</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 60px;">
        <a href="#products" class="contact-btn-pill">BROWSE ALL PRODUCTS</a>
    </div>
</section>

<section class="innovation-section">
    <div class="innovation-grid">
        <div class="innovation-image"><img src="assets/images/solar-maintenance.jpg" alt="Maintenance"></div>
        <div class="innovation-text">
            <h2>Engineering Excellence</h2>
            <p>Reducing energy costs by up to 60% through high-performance solar harvesting and intelligent storage management.</p>
            <div class="innovation-actions">
                <a href="register.php" class="sus-btn-primary">Join Network</a>
                <a href="#" class="sus-btn-ghost">Consulting</a>
            </div>
        </div>
    </div>
</section>

<section class="light-lifecycle">
    <div style="text-align:center; margin-bottom: 70px;">
        <h2 style="font-size: 4rem; font-weight: 900; color: var(--brand-blue); letter-spacing: -2px;">Our Process</h2>
    </div>
    <div class="lifecycle-grid-alt" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; max-width: 1600px; margin: 0 auto; padding: 0 5%;">
        <div class="card"><div class="watermark-num">01</div><h3>Analysis</h3><p>CIBIL-based credit assessment and subsidy qualification.</p></div>
        <div class="card"><div class="watermark-num">02</div><h3>Design</h3><p>Custom engineering for optimal wattage and storage capacity.</p></div>
        <div class="card"><div class="watermark-num">03</div><h3>Payout</h3><p>Automated distribution of Level and Referral income.</p></div>
        <div class="card"><div class="watermark-num">04</div><h3>Success</h3><p>Sustainable returns for both the company and the customer.</p></div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
