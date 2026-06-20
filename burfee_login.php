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
        // Fetch one record to inspect column names (case-insensitive approach)
        $stmt = $pdo->query("SELECT * FROM customer LIMIT 1");
        $sample = $stmt->fetch();

        $col_id = 'MemberId';
        $col_pass = 'MemberPass';
        $col_name = 'FullName';
        $col_cibil = 'CibilStatus';

        if ($sample) {
            $cols = array_keys($sample);

            // Function for case-insensitive column search
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

        file_put_contents($log_file, "[$timestamp] Attempting login for $member_id using ID column: $col_id\n", FILE_APPEND);

        $stmt = $pdo->prepare("SELECT * FROM customer WHERE $col_id = ?");
        $stmt->execute([$member_id]);
        $member = $stmt->fetch();

        $authenticated = false;
        if ($member) {
            // Check password - legacy often uses plain text
            if ($member[$col_pass] === $password) {
                $authenticated = true;
            } elseif (password_verify($password, $member[$col_pass])) {
                $authenticated = true;
            } else {
                file_put_contents($log_file, "[$timestamp] Fail: Password mismatch for $member_id. Input: $password, DB: " . substr($member[$col_pass], 0, 3) . "...\n", FILE_APPEND);
            }
        } else {
            file_put_contents($log_file, "[$timestamp] Fail: Member $member_id not found in table using column $col_id\n", FILE_APPEND);
        }

        if ($authenticated) {
            // Migration / Session handling
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
            file_put_contents($log_file, "[$timestamp] Success: $member_id logged in\n", FILE_APPEND);
            redirect('index.php');
        } else {
            $error = "Invalid Member ID or Password.";
        }

    } catch (PDOException $e) {
        $error = "Auth System Error. Please contact admin.";
        file_put_contents($log_file, "[$timestamp] DB Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width: 500px;">
    <div class="glass-card">
        <h2 class="neon-text">Burfee Member Login</h2>
        <p style="color: #aaa; margin-bottom: 20px; font-size: 0.9rem;">Access the SolarShop using your legacy Burfee Member ID.</p>

        <?php if ($error): ?>
            <p style="color: #ff4d4d; margin-bottom: 15px; font-weight: bold;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Member ID</label>
                <input type="text" name="member_id" required placeholder="Enter Member ID" style="width: 100%; padding: 12px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" required placeholder="Enter Password" style="width: 100%; padding: 12px; border-radius: 5px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.1); color: white;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px;">Authenticate Member</button>
        </form>

        <p style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
            <a href="login.php" style="color: var(--neon-blue);">Back to Standard Login</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
