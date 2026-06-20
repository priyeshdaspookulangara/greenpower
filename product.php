<?php
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('index.php');
}

// Fetch properties
$stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ?");
$stmt->execute([$id]);
$properties = $stmt->fetchAll();

// Add to cart logic
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $id;
    redirect('cart.php');
}

include 'includes/header.php';
?>

<div class="container" style="margin-top: 150px; margin-bottom: 100px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: start;">
        <div class="glass-card" style="padding: 0; overflow: hidden; height: 600px; background: var(--white); display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
            <i class="fas fa-solar-panel" style="font-size: 12rem; color: var(--brand-green); opacity: 0.2;"></i>
        </div>

        <div>
            <p class="category" style="text-transform: uppercase; letter-spacing: 2px; color: var(--brand-green); margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 800;">
                <?php echo str_replace('_', ' ', $product['category']); ?>
            </p>
            <h1 style="font-size: 4rem; margin-bottom: 1rem; color: var(--brand-blue); font-weight: 900; line-height: 1;"><?php echo htmlspecialchars($product['name']); ?></h1>
            <h2 style="font-size: 2.5rem; margin-bottom: 2.5rem; color: var(--brand-green); font-weight: 800;"><?php echo formatPrice($product['price']); ?></h2>

            <div class="glass-card" style="margin-bottom: 2.5rem; padding: 2rem;">
                <h4 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--brand-green); padding-bottom: 10px; color: var(--brand-blue); text-transform: uppercase; letter-spacing: 1px;">Technical Specifications</h4>
                <ul class="property-list">
                    <?php foreach ($properties as $prop): ?>
                        <li>
                            <span style="color: var(--text-muted);"><?php echo htmlspecialchars($prop['property_name']); ?></span>
                            <strong style="color: var(--brand-blue);"><?php echo htmlspecialchars($prop['property_value']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <span style="color: var(--text-muted);">Availability</span>
                        <strong style="color: <?php echo $product['stock'] > 0 ? 'var(--brand-green)' : '#e74c3c'; ?>">
                            <?php echo $product['stock'] > 0 ? $product['stock'] . ' In Stock' : 'Out of Stock'; ?>
                        </strong>
                    </li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 100%; padding: 20px; font-size: 1.1rem; font-weight: 800; border-radius: 4px;" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock'] > 0 ? 'ADD TO SHOPPING CART' : 'OUT OF STOCK'; ?>
                </button>
            </form>

            <p style="margin-top: 1.5rem; color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">
                <i class="fas fa-shield-check" style="margin-right: 10px; color: var(--brand-green);"></i>
                Secure transaction with CIBIL-based subsidy processing.
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
