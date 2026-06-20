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
    $cibil = $_POST['cibil_status'];
    $referrer_email = $_POST['referrer_email'] ?? null;

    $referrer_id = null;
    if ($referrer_email) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$referrer_email]);
        $ref = $stmt->fetch();
        if ($ref) $referrer_id = $ref['id'];
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, cibil_status, referrer_id) VALUES (?, ?, ?, 'customer', ?, ?)");
        $stmt->execute([$name, $email, $password, $cibil, $referrer_id]);
        redirect('login.php?registered=1');
    } catch (Exception $e) {
        $error = "Registration failed. Email might already exist.";
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 600px;">
    <div class="glass-card">
        <h2 class="neon-text" style="margin-bottom: 1.5rem;">Join SolarShop</h2>
        <?php if ($error): ?>
            <p style="color: #ff4d4d; margin-bottom: 15px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label>Full Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>CIBIL Status</label>
                <select name="cibil_status" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(10, 25, 41, 0.9); color: white;">
                    <option value="good">Good (Qualify for Subsidy)</option>
                    <option value="low">Low (Third-Party Routing)</option>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label>Referrer Email (Optional)</label>
                <input type="email" name="referrer_email" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
        </form>

        <p style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
            Already have an account? <a href="#" onclick="openLoginModal(event)" style="color: var(--neon-blue);">Login here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
