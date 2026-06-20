<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $cibil_status = $_POST['cibil_status'];
    $referrer_email = $_POST['referrer_email'];
    $role = $_POST['role'] ?? 'customer';

    $referrer_id = null;
    if (!empty($referrer_email)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$referrer_email]);
        $ref = $stmt->fetch();
        if ($ref) {
            $referrer_id = $ref['id'];
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, cibil_status, referrer_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role, $cibil_status, $referrer_id]);
        redirect('login.php');
    } catch (PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Solar Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 50px;">
        <div class="glass-card">
            <h2 class="neon-text">Join the Solar Revolution</h2>
            <?php if ($error): ?>
                <p style="color: #ff4d4d;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <label>Full Name</label>
                <input type="text" name="name" required>

                <label>Email Address</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>CIBIL Status</label>
                <select name="cibil_status">
                    <option value="good">Good CIBIL (Profile A)</option>
                    <option value="low">Low CIBIL (Profile B)</option>
                </select>

                <label>Referrer Email (Optional)</label>
                <input type="email" name="referrer_email" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>">

                <label>Role</label>
                <select name="role">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
            </form>
            <p style="margin-top: 15px; text-align: center;">
                Already have an account? <a href="login.php" style="color: var(--neon-blue);">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
