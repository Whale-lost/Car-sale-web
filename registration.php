<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db_connection.php';

    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $errors = [];

    if (!preg_match('/^[A-Za-z ]+$/', $name))
        $errors[] = "Name can only contain letters and spaces";
    if (!preg_match('/^[A-Za-z0-9 ]+$/', $address))
        $errors[] = "Address can only contain letters, numbers and spaces";
    if (!preg_match('/^1[3-9]\d{9}$/', $phone))
        $errors[] = "Invalid Chinese phone number format";
    if (substr_count($email, '@') != 1)
        $errors[] = "Email must contain exactly one '@' symbol";
    $domain = substr($email, strrpos($email, '.'));
    if (!in_array($domain, ['.com', '.cn']))
        $errors[] = "Email must end with .com or .cn";
    if (!preg_match('/^[A-Za-z0-9]{6,}$/', $username))
        $errors[] = "Username must be at least 6 alphanumeric characters";
    if (!preg_match('/^[A-Za-z0-9]{6,}$/', $password))
        $errors[] = "Password must be at least 6 alphanumeric characters";

    if (empty($errors)) {
        $check = $conn->prepare("SELECT seller_id FROM sellers WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error_msg = "Username or email already registered";
        } else {
            $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO sellers (name, address, phone, email, username, password) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("ssssss", $name, $address, $phone, $email, $username, $hashed_pwd);
            if ($insert->execute()) {
                $new_seller_id = $conn->insert_id;
                $_SESSION['seller_id'] = $new_seller_id;
                $_SESSION['username'] = $username;
                header("Location: seller.php");
                exit;
            } else {
                $error_msg = "Registration failed, please try again later";
            }
        }
        $check->close();
    } else {
        $error_msg = implode(", ", $errors);
    }

    if (isset($error_msg)) {
        header("Location: registration.php?error=" . urlencode($error_msg));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>VanCar | Sign Up · Join Electric Mobility</title>
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

        .register-section {
            flex: 1;
            padding: 50px 24px 70px;
            display: flex;
            justify-content: center;
        }

        .glass-form-card {
            max-width: 780px;
            width: 100%;
            padding: 40px 38px;
            border-radius: 48px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            box-shadow: 0 20px 40px -18px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(169, 198, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .form-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(125deg, #0e405a, #266e94);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #3d7897;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px 28px;
        }

        .full-width {
            grid-column: span 2;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .input-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1c5a78;
            letter-spacing: 0.3px;
        }

        .required-star {
            color: #e06c6c;
            margin-left: 2px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 18px;
            font-size: 0.95rem;
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
            font-size: 0.85rem;
        }

        .error-message {
            font-size: 0.7rem;
            color: #dc2626;
            margin-top: 4px;
            margin-left: 12px;
            display: none;
        }

        .input-group.error input {
            border-color: #dc2626;
        }

        .submit-area {
            margin-top: 36px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .submit-btn {
            min-width: 200px;
            font-size: 1rem;
            padding: 14px 32px;
        }

        .login-redirect {
            font-size: 0.85rem;
            color: #3a7897;
        }

        .login-redirect a {
            color: #1f6b9b;
            text-decoration: none;
            font-weight: 600;
            margin-left: 6px;
        }

        .login-redirect a:hover {
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

        .global-error {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 40px;
            background: rgba(255, 100, 100, 0.15);
            color: #b13e3e;
            border: 1px solid #ffa1a1;
            font-size: 0.85rem;
        }

        @media (max-width: 720px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .full-width {
                grid-column: span 1;
            }

            .glass-form-card {
                padding: 28px 20px;
            }

            .container {
                padding: 0 20px;
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

        .glass-form-card {
            animation: fadeSlideUp 0.5s ease-out;
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(16px);
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
                    <a href="login.php" class="nav-link">Login</a>
                </div>
            </nav>
        </div>

        <div class="register-section">
            <div class="glass-form-card">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Join VanCar and start your electric journey</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="global-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <form id="registerForm" action="registration.php" method="POST">
                    <div class="form-grid">
                        <!-- Full Name (now name="name") -->
                        <div class="input-group" id="group-name">
                            <label>Full Name <span class="required-star">*</span> <span style="font-weight: normal; font-size: 0.7rem;">(letters & spaces only)</span></label>
                            <input type="text" id="fullname" name="name" placeholder="e.g., Li Wei" autocomplete="name">
                            <div class="error-message" id="error-name">Only letters and spaces allowed</div>
                        </div>
                        <!-- Address -->
                        <div class="input-group" id="group-address">
                            <label>Address <span class="required-star">*</span> <span style="font-weight: normal; font-size: 0.7rem;">(letters, numbers &amp; spaces)</span></label>
                            <input type="text" id="address" name="address" placeholder="e.g., Building 12, Green District" autocomplete="address-line1">
                            <div class="error-message" id="error-address">Only letters, numbers and spaces allowed</div>
                        </div>
                        <!-- Phone Number -->
                        <div class="input-group" id="group-phone">
                            <label>Phone Number <span class="required-star">*</span> <span style="font-weight: normal; font-size: 0.7rem;">(China mobile)</span></label>
                            <input type="tel" id="phone" name="phone" placeholder="13912345678" autocomplete="tel">
                            <div class="error-message" id="error-phone">Enter a valid 11-digit China mobile number (must start with 1, second digit 3-9)</div>
                        </div>
                        <!-- Email -->
                        <div class="input-group" id="group-email">
                            <label>Email Address <span class="required-star">*</span> <span style="font-weight: normal; font-size: 0.7rem;">(.com or .cn)</span></label>
                            <input type="email" id="email" name="email" placeholder="name@example.com" autocomplete="email">
                            <div class="error-message" id="error-email">Email must contain exactly one '@' and end with .com or .cn</div>
                        </div>
                        <!-- Username -->
                        <div class="input-group" id="group-username">
                            <label>Username <span class="required-star">*</span> <span style="font-weight: normal; font-size: 0.7rem;">(min 6 alphanumeric)</span></label>
                            <input type="text" id="username" name="username" placeholder="At least 6 letters or digits" autocomplete="username">
                            <div class="error-message" id="error-username">Username must be at least 6 alphanumeric characters (letters/digits only)</div>
                        </div>
                        <!-- Password -->
                        <div class="input-group" id="group-password">
                            <label>Password <span class="required-star">*</span> <span style="font-weight: normal; font-size: 0.7rem;">(min 6 alphanumeric)</span></label>
                            <input type="password" id="password" name="password" placeholder="At least 6 letters or digits" autocomplete="new-password">
                            <div class="error-message" id="error-password">Password must be at least 6 alphanumeric characters (letters/digits only)</div>
                        </div>
                        <!-- Confirm Password -->
                        <div class="input-group full-width" id="group-confirm">
                            <label>Confirm Password <span class="required-star">*</span></label>
                            <input type="password" id="confirmPwd" name="confirmPwd" placeholder="Re-enter your password">
                            <div class="error-message" id="error-confirm">Passwords do not match</div>
                        </div>
                    </div>

                    <div class="submit-area">
                        <button type="submit" class="btn btn-primary submit-btn">Sign Up →</button>
                        <div class="login-redirect">
                            Already have an account? <a href="login.php">Log in</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
                            <a href="search.php">Search Cars</a>
                            <a href="addcar.php">Sell Your Car</a>
                        </div>
                        <div class="footer-col">
                            <strong>Account</strong>
                            <a href="login.php">Login</a>
                            <a href="registration.php">Register</a>
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

            const interactiveElements = document.querySelectorAll('a, button, .btn, .nav-link, input');
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

        const form = document.getElementById('registerForm');
        const nameInput = document.getElementById('fullname');
        const addressInput = document.getElementById('address');
        const phoneInput = document.getElementById('phone');
        const emailInput = document.getElementById('email');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirmPwd');

        function showError(groupId, errorId, show, customMsg = null) {
            const group = document.getElementById(groupId);
            const errorDiv = document.getElementById(errorId);
            if (show) {
                group.classList.add('error');
                errorDiv.style.display = 'block';
                if (customMsg) errorDiv.innerText = customMsg;
            } else {
                group.classList.remove('error');
                errorDiv.style.display = 'none';
            }
        }

        function validateName(name) { return /^[A-Za-z\s]+$/.test(name); }
        function validateAddress(addr) { return /^[A-Za-z0-9\s]+$/.test(addr); }
        function validatePhone(phone) { return /^1[3-9]\d{9}$/.test(phone); }
        function validateEmail(email) {
            const atCount = (email.match(/@/g) || []).length;
            if (atCount !== 1) return false;
            const lowerEmail = email.toLowerCase();
            return /^[^\s@]+@[^\s@]+\.(com|cn)$/.test(lowerEmail);
        }
        function validateUsername(username) { return /^[A-Za-z0-9]{6,}$/.test(username); }
        function validatePassword(password) { return /^[A-Za-z0-9]{6,}$/.test(password); }

        function validateField(fieldId) {
            switch (fieldId) {
                case 'fullname':
                    const nameVal = nameInput.value.trim();
                    const nameOk = nameVal !== '' && validateName(nameVal);
                    showError('group-name', 'error-name', !nameOk);
                    return nameOk;
                case 'address':
                    const addrVal = addressInput.value.trim();
                    const addrOk = addrVal !== '' && validateAddress(addrVal);
                    showError('group-address', 'error-address', !addrOk);
                    return addrOk;
                case 'phone':
                    const phoneVal = phoneInput.value.trim();
                    const phoneOk = phoneVal !== '' && validatePhone(phoneVal);
                    showError('group-phone', 'error-phone', !phoneOk);
                    return phoneOk;
                case 'email':
                    const emailVal = emailInput.value.trim();
                    const emailOk = emailVal !== '' && validateEmail(emailVal);
                    showError('group-email', 'error-email', !emailOk);
                    return emailOk;
                case 'username':
                    const userVal = usernameInput.value.trim();
                    const userOk = userVal !== '' && validateUsername(userVal);
                    showError('group-username', 'error-username', !userOk);
                    return userOk;
                case 'password':
                    const pwdVal = passwordInput.value;
                    const pwdOk = pwdVal !== '' && validatePassword(pwdVal);
                    showError('group-password', 'error-password', !pwdOk);
                    if (confirmInput.value.length > 0) validateField('confirmPwd');
                    return pwdOk;
                case 'confirmPwd':
                    const pwd = passwordInput.value;
                    const confirm = confirmInput.value;
                    const matchOk = (pwd === confirm) && pwd !== '';
                    showError('group-confirm', 'error-confirm', !matchOk);
                    return matchOk;
                default: return true;
            }
        }

        nameInput.addEventListener('blur', () => validateField('fullname'));
        addressInput.addEventListener('blur', () => validateField('address'));
        phoneInput.addEventListener('blur', () => validateField('phone'));
        emailInput.addEventListener('blur', () => validateField('email'));
        usernameInput.addEventListener('blur', () => validateField('username'));
        passwordInput.addEventListener('blur', () => validateField('password'));
        confirmInput.addEventListener('blur', () => validateField('confirmPwd'));

        function validateAll() {
            return validateField('fullname') && validateField('address') && validateField('phone') &&
                   validateField('email') && validateField('username') && validateField('password') && validateField('confirmPwd');
        }

        form.addEventListener('submit', (e) => {
            if (!validateAll()) {
                e.preventDefault();
                const hint = document.createElement('div');
                hint.innerText = 'Please fix the errors above';
                hint.style.cssText = 'position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:#b13e3e; color:white; padding:8px 20px; border-radius:40px; font-size:0.8rem; z-index:10000;';
                document.body.appendChild(hint);
                setTimeout(() => hint.remove(), 2000);
            }
        });

        let lightShift = 0;
        function ambientGlow() {
            lightShift += 0.003;
            const xPos = 35 + Math.sin(lightShift) * 12;
            const yPos = 45 + Math.cos(lightShift * 0.7) * 15;
            document.body.style.background = `radial-gradient(circle at ${xPos}% ${yPos}%, #eef5ff, #e1edff, #d6e5ff)`;
            requestAnimationFrame(ambientGlow);
        }
        ambientGlow();
    </script>
</body>

</html>
