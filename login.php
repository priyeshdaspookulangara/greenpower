<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        redirect('index.php');
    } else {
        $error = "Invalid email or password.";
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 500px;">
    <div class="glass-card">
        <h2 class="neon-text">Login</h2>
        <?php if ($error): ?>
            <p style="color: #ff4d4d; margin-bottom: 15px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>

        <p style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
            Need an account? <a href="register.php" style="color: var(--neon-blue);">Register here</a>
        </p>
        <p style="text-align: center; font-size: 0.9rem; margin-top: 10px;">
            Existing Burfee Member? <a href="burfee_login.php" style="color: var(--neon-blue);">Login here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
