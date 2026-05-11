<?php

require_once 'db_connection.php';

$model = isset($_GET['model']) ? trim($_GET['model']) : '';
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

$results = [];
$sql = "SELECT c.*, s.name as seller_name FROM cars c JOIN sellers s ON c.seller_id = s.seller_id WHERE 1=1";
$params = [];
$types = "";

if (!empty($model)) {
    $sql .= " AND c.model LIKE ?";
    $params[] = "%$model%";
    $types .= "s";
}
if ($year > 0) {
    $sql .= " AND c.year = ?";
    $params[] = $year;
    $types .= "i";
}
$sql .= " ORDER BY c.add_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_obj = $stmt->get_result();
$results = $result_obj->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>VanCar | Search Electric Vehicles</title>
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

        .search-section {
            padding: 40px 24px 30px;
        }

        .glass-search-card {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 38px;
            border-radius: 48px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            box-shadow: 0 20px 40px -18px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(169, 198, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .search-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .search-header h2 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(125deg, #0e405a, #266e94);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 6px;
        }

        .search-header p {
            color: #3d7897;
            font-size: 0.9rem;
        }

        .search-form {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 40px;
        }

        .search-input-group {
            flex: 1;
            min-width: 200px;
        }

        .search-input-group input {
            width: 100%;
            padding: 14px 20px;
            font-size: 1rem;
            background: #ffffff;
            border: 1px solid rgba(169, 198, 255, 0.8);
            border-radius: 48px;
            outline: none;
            color: #12445f;
            font-family: inherit;
        }

        .search-input-group input:focus {
            border-color: #a9c6ff;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(169, 198, 255, 0.3);
        }

        .search-input-group input::placeholder {
            color: #94aec7;
        }

        .search-btn {
            padding: 14px 32px;
            border-radius: 48px;
        }

        /* Results grid (replaces original list+detail) */
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 28px;
            margin-top: 20px;
        }

        .car-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 32px;
            padding: 20px;
            transition: all 0.3s;
            border: 1px solid rgba(169, 198, 255, 0.5);
            backdrop-filter: blur(4px);
        }

        .car-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.15);
            border-color: #a9c6ff;
        }

        .car-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 24px;
            background: #eef5ff;
        }

        .car-card h3 {
            font-size: 1.3rem;
            margin: 12px 0 6px;
            color: #1c5a78;
        }

        .car-details {
            font-size: 0.85rem;
            color: #3d7897;
            margin: 8px 0;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e729c;
            margin: 8px 0;
        }

        .no-results {
            text-align: center;
            padding: 60px;
            color: #3d7897;
            font-size: 1.1rem;
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

        @media (max-width: 780px) {
            .glass-search-card {
                padding: 24px 20px;
            }

            .container {
                padding: 0 20px;
            }

            .navbar {
                flex-direction: column;
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
    </style>
</head>

<body>
    <div class="cursor-follower" id="cursorFollower"></div>

    <main style="flex:1">
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
                    <a href="seller.php" class="nav-link">Seller</a>
                    <a href="addcar.php" class="nav-link">Add Car</a>
                </div>
            </nav>
        </div>

        <div class="search-section">
            <div class="glass-search-card">
                <div class="search-header">
                    <h2>Find Your Electric Drive</h2>
                    <p>Search by model or year — discover premium pre-owned EVs</p>
                </div>
                <form method="GET" action="search.php" class="search-form">
                    <div class="search-input-group">
                        <input type="text" name="model" placeholder="Model (e.g., Tesla Model 3)"
                            value="<?php echo htmlspecialchars($model); ?>" autocomplete="off">
                    </div>
                    <div class="search-input-group">
                        <input type="number" name="year" placeholder="Year (e.g., 2022)"
                            value="<?php echo $year > 0 ? $year : ''; ?>" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary search-btn">Search →</button>
                </form>

                <div class="results-grid">
                    <?php if (count($results) > 0): ?>
                    <?php foreach ($results as $car): ?>
                    <div class="car-card">
                        <?php if (!empty($car['image']) && file_exists($car['image'])): ?>
                        <img src="<?php echo htmlspecialchars($car['image']); ?>"
                            alt="<?php echo htmlspecialchars($car['model']); ?>">
                        <?php else: ?>
                        <img src="uploads/placeholder.jpg" alt="Car Image" style="background: #d9ecf5;">
                        <?php endif; ?>
                        <h3>
                            <?php echo htmlspecialchars($car['model']); ?>
                        </h3>
                        <div class="car-details">
                            <span>
                                <?php echo $car['year']; ?>
                            </span>
                            <span>
                                <?php echo htmlspecialchars($car['colour']); ?>
                            </span>
                            <span>
                                <?php echo htmlspecialchars($car['location']); ?>
                            </span>
                        </div>
                        <div class="price">$
                            <?php echo number_format($car['price'], 2); ?>
                        </div>
                        <p style="font-size:0.8rem; color:#3a7897;">Seller:
                            <?php echo htmlspecialchars($car['seller_name']); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="no-results" style="grid-column: 1/-1;">No cars found. Try a different model or year.
                    </div>
                    <?php endif; ?>
                </div>
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
            const interactive = document.querySelectorAll('a, button, .car-card, .btn, .nav-link');
            interactive.forEach(el => {
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
    </script>
</body>

</html>
