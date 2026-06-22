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

<div class="container" style="max-width: 450px;">
    <div class="card" style="margin-top: 150px; margin-bottom: 100px;">
        <h2 style="text-align: center; margin-bottom: 2rem;">Sign In</h2>

        <?php if ($error): ?>
            <div style="background: rgba(255,0,0,0.1); border: 1px solid #ff4d4d; color: #ff4d4d; padding: 10px; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Email Address</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Access Dashboard</button>
        </form>

        <div style="margin-top: 2rem; text-align: center; font-size: 0.9rem;">
            <p style="color: var(--text-secondary);">New to Empress Green Power? <a href="register.php" style="color: var(--brand-green); text-decoration: none; font-weight: 700;">Create Account</a></p>
            <p style="margin-top: 10px;"><a href="burfee_login.php" style="color: var(--accent-blue); text-decoration: none;">Legacy Member Login</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
