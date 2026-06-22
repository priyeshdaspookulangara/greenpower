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
        $stmt->execute([$email]); // Wait, this should be referrer_email
        // Fixing the logic here while I'm at it
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

<div class="container" style="max-width: 550px;">
    <div class="glass-card" style="margin-top: 50px;">
        <h2 class="neon-text" style="text-align: center; margin-bottom: 2rem;">Join the Network</h2>

        <?php if ($error): ?>
            <div style="background: rgba(255,0,0,0.1); border: 1px solid #ff4d4d; color: #ff4d4d; padding: 10px; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="name" required>

            <label>Email Address</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>CIBIL Credit Status</label>
            <select name="cibil_status" required>
                <option value="good">Good (Standard Subsidy)</option>
                <option value="low">Low (Third-Party Flow)</option>
            </select>

            <label>Referrer Email (Optional)</label>
            <input type="email" name="referrer_email" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>" placeholder="Invite code / email">

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem;">Initialize Membership</button>
        </form>

        <p style="margin-top: 2rem; text-align: center; color: var(--text-dim); font-size: 0.9rem;">
            Existing member? <a href="login.php" class="neon-text" style="text-decoration: none;">Sign In</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
