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

$products = $pdo->query("SELECT * FROM products")->fetchAll();
$orders = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();
$transactions = $pdo->query("SELECT t.*, u.name as user_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Solar Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav>
            <div class="logo"><h2 class="neon-text">ADMIN PANEL</h2></div>
            <div class="links">
                <a href="index.php">View Shop</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>

        <div class="glass-card" style="margin-bottom: 30px;">
            <h3>Add New Product</h3>
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Product Name</label>
                        <input type="text" name="name" required>
                        <label>Category</label>
                        <select name="category">
                            <option value="solar_panel">Solar Panel</option>
                            <option value="battery">Battery</option>
                        </select>
                    </div>
                    <div>
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" required>
                        <label>Stock</label>
                        <input type="number" name="stock" required>
                    </div>
                </div>

                <h4>Custom Properties</h4>
                <div id="properties-container">
                    <div class="property-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input type="text" name="prop_names[]" placeholder="Property Name (e.g. Wattage)">
                        <input type="text" name="prop_values[]" placeholder="Value (e.g. 440W)">
                    </div>
                </div>
                <button type="button" class="btn" onclick="addPropertyRow()" style="background: var(--glass-bg); margin-bottom: 15px;">+ Add Property</button>

                <button type="submit" name="add_product" class="btn btn-primary" style="width: 100%;">Save Product</button>
            </form>
        </div>

        <div class="glass-card" style="margin-bottom: 30px;">
            <h3>Product Inventory</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo $p['category']; ?></td>
                        <td><?php echo formatPrice($p['price']); ?></td>
                        <td><?php echo $p['stock']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="glass-card">
            <h3>Transaction Ledger & Split History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?php echo $t['created_at']; ?></td>
                        <td>#<?php echo $t['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($t['user_name']); ?></td>
                        <td class="neon-text"><?php echo formatPrice($t['amount']); ?></td>
                        <td><code><?php echo strtoupper($t['type']); ?></code></td>
                        <td><small><?php echo htmlspecialchars($t['description']); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function addPropertyRow() {
            const container = document.getElementById('properties-container');
            const row = document.createElement('div');
            row.className = 'property-row';
            row.style.display = 'flex';
            row.style.gap = '10px';
            row.style.marginBottom = '10px';
            row.innerHTML = `
                <input type="text" name="prop_names[]" placeholder="Property Name">
                <input type="text" name="prop_values[]" placeholder="Value">
            `;
            container.appendChild(row);
        }
    </script>
</body>
</html>
