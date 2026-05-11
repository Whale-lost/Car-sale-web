<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db_connection.php';
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT seller_id, username, password FROM sellers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($seller_id, $db_user, $hashed_pwd);
        $stmt->fetch();
        if (password_verify($password, $hashed_pwd)) {
            $_SESSION['seller_id'] = $seller_id;
            $_SESSION['username'] = $db_user;
            header("Location: seller.php");
            exit;
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Username not found";
    }
    header("Location: login.php?error=" . urlencode($error));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>VanCar | Login · Electric Mobility</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            cursor: none;
        }

        body,
        a,
        button,
        input,
        .btn {
            cursor: none;
        }

        body {
            background: radial-gradient(circle at 25% 20%, #eef5ff, #e1edff);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, sans-serif;
            color: #0a2a38;
            line-height: 1.5;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .cursor-follower {
            position: fixed;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(169, 198, 255, 0.45) 0%, rgba(197, 217, 255, 0.3) 60%, rgba(169, 198, 255, 0) 90%);
            backdrop-filter: blur(2px);
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: transform 0.08s linear;
            will-change: left, top;
            mix-blend-mode: soft-light;
            box-shadow: 0 0 12px rgba(169, 198, 255, 0.6);
            border: 1px solid rgba(169, 198, 255, 0.5);
        }

        .cursor-follower::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 8px;
            height: 8px;
            background: #b8d0ff;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.8;
            box-shadow: 0 0 6px #c5d9ff;
        }

        html {
            scroll-behavior: smooth;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
            width: 100%;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            flex-wrap: wrap;
            gap: 20px;
            border-bottom: 1px solid rgba(169, 198, 255, 0.5);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .logo-icon {
            background: linear-gradient(135deg, #c5d9ff, #a9c6ff);
            width: 48px;
            height: 48px;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 18px -8px rgba(169, 198, 255, 0.5);
            transition: transform 0.25s ease;
        }

        .logo-icon svg {
            width: 32px;
            height: 32px;
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.05));
        }

        .logo-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            background: linear-gradient(120deg, #1a4970, #2b6a9e);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .logo-text span {
            font-size: 0.75rem;
            color: #3a6a85;
            letter-spacing: 1px;
            font-weight: 500;
        }

        .nav-links {
            display: flex;
            gap: 28px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-link {
            text-decoration: none;
            font-weight: 600;
            color: #174f6b;
            transition: all 0.25s;
            padding: 8px 6px;
            font-size: 1rem;
            position: relative;
        }

        .nav-link:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0%;
            height: 2px;
            background: linear-gradient(90deg, #b8d0ff, #a9c6ff);
            transition: width 0.3s ease;
        }

        .nav-link:hover:after {
            width: 100%;
        }

        .nav-link:hover {
            color: #1a5a82;
            transform: translateY(-1px);
        }

        .btn {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            border: none;
            background: transparent;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            cursor: none;
        }

        .btn-primary {
            background: linear-gradient(105deg, #b8d0ff, #a9c6ff);
            color: #133c55;
            box-shadow: 0 6px 14px rgba(169, 198, 255, 0.45);
            font-weight: 700;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px -8px rgba(169, 198, 255, 0.6);
            background: linear-gradient(105deg, #a9c6ff, #9bb9f0);
            color: #0f344c;
        }

        .login-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
        }

        .glass-login-card {
            max-width: 480px;
            width: 100%;
            padding: 42px 38px;
            border-radius: 48px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            box-shadow: 0 20px 40px -18px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(169, 198, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header h2 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(125deg, #0e405a, #266e94);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #3d7897;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .error-message {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 40px;
            background: rgba(255, 100, 100, 0.15);
            color: #b13e3e;
            border: 1px solid #ffa1a1;
            font-size: 0.85rem;
        }

        .input-group {
            margin-bottom: 24px;
        }

        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1c5a78;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 18px;
            font-size: 1rem;
            background: #ffffff;
            border: 1px solid rgba(169, 198, 255, 0.8);
            border-radius: 32px;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
            color: #12445f;
            font-weight: 500;
        }

        .input-group input:focus {
            border-color: #a9c6ff;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(169, 198, 255, 0.3);
        }

        .input-group input::placeholder {
            color: #94aec7;
            font-weight: 400;
            font-size: 0.9rem;
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            font-size: 0.8rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #2c6e8f;
        }

        .checkbox-wrapper input {
            width: 16px;
            height: 16px;
            accent-color: #a9c6ff;
            margin: 0;
        }

        .forgot-link {
            color: #3a7ca8;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #1e6b9b;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 24px;
            cursor: none;
        }

        .signup-text {
            text-align: center;
            font-size: 0.85rem;
            color: #3a7897;
        }

        .signup-text a {
            color: #1f6b9b;
            text-decoration: none;
            font-weight: 600;
            margin-left: 6px;
        }

        .signup-text a:hover {
            text-decoration: underline;
            color: #0e5a87;
        }

        .footer {
            background: #0e2f3b;
            color: #d9ecf2;
            padding: 32px 0 24px;
            border-top-left-radius: 32px;
            border-top-right-radius: 32px;
            margin-top: auto;
            box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.05);
            border-top: 1px solid rgba(169, 198, 255, 0.2);
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 32px;
        }

        .footer-logo h3 {
            font-size: 1.5rem;
            color: #c0e4f2;
        }

        .footer-logo p {
            color: #b3d4e3;
            font-size: 0.8rem;
            margin-top: 8px;
        }

        .footer-links {
            display: flex;
            gap: 48px;
            flex-wrap: wrap;
        }

        .footer-col strong {
            color: #d3ecf5;
            font-weight: 700;
        }

        .footer-col a {
            display: block;
            color: #b3d4e3;
            text-decoration: none;
            margin: 6px 0;
            transition: 0.2s;
            font-size: 0.85rem;
        }

        .footer-col a:hover {
            color: #9bc0ff;
            transform: translateX(4px);
            text-shadow: 0 0 2px rgba(169, 198, 255, 0.5);
        }

        .copyright {
            text-align: center;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #2c596b;
            font-size: 0.75rem;
            color: #b0cfdf;
        }

        @media (max-width: 680px) {
            .container {
                padding: 0 20px;
            }

            .glass-login-card {
                padding: 32px 24px;
            }

            .login-header h2 {
                font-size: 1.7rem;
            }

            .navbar {
                flex-direction: column;
            }

            .footer-inner {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .footer-links {
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .cursor-follower {
                display: none;
            }

            body,
            a,
            button,
            input {
                cursor: auto;
            }
        }

        .glass-login-card {
            animation: subtleAppear 0.5s ease-out;
        }

        @keyframes subtleAppear {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="cursor-follower" id="cursorFollower"></div>

    <main style="flex: 1; display: flex; flex-direction: column;">
        <div class="container">
            <nav class="navbar">
                <div class="logo-area">
                    <div class="logo-icon">
                        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 2L6 12L9 22L16 28L23 22L26 12L16 2Z" fill="white" stroke="#a9c6ff"
                                stroke-width="1.2" stroke-linejoin="round" />
                            <path d="M20 15L14 19M12 14L18 10" stroke="#7fa5e0" stroke-width="1.5"
                                stroke-linecap="round" />
                            <circle cx="16" cy="16" r="3" fill="#8eb2ee" stroke="white" stroke-width="1" />
                            <path d="M16 22 L16 28 M12 25 L20 25" stroke="#9bbcf5" stroke-width="1.2" />
                        </svg>
                    </div>
                    <div class="logo-text">
                        <h1>VanCar</h1>
                        <span>Electrified · Certified · Pre-owned</span>
                    </div>
                </div>
                <div class="nav-links">
                    <a href="home.php" class="nav-link">Home</a>
                    <a href="registration.php" class="nav-link">Registration</a>
                </div>
            </nav>
        </div>

        <!-- Login Section -->
        <div class="login-section">
            <div class="glass-login-card">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to access your dashboard</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required
                            autocomplete="username">
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="··········" required
                            autocomplete="current-password">
                    </div>
                    <div class="login-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox"> Remember me
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary login-btn">Log In →</button>
                    <div class="signup-text">
                        Don't have an account?<a href="registration.php">Create account</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="footer-inner">
                    <div class="footer-logo">
                        <h3>VanCar</h3>
                        <p>Clean pre-owned electric mobility.<br>Drive with purpose.</p>
                    </div>
                    <div class="footer-links">
                        <div class="footer-col">
                            <strong>Explore</strong>
                            <a href="home.php">Home</a>
                            <a href="registration.php">Registration</a>
                        </div>
                        <div class="footer-col">
                            <strong>Support</strong>
                            <a href="#">FAQ</a>
                            <a href="#">Warranty</a>
                            <a href="#">Contact</a>
                        </div>
                    </div>
                </div>
                <div class="copyright">
                    © 2025 VanCar — Sustainable pre-owned electric & hybrid vehicles. All rights reserved.
                </div>
            </div>
        </footer>
    </main>

    <script>
        const cursor = document.getElementById('cursorFollower');
        if (cursor) {
            let mouseX = 0, mouseY = 0;
            let cursorX = 0, cursorY = 0;
            document.addEventListener('mousemove', (e) => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });
            function animateCursor() {
                cursorX += (mouseX - cursorX) * 0.2;
                cursorY += (mouseY - cursorY) * 0.2;
                cursor.style.left = cursorX + 'px';
                cursor.style.top = cursorY + 'px';
                requestAnimationFrame(animateCursor);
            }
            animateCursor();

            const interactiveElements = document.querySelectorAll('a, button, .btn, .nav-link, input, .forgot-link');
            interactiveElements.forEach(el => {
                el.addEventListener('mouseenter', () => {
                    cursor.style.transform = 'translate(-50%, -50%) scale(1.35)';
                    cursor.style.background = 'radial-gradient(circle, rgba(169,198,255,0.6) 0%, rgba(197,217,255,0.35) 70%)';
                    cursor.style.backdropFilter = 'blur(4px)';
                });
                el.addEventListener('mouseleave', () => {
                    cursor.style.transform = 'translate(-50%, -50%) scale(1)';
                    cursor.style.background = 'radial-gradient(circle, rgba(169,198,255,0.45) 0%, rgba(197,217,255,0.25) 60%, rgba(169,198,255,0) 90%)';
                    cursor.style.backdropFilter = 'blur(2px)';
                });
            });
        }

        let lightShift = 0;
        function ambientGlow() {
            lightShift += 0.003;
            const xPos = 35 + Math.sin(lightShift) * 12;
            const yPos = 45 + Math.cos(lightShift * 0.7) * 15;
            document.body.style.background = `radial-gradient(circle at ${xPos}% ${yPos}%, #eef5ff, #e1edff, #d6e5ff)`;
            requestAnimationFrame(ambientGlow);
        }
        ambientGlow();

        const forgotLink = document.querySelector('.forgot-link');
        if (forgotLink) {
            forgotLink.addEventListener('click', (e) => {
                e.preventDefault();
                alert('Password recovery demo. Please contact support.');
            });
        }
    </script>
</body>

</html>
