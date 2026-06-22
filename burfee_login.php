<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member_id'];
    $password = $_POST['password'];

    // 🕵️ Debug Log
    $log_file = 'burfee_auth.log';
    $timestamp = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->query("SELECT * FROM customer LIMIT 1");
        $sample = $stmt->fetch();

        $col_id = 'MemberId';
        $col_pass = 'MemberPass';
        $col_name = 'FullName';
        $col_cibil = 'CibilStatus';

        if ($sample) {
            $cols = array_keys($sample);
            $findCol = function($options, $existingCols) {
                foreach ($options as $opt) {
                    foreach ($existingCols as $actual) {
                        if (strcasecmp($opt, $actual) === 0) return $actual;
                    }
                }
                return null;
            };
            $col_id = $findCol(['MemberId', 'Memberid', 'email', 'id'], $cols) ?? $col_id;
            $col_pass = $findCol(['MemberPass', 'Memberpass', 'password', 'pass'], $cols) ?? $col_pass;
            $col_name = $findCol(['FullName', 'Fullname', 'name', 'DisplayName'], $cols) ?? $col_name;
            $col_cibil = $findCol(['CibilStatus', 'Cibilstatus', 'status', 'cibil'], $cols) ?? $col_cibil;
        }

        $stmt = $pdo->prepare("SELECT * FROM customer WHERE $col_id = ?");
        $stmt->execute([$member_id]);
        $member = $stmt->fetch();

        $authenticated = false;
        if ($member) {
            if ($member[$col_pass] === $password || password_verify($password, $member[$col_pass])) {
                $authenticated = true;
            }
        }

        if ($authenticated) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$member[$col_id]]);
            $user = $stmt->fetch();

            $actual_name = $member[$col_name] ?? $member[$col_id];

            if (!$user) {
                $status = $member[$col_cibil] ?? 'good';
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, cibil_status) VALUES (?, ?, ?, 'customer', ?)");
                $stmt->execute([$actual_name, $member[$col_id], password_hash($password, PASSWORD_DEFAULT), $status]);
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = 'customer';
                $_SESSION['name'] = $actual_name;
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
            }
            redirect('index.php');
        } else {
            $error = "Invalid Member ID or Password.";
        }
    } catch (PDOException $e) {
        $error = "Auth System Error.";
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 450px;">
    <div class="glass-card" style="margin-top: 50px;">
        <h2 class="neon-text" style="text-align: center; margin-bottom: 1rem;">Legacy Portal</h2>
        <p style="color: var(--text-dim); text-align: center; margin-bottom: 2rem; font-size: 0.9rem;">Authenticate using your Burfee credentials.</p>

        <?php if ($error): ?>
            <div style="background: rgba(255,0,0,0.1); border: 1px solid #ff4d4d; color: #ff4d4d; padding: 10px; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Member ID</label>
            <input type="text" name="member_id" required placeholder="ID / Email">

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Verify & Enter</button>
        </form>

        <div style="margin-top: 2rem; text-align: center;">
            <a href="index.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.8rem;"><i class="fas fa-arrow-left"></i> Return to Shop</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
