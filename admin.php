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

<div class="admin-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h4 style="color: #fff; margin: 0;">SOLARSHOP ADMIN</h4>
        </div>
        <ul class="sidebar-nav">
            <li><a href="admin.php" class="active"><i class="fas fa-th-large"></i> Overview</a></li>
            <li><a href="#inventory"><i class="fas fa-box"></i> Inventory</a></li>
            <li><a href="#ledger"><i class="fas fa-file-invoice-dollar"></i> Financial Ledger</a></li>
            <li><a href="index.php"><i class="fas fa-shopping-cart"></i> View Shop</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
                </ol>
            </nav>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 class="m-0" style="color: #334155;">Admin Overview</h1>
                <button class="btn btn-primary" onclick="openProductModal()" style="background-color: #28a745; color: #fff;">
                    <i class="fas fa-plus mr-2"></i> Add New Product
                </button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
            <div class="admin-card stat-box">
                <div class="label">Total Revenue</div>
                <div class="value" style="color: #28a745;"><?php echo formatPrice($total_revenue); ?></div>
            </div>
            <div class="admin-card stat-box">
                <div class="label">Total Orders</div>
                <div class="value"><?php echo $total_orders; ?></div>
            </div>
            <div class="admin-card stat-box">
                <div class="label">Active Products</div>
                <div class="value"><?php echo count($products); ?></div>
            </div>
        </div>

        <div class="admin-card" id="inventory">
            <div class="admin-card-header">
                <span>Product Inventory</span>
                <span style="font-size: 0.8rem; background: #e1e5eb; padding: 4px 10px; border-radius: 20px;">
                    <?php echo count($products); ?> Items
                </span>
            </div>
            <div class="admin-card-body p-0">
                <table style="color: #333; margin-top: 0;">
                    <thead style="background: #f8f9fb;">
                        <tr>
                            <th style="padding-left: 20px;">Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th style="text-align: right; padding-right: 20px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td style="padding-left: 20px;"><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                            <td><span style="font-size: 0.75rem; color: #666; background: #f0f2f5; padding: 2px 8px; border-radius: 4px;"><?php echo strtoupper(str_replace('_', ' ', $p['category'])); ?></span></td>
                            <td style="font-weight: 600;"><?php echo formatPrice($p['price']); ?></td>
                            <td><?php echo $p['stock']; ?> units</td>
                            <td style="text-align: right; padding-right: 20px;">
                                <a href="admin.php?delete_product=<?php echo $p['id']; ?>" style="color: #dc3545;" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card" id="ledger" style="margin-top: 2rem;">
            <div class="admin-card-header">
                <span>Financial Ledger & Commission Tracking</span>
            </div>
            <div class="admin-card-body p-0">
                <table style="color: #333; margin-top: 0;">
                    <thead style="background: #f8f9fb;">
                        <tr>
                            <th style="padding-left: 20px;">Date</th>
                            <th>Order</th>
                            <th>Beneficiary</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th style="padding-right: 20px;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td style="padding-left: 20px; font-size: 0.8rem; color: #666;"><?php echo date('d M, Y H:i', strtotime($t['created_at'])); ?></td>
                            <td>#<?php echo $t['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($t['user_name'] ?? 'System'); ?></td>
                            <td>
                                <?php
                                    $class = 'status-scheduled'; // Default blue
                                    if($t['type'] == 'company_revenue') $class = 'status-completed'; // Green
                                    if($t['type'] == 'referral_commission') $class = 'status-in-progress'; // Orange
                                    if($t['type'] == 'third_party_subsidy') $class = 'status-postponed'; // Red
                                ?>
                                <span class="badge" style="font-size: 0.7rem; color: #fff; background: <?php
                                    if($t['type'] == 'company_revenue') echo '#28a745';
                                    elseif($t['type'] == 'referral_commission') echo '#fd7e14';
                                    elseif($t['type'] == 'subsidy_payout') echo '#007bff';
                                    else echo '#dc3545';
                                ?>; padding: 2px 6px; border-radius: 3px;"><?php echo strtoupper($t['type']); ?></span>
                            </td>
                            <td style="font-weight: 700;"><?php echo formatPrice($t['amount']); ?></td>
                            <td style="font-size: 0.8rem; color: #666; padding-right: 20px;"><?php echo htmlspecialchars($t['description']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- PRODUCT ADD MODAL -->
<div class="modal-overlay" id="productModal">
    <div class="login-modal" style="max-width: 600px; text-align: left;">
        <span class="close-btn" onclick="closeProductModal()">&times;</span>
        <h3 style="margin-bottom: 1.5rem; color: #333;">Schedule New Product</h3>
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="color: #666; font-size: 0.9rem;">Product Name</label>
                    <input type="text" name="name" required placeholder="e.g. 440W Solar Panel">
                </div>
                <div>
                    <label style="color: #666; font-size: 0.9rem;">Category</label>
                    <select name="category" style="width: 100%; padding: 14px; border-radius: 6px; border: 1px solid #ddd; margin-bottom: 15px;">
                        <option value="solar_panel">Solar Panel</option>
                        <option value="battery">Battery</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="color: #666; font-size: 0.9rem;">Unit Price (₹)</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div>
                    <label style="color: #666; font-size: 0.9rem;">Inventory Stock</label>
                    <input type="number" name="stock" required>
                </div>
            </div>

            <h5 style="margin: 1rem 0 0.5rem; color: #333; font-size: 0.9rem;">Technical Specifications</h5>
            <div id="specs-container">
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" name="prop_names[]" placeholder="Spec Name" style="margin-bottom: 0;">
                    <input type="text" name="prop_values[]" placeholder="Value" style="margin-bottom: 0;">
                </div>
            </div>
            <button type="button" class="btn" style="background: #f8f9fa; color: #333; font-size: 0.8rem; border: 1px solid #ddd; margin-top: 5px;" onclick="addSpecRow()">
                <i class="fas fa-plus mr-1"></i> Add Specification
            </button>

            <div style="text-align: right; margin-top: 2rem;">
                <button type="button" class="btn" style="background: #6c757d; color: #fff; margin-right: 10px;" onclick="closeProductModal()">Cancel</button>
                <button type="submit" name="add_product" class="btn" style="background: #28a745; color: #fff; padding: 12px 30px;">Save Product</button>
            </div>
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
            <input type="text" name="prop_names[]" placeholder="Spec Name" style="margin-bottom: 0;">
            <input type="text" name="prop_values[]" placeholder="Value" style="margin-bottom: 0;">
        `;
        container.appendChild(div);
    }
</script>

<?php include 'includes/footer.php'; ?>
