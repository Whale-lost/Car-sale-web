<?php
session_start();
require_once 'db_connection.php';

// Fetch 3 random cars from database
$random_cars = [];
$sql = "SELECT car_id, model, year, colour, location, price, image FROM cars ORDER BY RAND() LIMIT 3";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $random_cars[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>VanCar | Certified Electrified Vehicles</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            cursor: none;
        }

        body {
            background: radial-gradient(circle at 22% 22%, #f7fdff 0%, #e0efff 40%, #d1e0ff 70%, #c0d6ff 100%);
            background-attachment: fixed;
            background-size: 150% 150%;
            font-family: 'Inter', system-ui, -apple-system, Roboto, sans-serif;
            color: #0a2a38;
            line-height: 1.5;
            overflow-x: hidden;
            min-height: 100vh;
            animation: bgShift 22s infinite linear;
        }

        @keyframes bgShift {
            0% {
                background-position: 0% 0%;
            }

            50% {
                background-position: 10% 15%;
            }

            100% {
                background-position: 0% 0%;
            }
        }

        .cursor-follower {
            position: fixed;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(169, 198, 255, 0.5) 0%, rgba(197, 217, 255, 0.35) 70%, transparent 100%);
            backdrop-filter: blur(3px);
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: transform 0.07s linear;
            will-change: left, top;
            box-shadow: 0 0 18px rgba(169, 198, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .cursor-follower::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 9px;
            height: 9px;
            background: #ffffff;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.9;
            box-shadow: 0 0 8px #c5d9ff;
        }

        html {
            scroll-behavior: smooth;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 0;
            flex-wrap: wrap;
            gap: 20px;
            border-bottom: 1px solid rgba(169, 198, 255, 0.35);
            position: sticky;
            top: 0;
            background: #ffffff;  /* 纯白色背景 */
            z-index: 999;
            transition: all 0.3s ease;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            background: linear-gradient(135deg, #c5d9ff, #a9c6ff);
            width: 50px;
            height: 50px;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 22px -8px rgba(169, 198, 255, 0.45);
            animation: iconFloat 6s infinite ease-in-out;
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-3px);
            }
        }

        .logo-icon svg {
            width: 32px;
            height: 32px;
        }

        .logo-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
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

        .breathing {
            animation: gentleBreathe 3.4s infinite ease-in-out;
        }

        @keyframes gentleBreathe {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.025);
            }

            100% {
                transform: scale(1);
            }
        }

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-link {
            text-decoration: none;
            font-weight: 600;
            color: #174f6b;
            transition: all 0.28s;
            padding: 8px 6px;
            font-size: 1rem;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #ffffff, #b8d0ff);
            transition: width 0.32s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            color: #1a5a82;
            transform: translateY(-1px);
        }

        .btn {
            display: inline-block;
            padding: 13px 30px;
            border-radius: 42px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.32s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            border: none;
            background: transparent;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            100% {
                left: 150%;
            }
        }

        .btn-primary {
            background: linear-gradient(105deg, #b8d0ff, #a9c6ff);
            color: #133c55;
            box-shadow: 0 8px 18px rgba(169, 198, 255, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 28px -8px rgba(169, 198, 255, 0.55);
            background: linear-gradient(105deg, #a9c6ff, #9bb9f0);
        }

        .btn-outline {
            border: 2px solid rgba(169, 198, 255, 0.6);
            color: #1b5472;
            background: rgba(169, 198, 255, 0.12);
            backdrop-filter: blur(3px);
        }

        .btn-outline:hover {
            background: rgba(169, 198, 255, 0.22);
            border-color: #b8d0ff;
            transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(169, 198, 255, 0.3);
        }

        .hero {
            padding: 70px 0 50px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
        }

        .hero-content {
            flex: 1;
            min-width: 280px;
        }

        .hero-badge {
            background: rgba(197, 217, 255, 0.35);
            display: inline-block;
            padding: 7px 18px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #1a6283;
            margin-bottom: 22px;
            backdrop-filter: blur(3px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: badgePulse 4s infinite ease-in-out;
        }

        @keyframes badgePulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.03);
            }
        }

        .hero-content h2 {
            font-size: 3.4rem;
            font-weight: 800;
            line-height: 1.2;
            background: linear-gradient(125deg, #0e405a, #266e94);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 24px;
        }

        .hero-content p {
            font-size: 1.22rem;
            color: #2d6d8c;
            max-width: 560px;
            margin-bottom: 36px;
        }

        .hero-buttons {
            display: flex;
            gap: 22px;
            flex-wrap: wrap;
        }

        .hero-image {
            flex: 0.8;
            min-width: 340px;
            text-align: center;
            position: relative;
        }

        .hero-image svg {
            animation: carFloat 7s infinite ease-in-out;
        }

        @keyframes carFloat {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            25% {
                transform: translateY(-4px) rotate(0.3deg);
            }

            50% {
                transform: translateY(-2px) rotate(0deg);
            }

            75% {
                transform: translateY(-5px) rotate(-0.2deg);
            }
        }

        .soft-glow::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(169, 198, 255, 0.55), transparent 75%);
            border-radius: 50%;
            top: -50px;
            right: -40px;
            z-index: -1;
            filter: blur(60px);
            animation: glowPulse 8s infinite ease-in-out;
        }

        @keyframes glowPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.6;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .featured {
            padding: 70px 0 90px;
        }

        .section-title {
            text-align: center;
            font-size: 2.6rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #12445f;
        }

        .section-sub {
            text-align: center;
            color: #3d7897;
            margin-bottom: 60px;
            font-size: 1.12rem;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 42px;
        }

        .car-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(4px);
            border-radius: 34px;
            padding: 28px;
            transition: all 0.38s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 18px 40px -16px rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(169, 198, 255, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.7);
            position: relative;
            overflow: hidden;
            animation: cardFloat 5s infinite ease-in-out;
        }

        @keyframes cardFloat {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-4px);
            }
        }

        .car-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: lightSweep 6s infinite linear;
        }

        @keyframes lightSweep {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .car-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 35px 48px -20px rgba(169, 198, 255, 0.5), 0 0 0 2px rgba(169, 198, 255, 0.55);
            background: #ffffff;
        }

        .card-img {
            background: linear-gradient(145deg, #f0f7ff, #e6f0ff);
            border-radius: 30px;
            height: 190px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            overflow: hidden;
            position: relative;
        }

        .card-img::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: imgShine 5s infinite linear;
        }

        @keyframes imgShine {
            0% {
                left: -100%;
            }

            100% {
                left: 150%;
            }
        }

        .car-card h3 {
            font-size: 1.65rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #0c3f58;
        }

        .car-details {
            font-size: 0.86rem;
            color: #54829b;
            display: flex;
            gap: 18px;
            margin: 14px 0;
            border-top: 1px solid #d9ecf5;
            padding-top: 14px;
        }

        .price {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1e729c;
            margin: 14px 0;
        }

        .card-link {
            display: inline-block;
            font-weight: 600;
            color: #3478b0;
            text-decoration: none;
            transition: 0.24s;
        }

        .card-link:hover {
            color: #1d6090;
            letter-spacing: 0.4px;
        }

        .footer {
            background: #0e2f3b;
            color: #d9ecf2;
            padding: 52px 0 36px;
            border-top-left-radius: 36px;
            border-top-right-radius: 36px;
            margin-top: 30px;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 44px;
        }

        .footer-logo h3 {
            font-size: 1.8rem;
            color: #c0e4f2;
        }

        .footer-links {
            display: flex;
            gap: 52px;
            flex-wrap: wrap;
        }

        .footer-col a {
            display: block;
            color: #b3d4e3;
            text-decoration: none;
            margin: 9px 0;
            transition: 0.24s;
        }

        .footer-col a:hover {
            color: #9bc0ff;
            transform: translateX(5px);
        }

        .copyright {
            text-align: center;
            margin-top: 52px;
            padding-top: 26px;
            border-top: 1px solid #2c596b;
            font-size: 0.8rem;
        }

        .login-status {
            text-align: center;
            background: rgba(169, 198, 255, 0.25);
            backdrop-filter: blur(8px);
            border-radius: 60px;
            padding: 10px 24px;
            margin: 20px auto 0;
            display: inline-block;
            width: auto;
            font-size: 0.9rem;
            color: #0e405a;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        @media (max-width: 880px) {
            .navbar {
                flex-direction: column;
            }

            .hero h2 {
                font-size: 2.6rem;
            }

            .container {
                padding: 0 24px;
            }
        }

        @media (max-width: 640px) {
            .cursor-follower {
                display: none;
            }

            body {
                cursor: auto;
            }
        }
    </style>
