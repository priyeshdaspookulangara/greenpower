<?php
require_once 'includes/functions.php';

if (!isAdmin()) {
    redirect('index.php');
}

$edit_product = null;
if (isset($_GET['edit_product'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit_product']]);
    $edit_product = $stmt->fetch();
    if ($edit_product) {
        $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ?");
        $stmt->execute([$edit_product['id']]);
        $edit_product['properties'] = $stmt->fetchAll();
    }
}

// Handle Product Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_product']) || isset($_POST['edit_product_submit']))) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $is_edit = isset($_POST['edit_product_submit']);
    $product_id = $is_edit ? $_POST['product_id'] : null;

    $pdo->beginTransaction();
    try {
        if ($is_edit) {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock = ? WHERE id = ?");
            $stmt->execute([$name, $category, $price, $stock, $product_id]);
            $stmt = $pdo->prepare("DELETE FROM product_properties WHERE product_id = ?");
            $stmt->execute([$product_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $category, $price, $stock]);
            $product_id = $pdo->lastInsertId();
        }

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
        redirect('admin.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
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
        <div class="sidebar-header" style="padding: 20px; text-align: center;">
            <h2 class="neon-text" style="font-size: 1.5rem;">ADMIN PANEL</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="admin.php" class="active">Dashboard</a>
            <a href="#inventory">Inventory</a>
            <a href="#ledger">Financial Ledger</a>
            <a href="index.php">View Shop</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="neon-text">Overview</h1>
            <button class="btn btn-primary" onclick="openProductModal()">+ Add Product</button>
        </div>

        <?php if(isset($error)): ?>
            <div style="background: rgba(220, 38, 38, 0.2); border: 1px solid #dc2626; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="glass-card stat-box">
                <div class="label">Total Revenue</div>
                <div class="value"><?php echo formatPrice($total_revenue); ?></div>
            </div>
            <div class="glass-card stat-box">
                <div class="label">Total Orders</div>
                <div class="value"><?php echo $total_orders; ?></div>
            </div>
            <div class="glass-card stat-box">
                <div class="label">Active Products</div>
                <div class="value"><?php echo count($products); ?></div>
            </div>
        </div>

        <div class="glass-card" id="inventory" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: var(--neon-blue);">Product Inventory</h3>
            <div class="table-container">
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
                            <td><span class="badge badge-info"><?php echo strtoupper(str_replace('_', ' ', $p['category'])); ?></span></td>
                            <td class="neon-text"><?php echo formatPrice($p['price']); ?></td>
                            <td><?php echo $p['stock']; ?> units</td>
                            <td>
                                <a href="admin.php?edit_product=<?php echo $p['id']; ?>" style="color: var(--neon-blue); margin-right: 15px;"><i class="fas fa-edit"></i></a>
                                <a href="admin.php?delete_product=<?php echo $p['id']; ?>" style="color: #ff4d4d;" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-card" id="ledger">
            <h3 style="margin-bottom: 1.5rem; color: var(--neon-blue);">Financial Ledger</h3>
            <div class="table-container">
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
                            <td style="font-size: 0.8rem; color: var(--text-dim);"><?php echo date('d M, H:i', strtotime($t['created_at'])); ?></td>
                            <td>#<?php echo $t['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($t['user_name'] ?? 'System'); ?></td>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border);">
                                    <?php echo strtoupper($t['type']); ?>
                                </span>
                            </td>
                            <td class="neon-text"><?php echo formatPrice($t['amount']); ?></td>
                            <td style="font-size: 0.8rem; color: var(--text-dim);"><?php echo htmlspecialchars($t['description']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- PRODUCT MODAL -->
<div class="modal-overlay <?php echo $edit_product ? 'active' : ''; ?>" id="productModal" style="display: <?php echo $edit_product ? 'flex' : 'none'; ?>;">
    <div class="glass-card" style="width: 90%; max-width: 600px;">
        <h3 class="neon-text" style="margin-bottom: 1.5rem;"><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h3>
        <form method="POST">
            <?php if($edit_product): ?>
                <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
            <?php endif; ?>

            <label>Product Name</label>
            <input type="text" name="name" required value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Category</label>
                    <select name="category">
                        <option value="solar_panel" <?php echo ($edit_product && $edit_product['category'] == 'solar_panel') ? 'selected' : ''; ?>>Solar Panel</option>
                        <option value="battery" <?php echo ($edit_product && $edit_product['category'] == 'battery') ? 'selected' : ''; ?>>Battery</option>
                    </select>
                </div>
                <div>
                    <label>Price (₹)</label>
                    <input type="number" name="price" step="0.01" required value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>">
                </div>
            </div>

            <label>Inventory Stock</label>
            <input type="number" name="stock" required value="<?php echo $edit_product ? $edit_product['stock'] : ''; ?>">

            <h4 style="margin: 1.5rem 0 1rem; color: var(--neon-blue);">Technical Specifications</h4>
            <div id="specs-container">
                <?php if($edit_product && !empty($edit_product['properties'])): ?>
                    <?php foreach($edit_product['properties'] as $prop): ?>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <input type="text" name="prop_names[]" value="<?php echo htmlspecialchars($prop['property_name']); ?>" placeholder="Property (e.g. Wattage)">
                            <input type="text" name="prop_values[]" value="<?php echo htmlspecialchars($prop['property_value']); ?>" placeholder="Value (e.g. 440W)">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input type="text" name="prop_names[]" placeholder="Property">
                        <input type="text" name="prop_values[]" placeholder="Value">
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-outline" style="font-size: 0.7rem; padding: 5px 15px; margin-bottom: 1rem;" onclick="addSpecRow()">+ Add Spec</button>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn" style="background: rgba(255,255,255,0.1);" onclick="closeProductModal()">Cancel</button>
                <button type="submit" name="<?php echo $edit_product ? 'edit_product_submit' : 'add_product'; ?>" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openProductModal() {
        document.getElementById('productModal').style.display = 'flex';
    }
    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
        if (window.location.search.includes('edit_product')) {
            window.location.href = 'admin.php';
        }
    }
    function addSpecRow() {
        const container = document.getElementById('specs-container');
        const div = document.createElement('div');
        div.style.display = 'flex';
        div.style.gap = '10px';
        div.style.marginBottom = '10px';
        div.innerHTML = `<input type="text" name="prop_names[]" placeholder="Property"> <input type="text" name="prop_values[]" placeholder="Value">`;
        container.appendChild(div);
    }
</script>

<?php include 'includes/footer.php'; ?>
