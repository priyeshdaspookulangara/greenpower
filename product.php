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

<div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 4rem; align-items: start;">
        <div class="glass-card" style="padding: 3rem; text-align: center; background: rgba(0,0,0,0.2);">
             <i class="fas fa-microchip" style="font-size: 10rem; color: var(--neon-green); opacity: 0.5;"></i>
             <div style="margin-top: 2rem; color: var(--text-dim);">Product Visualization</div>
        </div>

        <div class="glass-card">
            <span class="badge badge-info" style="margin-bottom: 1rem;"><?php echo strtoupper(str_replace('_', ' ', $product['category'])); ?></span>
            <h1 class="neon-text" style="font-size: 3.5rem; margin-bottom: 1rem; line-height: 1.1;"><?php echo htmlspecialchars($product['name']); ?></h1>
            <h2 class="neon-text" style="font-size: 2.5rem; margin-bottom: 2rem; color: var(--neon-blue);"><?php echo formatPrice($product['price']); ?></h2>

            <div style="margin-bottom: 2.5rem;">
                <h4 style="margin-bottom: 1.5rem; color: var(--neon-green); text-transform: uppercase;">Technical Specifications</h4>
                <ul class="property-list">
                    <?php foreach ($properties as $prop): ?>
                        <li>
                            <span><?php echo htmlspecialchars($prop['property_name']); ?></span>
                            <strong><?php echo htmlspecialchars($prop['property_value']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <span>Availability</span>
                        <strong style="color: <?php echo $product['stock'] > 0 ? 'var(--neon-green)' : '#ff4d4d'; ?>">
                            <?php echo $product['stock'] > 0 ? $product['stock'] . ' Units in Stock' : 'Out of Stock'; ?>
                        </strong>
                    </li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 100%; padding: 18px; font-size: 1rem;" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock'] > 0 ? 'PURCHASE NOW' : 'STOCK DEPLETED'; ?>
                </button>
            </form>

            <p style="margin-top: 1.5rem; color: var(--text-dim); font-size: 0.85rem; text-align: center;">
                <i class="fas fa-lock" style="margin-right: 8px;"></i> Secure Transaction & Immediate Income Processing
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
