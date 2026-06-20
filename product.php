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
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
        <div class="glass-card" style="padding: 0; overflow: hidden; height: 500px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-solar-panel" style="font-size: 10rem; color: var(--neon-blue); opacity: 0.5;"></i>
        </div>

        <div>
            <p class="category" style="text-transform: uppercase; letter-spacing: 2px; color: var(--neon-blue); margin-bottom: 0.5rem; font-size: 0.8rem;">
                <?php echo str_replace('_', ' ', $product['category']); ?>
            </p>
            <h1 class="neon-text" style="font-size: 3rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($product['name']); ?></h1>
            <h2 style="font-size: 2rem; margin-bottom: 2rem;"><?php echo formatPrice($product['price']); ?></h2>

            <div class="glass-card" style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">Technical Specifications</h4>
                <ul class="property-list">
                    <?php foreach ($properties as $prop): ?>
                        <li>
                            <span style="color: #aaa;"><?php echo htmlspecialchars($prop['property_name']); ?>:</span>
                            <strong style="float: right;"><?php echo htmlspecialchars($prop['property_value']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <span style="color: #aaa;">Availability:</span>
                        <strong style="float: right; color: <?php echo $product['stock'] > 0 ? '#4caf50' : '#ff4d4d'; ?>">
                            <?php echo $product['stock'] > 0 ? $product['stock'] . ' In Stock' : 'Out of Stock'; ?>
                        </strong>
                    </li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 100%; padding: 18px; font-size: 1.2rem;" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock'] > 0 ? 'ADD TO SHOPPING CART' : 'OUT OF STOCK'; ?>
                </button>
            </form>

            <p style="margin-top: 1.5rem; color: #aaa; font-size: 0.9rem;">
                <i class="fas fa-shield-alt" style="margin-right: 10px; color: var(--neon-blue);"></i>
                Secure transaction with CIBIL-based subsidy processing.
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
