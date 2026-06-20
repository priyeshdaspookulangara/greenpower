<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error_msg = '';
$success_msg = '';

if (isset($_GET['logged_out'])) {
    $success_msg = "You have been successfully signed out.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $member_id = trim($_POST['member_id'] ?? '');
    $password  = trim($_POST['password'] ?? '');

    // The script expects the BC prefix to be stripped by JS, but let's be safe server-side too
    $member_id = preg_replace('/^BC/i', '', $member_id);

    if ($member_id === '' || $password === '') {
        $error_msg = "Please enter both Member ID and Password.";
    } else {
        // Search in customer table
        $stmt = $pdo->prepare("SELECT * FROM customer WHERE MemberId = :member_id LIMIT 1");
        // We need to check both BC prefix and without it because the DB might have it or not
        // Based on the JS, it strips it. So we search for what's in the DB.
        // Let's assume the DB might have 'BC1001' or '1001'
        $stmt->execute(['member_id' => 'BC' . $member_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
             // Try without prefix
             $stmt->execute(['member_id' => $member_id]);
             $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($user && $user['MemberPass'] === $password) {
            $_SESSION['user_id'] = 'BURFEE_' . $user['id']; // Prefix to distinguish from regular users
            $_SESSION['role'] = 'customer';
            $_SESSION['name'] = $user['name'];
            $_SESSION['MemberId'] = $user['MemberId'];
            $_SESSION['logged_in'] = true;

            redirect('dashboard.php');
        } else {
            $error_msg = "Invalid Member ID or Password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Member Login | Burfee Cart</title>
  <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * { padding: 0; margin: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; overflow: hidden; background-color: #3f2259; display: flex; align-items: center; justify-content: center; height: 100vh; }
    .login-content { background: #fff; border-radius: 24px; padding: 40px; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2); border: 1px solid #504793; width: 100%; max-width: 400px; }
    .logo { display: flex; justify-content: center; margin-bottom: 25px; }
    .logo img { height: 60px; width: auto; }
    label { font-size: 0.8rem; font-weight: 600; color: #504793; margin-bottom: 5px; text-transform: uppercase; }
    .input-group-text { background-color: transparent !important; color: #504793 !important; border: 1px solid #d9d9d9; transition: border-color 0.3s ease; }
    .form-control { border: 1px solid #d9d9d9; padding: 10px; color: #333 !important; background-color: transparent !important; transition: border-color 0.3s ease; }
    .btn-login { width: 100%; height: 50px; border-radius: 25px; border: none; background: linear-gradient(90deg, #504793 17%, #4fc2da 98%); color: #fff; font-weight: 600; text-transform: uppercase; margin: 20px 0; transition: 0.3s; }
    .btn-login:hover { opacity: 0.9; transform: translateY(-2px); }
    .back-home { display: block; text-align: center; text-decoration: none; color: #504793; font-size: 0.9rem; }
    .alert { font-size: 0.8rem; border-radius: 10px; }
  </style>
</head>
<body>
  <div class="login-content">
    <form method="post" id="loginForm">
      <div class="logo">
        <h2 style="color: #3f2259; font-weight: 900;">BURFEE CART</h2>
      </div>

      <?php if($error_msg): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
      <?php endif; ?>
      <?php if($success_msg): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
      <?php endif; ?>

      <div class="text-start mb-3">
        <label>MEMBER ID</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" name="member_id" id="member_id" class="form-control" placeholder="Enter Member ID" required>
        </div>
      </div>

      <div class="text-start mb-2 pass-container">
        <label>PASSWORD</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
          <span class="input-group-text eye-toggle" id="togglePassword">
            <i id="eyeIcon" class="fa-solid fa-eye-slash"></i>
          </span>
        </div>
      </div>

      <div class="text-end mb-3">
        <a href="#" style="font-size: 0.75rem; color: #504793; text-decoration: none;">Forgot Password?</a>
      </div>

      <input type="submit" class="btn-login" value="Login">
      <a href="index.php" class="back-home">Back to Home</a>
    </form>
  </div>

  <script>
    const loginForm      = document.getElementById('loginForm');
    const memberIdField  = document.getElementById('member_id');
    const passwordField  = document.getElementById('password');
    const toggleBtn      = document.getElementById('togglePassword');
    const eyeIcon        = document.getElementById('eyeIcon');

    toggleBtn.addEventListener('click', () => {
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
      } else {
        passwordField.type = 'password';
        eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
      }
    });

    loginForm.addEventListener('submit', function () {
      memberIdField.value = memberIdField.value.trim().replace(/^BC/i, '');
    });
  </script>
</body>
</html>
