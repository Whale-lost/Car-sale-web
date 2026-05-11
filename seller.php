<?php
session_start();
if (!isset($_SESSION['seller_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connection.php';

$seller_id = $_SESSION['seller_id'];
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM cars WHERE seller_id = ? ORDER BY add_date DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$my_cars = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>VanCar | Seller Dashboard · Manage Your EVs</title>
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
        .btn,
        .car-zigzag-item {
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

        .btn-outline {
            border: 2px solid #a9c6ff;
            color: #1b5472;
            background: rgba(169, 198, 255, 0.1);
            backdrop-filter: blur(2px);
        }

        .btn-outline:hover {
            background: rgba(169, 198, 255, 0.2);
            border-color: #b8d0ff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(169, 198, 255, 0.3);
            color: #10435e;
        }

        .logout-btn {
            background: rgba(169, 198, 255, 0.15);
            border: 1px solid #a9c6ff;
            color: #1b5472;
        }

        .logout-btn:hover {
            background: rgba(169, 198, 255, 0.3);
        }

        .jump-cards {
            margin: 48px 0 32px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .jump-card {
            background: linear-gradient(125deg, #ffffff, #f5faff);
            border-radius: 48px;
            padding: 40px 36px;
            text-align: center;
            box-shadow: 0 20px 35px -16px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(169, 198, 255, 0.6);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .jump-card:hover {
            transform: translateY(-6px);
            background: #ffffff;
            border-color: #c5d9ff;
            box-shadow: 0 28px 40px -18px rgba(0, 0, 0, 0.18);
        }

        .jump-card h2 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(125deg, #0e405a, #266e94);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 12px;
        }

        .jump-card p {
            font-size: 1rem;
            color: #3d7897;
            margin-bottom: 24px;
        }

        .jump-btn {
            font-size: 1.1rem;
            padding: 14px 36px;
            display: inline-block;
        }

        .jump-card:first-child {
            border-bottom: 3px solid #a9c6ff;
        }

        .jump-card:last-child {
            border-top: 3px solid #c5d9ff;
        }

        .zigzag-section {
            margin: 60px 0 40px;
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: #1c5a78;
            margin-bottom: 40px;
        }

        .zigzag-container {
            display: flex;
            flex-direction: column;
            gap: 48px;
        }

        .zigzag-item {
            display: flex;
            align-items: center;
            gap: 40px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            border-radius: 48px;
            padding: 32px;
            transition: all 0.35s ease;
            border: 1px solid rgba(169, 198, 255, 0.5);
            box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.08);
        }

        .zigzag-item:hover {
            transform: translateY(-6px);
            background: #ffffff;
            border-color: #c5d9ff;
            box-shadow: 0 22px 30px -14px rgba(0, 0, 0, 0.12);
        }

        .zigzag-img {
            flex: 1;
            min-width: 160px;
            background: linear-gradient(145deg, #e1edff, #d6e5ff);
            border-radius: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .zigzag-img img {
            width: 100%;
            max-width: 200px;
            height: auto;
            border-radius: 24px;
            object-fit: cover;
        }

        .zigzag-content {
            flex: 1.5;
        }

        .zigzag-content h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #12445f;
            margin-bottom: 12px;
        }

        .car-meta {
            display: flex;
            gap: 24px;
            margin: 12px 0;
            font-size: 0.9rem;
            color: #3a7897;
            flex-wrap: wrap;
        }

        .car-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1f6b9b;
            margin: 16px 0 8px;
        }

        .view-details {
            display: inline-block;
            margin-top: 12px;
            color: #2b6e9e;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: 0.2s;
        }

        .view-details:hover {
            border-bottom-color: #a9c6ff;
            color: #0e5a87;
        }

        .zigzag-item:nth-child(even) {
            flex-direction: row-reverse;
        }

        .success-message {
            text-align: center;
            background: rgba(100, 200, 100, 0.15);
            color: #2c6e2c;
            border: 1px solid #8bc88b;
            border-radius: 40px;
            padding: 12px;
            margin: 20px auto;
            max-width: 600px;
        }

        /* Footer */
        .footer {
            background: #0e2f3b;
            color: #d9ecf2;
            padding: 32px 0 24px;
            border-top-left-radius: 32px;
            border-top-right-radius: 32px;
            margin-top: 60px;
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

        @media (max-width: 880px) {
            .navbar {
                flex-direction: column;
            }

            .container {
                padding: 0 24px;
            }

            .zigzag-item {
                flex-direction: column !important;
                text-align: center;
            }

            .jump-card h2 {
                font-size: 1.6rem;
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

            .zigzag-img img {
                max-width: 140px;
            }
        }

        .jump-card,
        .zigzag-item {
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

        .welcome-badge {
            background: rgba(169, 198, 255, 0.3);
            display: inline-block;
            padding: 6px 20px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #1a6283;
            margin-bottom: 16px;
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
                    <a href="logout.php" class="btn btn-outline logout-btn">Logout →</a>
                </div>
            </nav>

            <?php if (isset($_GET['msg'])): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
            <?php endif; ?>

            <div class="jump-cards">
                <div class="jump-card" id="searchJumpCard">
                    <div class="welcome-badge">Seller Dashboard · Hello
                        <?php echo htmlspecialchars($username); ?>
                    </div>
                    <h2>Explore Full Inventory</h2>
                    <p>Search thousands of premium electric vehicles, filter by model, year, price and more.</p>
                    <a href="search.php" class="btn btn-primary jump-btn">Go to Search →</a>
                </div>

                <div class="jump-card" id="addCarJumpCard">
                    <h2>List Your Vehicle</h2>
                    <p>Add a new EV to your seller portfolio. Reach eco-conscious buyers instantly.</p>
                    <a href="addcar.php" class="btn btn-primary jump-btn">Add Car →</a>
                </div>
            </div>

            <div class="zigzag-section">
                <h2 class="section-title">Your Listed Cars</h2>
                <div class="zigzag-container" id="zigzagContainer">
                    <?php if (count($my_cars) > 0): ?>
                    <?php foreach ($my_cars as $index => $car): ?>
                    <div class="zigzag-item" data-id="<?php echo $car['car_id']; ?>">
                        <div class="zigzag-img">
                            <?php if (!empty($car['image']) && file_exists($car['image'])): ?>
                            <img src="<?php echo htmlspecialchars($car['image']); ?>"
                                alt="<?php echo htmlspecialchars($car['model']); ?>">
                            <?php else: ?>
                            <svg width="200" height="130" viewBox="0 0 200 100" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect x="10" y="30" width="160" height="45" rx="12" fill="#CEE9FF" stroke="#a9c6ff"
                                    stroke-width="1.5" />
                                <circle cx="45" cy="68" r="14" fill="#1F4E6F" stroke="#2c7da0" />
                                <circle cx="145" cy="68" r="14" fill="#1F4E6F" stroke="#2c7da0" />
                                <path d="M40 25 L90 18 L130 25 L155 38 L35 38 L40 25Z" fill="#A0CDE6" />
                            </svg>
                            <?php endif; ?>
                        </div>
                        <div class="zigzag-content">
                            <h3>
                                <?php echo htmlspecialchars($car['model']); ?>
                            </h3>
                            <div class="car-meta">
                                <span>📍
                                    <?php echo htmlspecialchars($car['location']); ?>
                                </span>
                                <span>📅
                                    <?php echo $car['year']; ?>
                                </span>
                                <span>🎨
                                    <?php echo htmlspecialchars($car['colour']); ?>
                                </span>
                            </div>
                            <div class="car-price">$
                                <?php echo number_format($car['price'], 2); ?>
                            </div>
                            <a href="#" class="view-details" data-id="<?php echo $car['car_id']; ?>">View details →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div
                        style="text-align: center; padding: 60px; background: rgba(255,255,255,0.7); border-radius: 48px;">
                        <p style="font-size: 1.2rem; color: #3d7897;">You haven't listed any cars yet.</p>
                        <p style="margin-top: 16px;"><a href="addcar.php" class="btn btn-primary">List Your First Car
                                →</a></p>
                    </div>
                    <?php endif; ?>
                </div>
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

            const interactiveElements = document.querySelectorAll('a, button, .btn, .zigzag-item, .jump-card');
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

        const viewDetailsLinks = document.querySelectorAll('.view-details');
        viewDetailsLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const carId = link.getAttribute('data-id');
                alert("Car details (ID: " + carId + ") – Full info coming soon.");
            });
        });
    </script>
</body>

</html>
