<?php

session_start();

if (!isset($_SESSION['seller_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connection.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colour = trim($_POST['colour']);
    $model = trim($_POST['model']);
    $year = intval($_POST['year']);
    $location = trim($_POST['location']);
    $price = floatval($_POST['price']);
    $seller_id = $_SESSION['seller_id'];

    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["car_image"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["car_image"]["tmp_name"]);
    if ($check === false) {
        $error_message = "File is not a valid image";
    } elseif ($_FILES["car_image"]["size"] > 2 * 1024 * 1024) {
        $error_message = "Image exceeds 2MB limit";
    } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $error_message = "Only JPG, JPEG, PNG, GIF allowed";
    } else {
        if (move_uploaded_file($_FILES["car_image"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO cars (seller_id, colour, model, year, location, price, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issisds", $seller_id, $colour, $model, $year, $location, $price, $target_file);
            if ($stmt->execute()) {
                $success_message = "Car posted successfully!";
            } else {
                $error_message = "Database save failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Image upload failed";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>VanCar | Add Vehicle · List Your EV</title>
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
        textarea,
        select,
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

        .btn-outline-light {
            border: 2px solid #a9c6ff;
            color: #1b5472;
            background: rgba(169, 198, 255, 0.1);
            backdrop-filter: blur(2px);
        }

        .btn-outline-light:hover {
            background: rgba(169, 198, 255, 0.2);
            border-color: #b8d0ff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(169, 198, 255, 0.3);
            color: #10435e;
        }

        .add-car-section {
            flex: 1;
            padding: 50px 24px 70px;
            display: flex;
            justify-content: center;
        }

        .glass-form-card {
            max-width: 760px;
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
            gap: 8px;
        }

        .input-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1c5a78;
            letter-spacing: 0.3px;
        }

        .input-group input,
        .input-group select {
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

        .input-group input:focus,
        .input-group select:focus {
            border-color: #a9c6ff;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(169, 198, 255, 0.3);
        }

        .input-group input::placeholder,
        .input-group select {
            color: #94aec7;
        }

        select option {
            background: #ffffff;
            color: #12445f;
        }

        .image-upload-area {
            margin-top: 8px;
        }

        .upload-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            background: rgba(230, 242, 255, 0.6);
            border-radius: 28px;
            padding: 20px;
            border: 1px dashed rgba(169, 198, 255, 0.8);
            transition: all 0.2s;
        }

        .preview-img-container {
            width: 100%;
            min-height: 160px;
            background: #f5faff;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        #imagePreview {
            max-width: 100%;
            max-height: 180px;
            object-fit: contain;
            border-radius: 20px;
            transition: 0.2s;
            display: none;
        }

        .placeholder-preview {
            color: #54829b;
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 32px;
        }

        .upload-label {
            background: rgba(169, 198, 255, 0.15);
            border: 1px solid rgba(169, 198, 255, 0.6);
            border-radius: 40px;
            padding: 10px 20px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1b5472;
            cursor: none;
            transition: 0.2s;
            display: inline-block;
            text-align: center;
        }

        .upload-label:hover {
            background: rgba(169, 198, 255, 0.3);
            border-color: #a9c6ff;
            color: #0e405a;
        }

        input[type="file"] {
            display: none;
        }

        .submit-area {
            margin-top: 36px;
            display: flex;
            justify-content: center;
        }

        .submit-btn {
            min-width: 200px;
            font-size: 1rem;
            padding: 14px 32px;
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
            input,
            select {
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

        .message {
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 40px;
            font-weight: 500;
        }

        .error-msg {
            background: rgba(255, 100, 100, 0.15);
            color: #b13e3e;
            border: 1px solid #ffa1a1;
        }

        .success-msg {
            background: rgba(100, 200, 100, 0.15);
            color: #2c6e2c;
            border: 1px solid #8bc88b;
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
                    <a href="seller.php" class="nav-link">Sellers</a>
                    <a href="search.php" class="nav-link">Search</a>
                    <a href="inventory.php" class="nav-link">Inventory</a>
                </div>
            </nav>
        </div>

        <div class="add-car-section">
            <div class="glass-form-card">
                <div class="form-header">
                    <h2>List Your Electric Vehicle</h2>
                    <p>Fill in the details to list your EV on VanCar</p>
                </div>

                <!-- Display messages -->
                <?php if (!empty($error_message)): ?>
                <div class="message error-msg">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                <div class="message success-msg">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>

                <form id="addCarForm" action="<?php echo htmlspecialchars($_SERVER[" PHP_SELF"]); ?>" method="POST"
                    enctype="multipart/form-data">
                    <div class="form-grid">
                        <!-- Colour -->
                        <div class="input-group">
                            <label>Colour</label>
                            <input type="text" id="colour" name="colour" placeholder="e.g., Aurora Silver" required>
                        </div>
                        <!-- Model -->
                        <div class="input-group">
                            <label>Model</label>
                            <input type="text" id="model" name="model" placeholder="Tesla Model 3, BYD Seal..."
                                required>
                        </div>
                        <!-- Year -->
                        <div class="input-group">
                            <label>Year</label>
                            <select id="year" name="year" required>
                                <option value="">Select year</option>
                                <option>2025</option>
                                <option>2024</option>
                                <option>2023</option>
                                <option>2022</option>
                                <option>2021</option>
                                <option>2020</option>
                                <option>2019</option>
                                <option>2018</option>
                            </select>
                        </div>
                        <!-- Location -->
                        <div class="input-group">
                            <label>Location</label>
                            <input type="text" id="location" name="location"
                                placeholder="City / Dealership (e.g., Shanghai)" required>
                        </div>
                        <!-- Price (USD) -->
                        <div class="input-group">
                            <label>Price (USD)</label>
                            <input type="number" id="price" name="price" placeholder="e.g., 35990" required step="1">
                        </div>
                        <div class="input-group full-width">
                            <label>Car Image</label>
                            <div class="image-upload-area">
                                <div class="upload-preview">
                                    <div class="preview-img-container" id="previewContainer">
                                        <img id="imagePreview" alt="Preview" src="#">
                                        <div class="placeholder-preview" id="placeholderPreview">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#54829b"
                                                stroke-width="1.2">
                                                <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" />
                                                <circle cx="8.5" cy="10.5" r="2.5" stroke="currentColor" />
                                                <path d="M21 15L16 10L5 21" stroke="currentColor"
                                                    stroke-linecap="round" />
                                            </svg>
                                            <span>JPEG, PNG, GIF (Max 2MB)</span>
                                        </div>
                                    </div>
                                    <label for="carImageInput" class="upload-label">Choose Image</label>
                                    <input type="file" id="carImageInput" name="car_image"
                                        accept="image/jpeg, image/png, image/gif">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="submit-area">
                        <button type="submit" class="btn btn-primary submit-btn">List Vehicle →</button>
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
                            <a href="seller.php">Sellers Hub</a>
                            <a href="search.php">Search Cars</a>
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

            const interactiveElements = document.querySelectorAll('a, button, .btn, .nav-link, input, select, .upload-label');
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

        const fileInput = document.getElementById('carImageInput');
        const previewImg = document.getElementById('imagePreview');
        const placeholderDiv = document.getElementById('placeholderPreview');

        fileInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Image must be less than 2MB');
                    fileInput.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    placeholderDiv.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.src = '#';
                previewImg.style.display = 'none';
                placeholderDiv.style.display = 'flex';
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

        <? php if (!empty($success_message)): ?>
            setTimeout(function () {
                window.location.href = "seller.php";
            }, 2000);
        <? php endif; ?>
    </script>
</body>

</html>

</html>
