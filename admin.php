<?php
require_once 'includes/functions.php';

if (!isAdmin()) {
    redirect('index.php');
}

// Handle Product Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $stock]);
        $product_id = $pdo->lastInsertId();

        if (isset($_POST['prop_names'])) {
            foreach ($_POST['prop_names'] as $i => $prop_name) {
                if (!empty($prop_name)) {
                    $prop_val = $_POST['prop_values'][$i];
                    $stmt = $pdo->prepare("INSERT INTO product_properties (product_id, property_name, property_value) VALUES (?, ?, ?)");
                    $stmt->execute([$product_id, $prop_name, $prop_val]);
                }
            }
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

// Handle Product Delete
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $stmt = $pdo->prepare("DELETE FROM product_properties WHERE product_id = ?");
    $stmt->execute([$id]);
    redirect('admin.php');
}

$products = $pdo->query("SELECT * FROM products")->fetchAll();
$transactions = $pdo->query("SELECT t.*, u.name as user_name FROM transactions t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC")->fetchAll();
$total_revenue = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type = 'company_revenue'")->fetch()['total'] ?? 0;
$total_orders = $pdo->query("SELECT COUNT(*) as total FROM orders")->fetch()['total'] ?? 0;

include 'includes/header.php';
?>

<div class="container" style="max-width: 1600px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="neon-text">Admin Control Center</h2>
        <button class="btn btn-primary" onclick="openProductModal()">+ Add New Product</button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="glass-card" style="text-align: center;">
            <p style="color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Company Revenue</p>
            <p class="neon-text" style="font-size: 2rem; font-weight: bold;"><?php echo formatPrice($total_revenue); ?></p>
        </div>
        <div class="glass-card" style="text-align: center;">
            <p style="color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Total Orders</p>
            <p class="neon-text" style="font-size: 2rem; font-weight: bold;"><?php echo $total_orders; ?></p>
        </div>
        <div class="glass-card" style="text-align: center;">
            <p style="color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Total Products</p>
            <p class="neon-text" style="font-size: 2rem; font-weight: bold;"><?php echo count($products); ?></p>
        </div>
    </div>

    <div class="glass-card">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">Product Inventory</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                        <td><span style="font-size: 0.7rem; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 3px;"><?php echo strtoupper(str_replace('_', ' ', $p['category'])); ?></span></td>
                        <td><?php echo formatPrice($p['price']); ?></td>
                        <td><?php echo $p['stock']; ?></td>
                        <td>
                            <a href="admin.php?delete_product=<?php echo $p['id']; ?>" style="color: #ff4d4d;" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card" style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">Financial Ledger (Subsidies & Commissions)</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order</th>
                        <th>Beneficiary</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td style="font-size: 0.8rem; color: #aaa;"><?php echo date('d M, Y H:i', strtotime($t['created_at'])); ?></td>
                        <td>#<?php echo $t['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($t['user_name'] ?? 'System'); ?></td>
                        <td>
                            <?php
                                $color = '#aaa';
                                if($t['type'] == 'company_revenue') $color = 'var(--neon-blue)';
                                if($t['type'] == 'referral_commission') $color = '#ffc107';
                                if($t['type'] == 'subsidy_payout') $color = '#4caf50';
                                if($t['type'] == 'third_party_subsidy') $color = '#ff5722';
                            ?>
                            <span style="font-size: 0.7rem; color: #fff; background: <?php echo $color; ?>; padding: 2px 6px; border-radius: 3px;"><?php echo strtoupper($t['type']); ?></span>
                        </td>
                        <td style="font-weight: bold;"><?php echo formatPrice($t['amount']); ?></td>
                        <td style="font-size: 0.8rem; color: #aaa;"><?php echo htmlspecialchars($t['description']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PRODUCT ADD MODAL -->
<div class="modal-overlay" id="productModal">
    <div class="login-modal" style="max-width: 600px; text-align: left;">
        <span class="close-btn" onclick="closeProductModal()">&times;</span>
        <h2 style="margin-bottom: 1rem; color: var(--brand-blue);">Add New Product</h2>
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label>Product Name</label>
                    <input type="text" name="name" required>
                </div>
                <div>
                    <label>Category</label>
                    <select name="category" style="width: 100%; padding: 14px; border-radius: 6px; border: 1px solid #ddd; margin-bottom: 15px;">
                        <option value="solar_panel">Solar Panel</option>
                        <option value="battery">Battery</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label>Price (₹)</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div>
                    <label>Stock</label>
                    <input type="number" name="stock" required>
                </div>
            </div>

            <h5 style="margin: 1rem 0 0.5rem; color: #666; font-size: 0.9rem; text-transform: uppercase;">Technical Specifications</h5>
            <div id="specs-container">
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" name="prop_names[]" placeholder="Name (e.g. Capacity)" style="margin-bottom: 0;">
                    <input type="text" name="prop_values[]" placeholder="Value (e.g. 150Ah)" style="margin-bottom: 0;">
                </div>
            </div>
            <button type="button" class="btn" style="background: #eee; color: #333; font-size: 0.8rem; margin-top: 5px;" onclick="addSpecRow()">+ Add Spec</button>

            <button type="submit" name="add_product" class="submit-btn" style="margin-top: 2rem;">Publish Product</button>
        </form>
    </div>
</div>

<script>
    const productModal = document.getElementById('productModal');
    function openProductModal() {
        productModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeProductModal() {
        productModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    function addSpecRow() {
        const container = document.getElementById('specs-container');
        const div = document.createElement('div');
        div.style.display = 'flex';
        div.style.gap = '10px';
        div.style.marginBottom = '10px';
        div.innerHTML = `
            <input type="text" name="prop_names[]" placeholder="Name" style="margin-bottom: 0;">
            <input type="text" name="prop_values[]" placeholder="Value" style="margin-bottom: 0;">
        `;
        container.appendChild(div);
    }
</script>

<?php include 'includes/footer.php'; ?>
