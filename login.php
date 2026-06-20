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

<div class="container" style="max-width: 500px; margin: 150px auto 100px;">
    <div class="card">
        <h2 style="color: var(--brand-blue); margin-bottom: 1.5rem; font-weight: 800;">Login</h2>
        <?php if ($error): ?>
            <p style="color: #e74c3c; margin-bottom: 15px; font-weight: bold;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 12px; border-radius: 4px; border: 1px solid var(--border-color); background: var(--white); color: var(--text-main);">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 12px; border-radius: 4px; border: 1px solid var(--border-color); background: var(--white); color: var(--text-main);">
            </div>

            <button type="submit" class="submit-btn" style="width: 100%;">Login</button>
        </form>

        <p style="margin-top: 20px; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
            Need an account? <a href="register.php" style="color: var(--brand-blue); font-weight: 600; text-decoration: none;">Register here</a>
        </p>
        <p style="text-align: center; font-size: 0.9rem; margin-top: 10px; color: var(--text-muted);">
            Existing Burfee Member? <a href="burfee_login.php" style="color: var(--brand-blue); font-weight: 600; text-decoration: none;">Login here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
