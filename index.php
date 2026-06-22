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

<!-- HERO SECTION -->
<section class="hero" style="position: relative; height: 90vh; min-height: 600px; background: url('https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover no-repeat; display: flex; align-items: center; justify-content: center; color: white;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4);"></div>
    <div class="container" style="position: relative; z-index: 1; text-align: center;">
        <h1 style="font-size: clamp(2.5rem, 8vw, 5rem); line-height: 1.1; margin-bottom: 1.5rem; color: white;">Solar Energy<br><span style="color: var(--brand-green);">For A Sustainable Future</span></h1>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto 2.5rem; opacity: 0.9;">Empowering homes and businesses with high-efficiency solar solutions. Reduce your carbon footprint and energy costs today.</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="#products" class="btn btn-primary">View Collection</a>
            <a href="#process" class="btn btn-outline" style="border-color: white; color: white;">Our Process</a>
        </div>
    </div>
</section>

<!-- SERVICES / CATEGORIES -->
<section class="section-padding" style="background: var(--bg-light);">
    <div class="container">
        <h2 class="section-title">Solar Energy Services</h2>
        <p class="section-subtitle">We provide end-to-end solar solutions tailored to your energy needs.</p>

        <div class="product-grid">
            <div class="card" style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--brand-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
                    <i class="fas fa-solar-panel"></i>
                </div>
                <h3>Residential Solar</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Custom solar panel installations for modern homes to achieve energy independence.</p>
                <a href="#" style="color: var(--brand-green); font-weight: 700; text-decoration: none;">Learn More →</a>
            </div>
            <div class="card" style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--brand-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
                    <i class="fas fa-city"></i>
                </div>
                <h3>Commercial Solar</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Scalable energy solutions for businesses to reduce overhead and meet ESG goals.</p>
                <a href="#" style="color: var(--brand-green); font-weight: 700; text-decoration: none;">Learn More →</a>
            </div>
            <div class="card" style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--brand-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
                    <i class="fas fa-battery-full"></i>
                </div>
                <h3>Energy Storage</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Advanced battery backup systems to keep your power running day and night.</p>
                <a href="#" style="color: var(--brand-green); font-weight: 700; text-decoration: none;">Learn More →</a>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTS SECTION -->
<section class="section-padding" id="products">
    <div class="container">
        <h2 class="section-title">Premium Products</h2>
        <p class="section-subtitle">Explore our range of high-performance solar panels and battery storage units.</p>

        <div class="product-grid">
            <?php foreach ($products as $p):
                $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ? LIMIT 3");
                $stmt->execute([$p['id']]);
                $props = $stmt->fetchAll();
            ?>
                <div class="card product-card">
                    <div style="height: 200px; background: #eee; display: flex; align-items: center; justify-content: center; color: #ccc;">
                        <i class="fas fa-image fa-4x"></i>
                    </div>
                    <div class="product-info">
                        <span class="badge badge-info" style="margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($p['category'] == 'solar_panel' ? 'Solar Panel' : 'Battery'); ?>
                        </span>
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <div class="price" style="font-size: 1.5rem; font-weight: 800; color: var(--brand-green); margin: 0.5rem 0;">
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
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-outline" style="text-align: center; padding: 10px 5px;">Details</a>
                            <a href="index.php?add=<?php echo $p['id']; ?>" class="btn btn-primary" style="text-align: center; padding: 10px 5px;">Add to Cart</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- OUR PROCESS -->
<section class="section-padding" id="process" style="background: var(--bg-light);">
    <div class="container">
        <h2 class="section-title">Our Process</h2>
        <p class="section-subtitle">How we transition your property to clean, renewable energy.</p>

        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4rem; margin-top: 3rem;">
            <div style="text-align: center; max-width: 250px;">
                <div style="width: 150px; height: 150px; border-radius: 50%; border: 5px solid white; box-shadow: var(--shadow-soft); margin: 0 auto 1.5rem; overflow: hidden; background: white; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-search fa-3x" style="color: var(--brand-green);"></i>
                </div>
                <h4>Consultation</h4>
                <p style="font-size: 0.9rem; color: var(--text-secondary);">We analyze your energy needs and property layout.</p>
            </div>
            <div style="text-align: center; max-width: 250px;">
                <div style="width: 150px; height: 150px; border-radius: 50%; border: 5px solid white; box-shadow: var(--shadow-soft); margin: 0 auto 1.5rem; overflow: hidden; background: white; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-pencil-ruler fa-3x" style="color: var(--brand-green);"></i>
                </div>
                <h4>Design</h4>
                <p style="font-size: 0.9rem; color: var(--text-secondary);">Custom engineering for maximum efficiency.</p>
            </div>
            <div style="text-align: center; max-width: 250px;">
                <div style="width: 150px; height: 150px; border-radius: 50%; border: 5px solid white; box-shadow: var(--shadow-soft); margin: 0 auto 1.5rem; overflow: hidden; background: white; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-tools fa-3x" style="color: var(--brand-green);"></i>
                </div>
                <h4>Installation</h4>
                <p style="font-size: 0.9rem; color: var(--text-secondary);">Fast and professional solar deployment.</p>
            </div>
        </div>
    </div>
</section>

<!-- STATISTICS -->
<section class="section-padding">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-box">
                <span class="value">5000+</span>
                <span class="label">Homes Powered</span>
            </div>
            <div class="stat-box">
                <span class="value">₹10B+</span>
                <span class="label">Subsidies Processed</span>
            </div>
            <div class="stat-box">
                <span class="value">99%</span>
                <span class="label">Client Satisfaction</span>
            </div>
            <div class="stat-box">
                <span class="value">2M+</span>
                <span class="label">Trees Planted Eq.</span>
            </div>
        </div>
    </div>
</section>

<!-- BRAND LOGOS -->
<section style="padding: 50px 0; background: #fafafa; border-top: 1px solid #eee;">
    <div class="container">
        <div style="display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 3rem; opacity: 0.5;">
            <i class="fab fa-aws fa-3x"></i>
            <i class="fab fa-google fa-3x"></i>
            <i class="fab fa-microsoft fa-3x"></i>
            <i class="fab fa-apple fa-3x"></i>
            <i class="fab fa-facebook fa-3x"></i>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
