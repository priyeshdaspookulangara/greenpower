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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Solar Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 100px;">
        <div class="glass-card">
            <h2 class="neon-text">Login</h2>
            <?php if ($error): ?>
                <p style="color: #ff4d4d;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <label>Email Address</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            <p style="margin-top: 15px; text-align: center;">
                Need an account? <a href="register.php" style="color: var(--neon-blue);">Register here</a>
            </p>
            <p style="text-align: center;">
                Existing Burfee Member? <a href="burfee_login.php" style="color: var(--neon-blue);">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
