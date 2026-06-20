<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check existing customer table
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE email = ?");
    $stmt->execute([$email]);
    $member = $stmt->fetch();

    if ($member && password_verify($password, $member['password'])) {
        // Find if user already exists in main users table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Auto-register them in our main system
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, cibil_status) VALUES (?, ?, ?, 'customer', 'good')");
            $stmt->execute([$member['name'], $member['email'], $member['password']]);
            $user_id = $pdo->lastInsertId();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = 'customer';
            $_SESSION['name'] = $member['name'];
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
        }
        redirect('index.php');
    } else {
        $error = "Invalid Burfee credentials.";
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 500px;">
    <div class="glass-card">
        <h2 class="neon-text">Burfee Member Login</h2>
        <p style="color: #aaa; margin-bottom: 20px; font-size: 0.9rem;">Access the SolarShop using your legacy Burfee credentials.</p>

        <?php if ($error): ?>
            <p style="color: #ff4d4d; margin-bottom: 15px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Burfee Email</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Authenticate Member</button>
        </form>

        <p style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
            <a href="login.php" style="color: var(--neon-blue);">Back to Standard Login</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
