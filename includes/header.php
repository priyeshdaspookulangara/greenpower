<?php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolarShop - Engineering Sustainable Excellence</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? '' : 'dark-theme'; ?>">
    <header>
        <nav>
            <div class="logo">
                <a href="index.php" class="logo-text">SolarShop</a>
                <div class="logo-subtext">Sustainable<br>Excellence</div>
            </div>
            <div class="hamburger" id="hamburger">
                <span></span><span></span><span></span>
            </div>
            <ul class="nav-links" id="nav-links">
                <li class="nav-item"><a href="index.php">HOME</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a href="dashboard.php">DASHBOARD</a></li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a href="admin.php">ADMIN</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="cart.php">CART (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
                    </li>
                    <li class="nav-item"><a href="logout.php" class="nav-btn-signup">LOGOUT</a></li>
                <?php else: ?>
                    <li class="nav-item"><a href="index.php#products">PRODUCTS</a></li>
                    <li class="nav-item">
                        <a href="#" onclick="openLoginModal(event)">SIGN IN</a>
                    </li>
                    <li class="nav-item"><a href="register.php" class="nav-btn-signup">SIGN UP</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- CORPORATE LOGIN MODAL -->
    <div class="modal-overlay" id="loginModal">
        <div class="login-modal">
            <span class="close-btn" onclick="closeLoginModal()">&times;</span>

            <div class="modal-logo">
                <div class="logo-text" style="color: var(--brand-blue); font-size: 2.2rem;">SolarShop</div>
            </div>

            <h2>Welcome Back!</h2>
            <p>Login to your member dashboard</p>

            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="submit-btn">Login</button>
            </form>
            <p style="margin-top: 20px; font-size: 0.8rem;">
                Existing Burfee Member? <a href="burfee_login.php" style="color: var(--brand-blue);">Login here</a>
            </p>
        </div>
    </div>

    <script>
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('nav-links');
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                navLinks.classList.toggle('active');
            });
        }

        const loginModal = document.getElementById('loginModal');

        function openLoginModal(e) {
            if(e) e.preventDefault();
            loginModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            loginModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        window.addEventListener('click', (e) => {
            if (e.target === loginModal) {
                closeLoginModal();
            }
        });
    </script>