</head>

<body>
    <div class="cursor-follower" id="cursorFollower"></div>

    <main>
        <div class="container">
            <nav class="navbar">
                <div class="logo-area breathing">
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
                    <a href="registration.php" class="nav-link">Registration</a>
                    <a href="login.php" class="nav-link">Login</a>
                </div>
            </nav>

            <div style="display: flex; justify-content: center;">
                <?php if(isset($_SESSION['seller_id'])): ?>
                <div class="login-status">
                    You are logged in as <strong>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </strong>.
                    <a href="seller.php" style="color: #1a5a82; font-weight: 600;">Go to your dashboard</a> |
                    <a href="logout.php" style="color: #1a5a82; font-weight: 600;">Logout</a>
                </div>
                <?php endif; ?>
            </div>

            <section class="hero">
                <div class="hero-content">
                    <div class="hero-badge">Blue Certified · Sustainable Drive</div>
                    <h2>Drive the future,<br>sustainably.</h2>
                    <p>VanCar offers a curated selection of hybrid & electric used cars. Every vehicle undergoes a
                        rigorous 150-point eco-inspection. Quality driving with a smaller footprint.</p>
                    <div class="hero-buttons">
                        <a href="search.php" class="btn btn-primary">Search Cars →</a>
                        <a href="addcar.php" class="btn btn-outline">Sell Your Car</a>
                    </div>
                </div>
                <div class="hero-image soft-glow">
                    <svg width="440" height="270" viewBox="0 0 480 280" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M80 120C80 95 110 80 170 80H310C370 80 400 95 400 120V180C400 205 370 220 310 220H170C110 220 80 205 80 180V120Z"
                            fill="#F0F7FF" stroke="#b8d0ff" stroke-width="3" rx="28" />
                        <circle cx="140" cy="210" r="32" fill="#1A4B6C" stroke="#c5d9ff" stroke-width="3" />
                        <circle cx="340" cy="210" r="32" fill="#1A4B6C" stroke="#c5d9ff" stroke-width="3" />
                        <path d="M170 105 L240 85 L310 105" stroke="#a9c6ff" stroke-width="2.5"
                            stroke-linecap="round" />
                        <rect x="210" y="100" width="80" height="28" rx="10" fill="#C5D9FF" />
                        <path d="M105 155 H85V135H105V155Z" fill="#B8D0FF" />
                        <path d="M375 155 H395V135H375V155Z" fill="#B8D0FF" />
                        <path d="M190 140 H290" stroke="#a9c6ff" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
            </section>

            <section class="featured">
                <h2 class="section-title">Featured Electric Rides</h2>
                <div class="section-sub">Zero compromise, zero emissions — handpicked sustainable choices</div>
                <div class="card-grid">
                    <?php if (count($random_cars) > 0): ?>
                        <?php foreach ($random_cars as $car): ?>
                            <div class="car-card">
                                <div class="card-img">
                                    <?php if (!empty($car['image']) && file_exists($car['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['model']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <!-- 占位 SVG（与原卡片样式保持一致） -->
                                        <svg width="180" height="110" viewBox="0 0 200 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M30 60C30 40 60 30 100 30H170C190 30 200 50 200 65V85C200 100 180 105 140 105H60C20 105 20 85 20 70V60" fill="#ffffff" stroke="#222222" stroke-width="2" rx="16"/>
                                            <path d="M50 45 L150 45" stroke="#0071E3" stroke-width="2.5" stroke-linecap="round"/>
                                            <path d="M60 70 L140 70" stroke="#E5E5E5" stroke-width="1.5"/>
                                            <circle cx="65" cy="100" r="18" fill="#111" stroke="#ccc" stroke-width="1.5"/>
                                            <circle cx="135" cy="100" r="18" fill="#111" stroke="#ccc" stroke-width="1.5"/>
                                            <path d="M40 75 H45" stroke="#0071E3" stroke-width="1.5"/>
                                            <path d="M155 75 H160" stroke="#0071E3" stroke-width="1.5"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <h3><?php echo htmlspecialchars($car['model']); ?></h3>
                                <div class="car-details">
                                    <span><?php echo htmlspecialchars($car['year']); ?> · <?php echo htmlspecialchars($car['colour']); ?></span>
                                    <span><?php echo htmlspecialchars($car['location']); ?></span>
                                </div>
                                <div class="price">$<?php echo number_format($car['price'], 2); ?></div>
                                <a href="#" class="card-link">View details →</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align:center; grid-column:1/-1; color:#54829b;">No cars available at the moment. Please check back later.</p>
                    <?php endif; ?>
                </div>
            </section>

            <div style="display: flex; justify-content: center; gap: 32px; margin: 20px 0 60px;">
                <a href="addcar.php" class="btn btn-outline">Become a Seller →</a>
                <a href="search.php" class="btn btn-primary">Advanced Search Inventory →</a>
            </div>
            <div style="text-align: center; margin-top: -30px; margin-bottom: 40px;">
                <p style="color: #4c7e9b;">Trusted by 2,500+ green drivers | Over 300+ sustainable vehicles available
                </p>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-logo">
                    <h3>VanCar</h3>
                    <p style="margin-top: 12px;">Clean pre-owned cars.<br>For tomorrow's drive.</p>
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

    <script>
        const cursor = document.getElementById('cursorFollower');
        if (cursor) {
            let mouseX = 0, mouseY = 0;
            let cursorX = 0, cursorY = 0;

            document.addEventListener('mousemove', e => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });

            function animateCursor() {
                cursorX += (mouseX - cursorX) * 0.22;
                cursorY += (mouseY - cursorY) * 0.22;
                cursor.style.left = cursorX + 'px';
                cursor.style.top = cursorY + 'px';
                requestAnimationFrame(animateCursor);
            }
            animateCursor();

            const interactive = document.querySelectorAll('a, button, .car-card, .btn, .nav-link');
            interactive.forEach(el => {
                el.onmouseenter = () => {
                    cursor.style.transform = 'translate(-50%, -50%) scale(1.45)';
                    cursor.style.backdropFilter = 'blur(5px)';
                };
                el.onmouseleave = () => {
                    cursor.style.transform = 'translate(-50%, -50%) scale(1)';
                    cursor.style.backdropFilter = 'blur(3px)';
                };
            });
        }

        let lightShift = 0;
        function moveBgGlow() {
            lightShift += 0.0032;
            let x = 35 + Math.sin(lightShift) * 13;
            let y = 45 + Math.cos(lightShift * 0.7) * 16;
            document.body.style.background = `radial-gradient(circle at ${x}% ${y}%, #eef5ff, #e1edff, #d6e5ff)`;
            requestAnimationFrame(moveBgGlow);
        }
        moveBgGlow();
    </script>
</body>

</html>
