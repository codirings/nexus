<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>About - NEXUS</title>
        <link rel="stylesheet" href="stylesheet.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Sometype+Mono:wght@400;500;600;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .page-hero {
                background-color: #000000;
                min-height: 50vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 150px 40px 80px;
            }

            .page-title {
                font-family: 'Space Mono', monospace;
                font-size: clamp(60px, 10vw, 100px);
                font-weight: 700;
                color: #ffffff;
                letter-spacing: 10px;
                text-transform: uppercase;
            }

            .about-detail-section {
                padding: 100px 40px;
                background-color: #ffffff;
            }

            .about-detail-content {
                max-width: 900px;
                margin: 0 auto;
            }

            .about-detail-content h2 {
                font-family: 'Overpass Mono', monospace;
                font-size: clamp(28px, 4vw, 36px);
                font-weight: 600;
                margin-bottom: 30px;
                color: #000000;
            }

            .about-detail-content p {
                font-family: 'Overpass Mono', monospace;
                font-size: clamp(16px, 2vw, 18px);
                line-height: 1.8;
                margin-bottom: 25px;
                color: #333333;
            }
        </style>
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php include 'navbar.php'; ?>

        <!-- Page Hero -->
        <section class="page-hero">
            <h1 class="page-title">ABOUT</h1>
        </section>

        <!-- About Detail Section -->
        <section class="about-detail-section">
            <div class="about-detail-content">
                <h2>Our Story</h2>
                <p>
                    NEXUS was founded with a simple vision: to provide cutting-edge technological products that enhance performance, aesthetics, productivity, and efficiency. We believe technology should not only function flawlessly but also inspire and elevate everyday experiences.
                </p>

                <h2>Our Mission</h2>
                <p>
                    We are committed to delivering the latest technological innovations to our customers. Each product in our catalog is carefully selected to meet the highest standards of quality, design, and functionality.
                </p>

                <h2>Why Choose NEXUS?</h2>
                <p>
                    At NEXUS, we understand that technology is personal. Whether you're looking for the latest smartphones, powerful tablets, or premium accessories, we ensure every product meets your unique needs. Our dedication to excellence and customer satisfaction sets us apart.
                </p>

                <p>
                    Welcome to the future. Welcome to NEXUS.
                </p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p class="footer-text">Contact us at Colegio de San Juan de Letran</p>
            </div>
        </footer>

        <script src="js/script.js"></script>
    </body>
</html>