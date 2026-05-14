<?php
require_once 'config.php';
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>NEXUS - Technological Products</title>
        <link rel="stylesheet" href="stylesheet.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Sometype+Mono:wght@400;500;600;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .enhanced-hero {
                position: relative;
                min-height: 100vh;
                background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            
            .grid-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: 
                    linear-gradient(rgba(0, 153, 255, 0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(0, 153, 255, 0.03) 1px, transparent 1px);
                background-size: 50px 50px;
                animation: gridFloat 20s linear infinite;
            }
            
            @keyframes gridFloat {
                0% { transform: translateY(0); }
                100% { transform: translateY(50px); }
            }
            
            .floating-shapes {
                position: absolute;
                width: 100%;
                height: 100%;
                overflow: hidden;
            }
            
            .shape {
                position: absolute;
                background: rgba(0, 153, 255, 0.1);
                border-radius: 50%;
                animation: float 15s infinite ease-in-out;
            }
            
            .shape:nth-child(1) {
                width: 300px;
                height: 300px;
                top: 10%;
                left: 10%;
                animation-delay: 0s;
            }
            
            .shape:nth-child(2) {
                width: 200px;
                height: 200px;
                top: 60%;
                right: 15%;
                animation-delay: 2s;
            }
            
            .shape:nth-child(3) {
                width: 150px;
                height: 150px;
                bottom: 20%;
                left: 20%;
                animation-delay: 4s;
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0) scale(1); }
                50% { transform: translateY(-30px) scale(1.1); }
            }
            
            .hero-enhanced-content {
                position: relative;
                z-index: 10;
                text-align: center;
                padding: 0 40px;
            }
            
            .glitch-title {
                font-family: 'Space Mono', monospace;
                font-size: clamp(80px, 15vw, 200px);
                font-weight: 700;
                color: #fff;
                letter-spacing: 20px;
                margin-bottom: 30px;
                position: relative;
                animation: glitchTitle 3s infinite;
            }
            
            @keyframes glitchTitle {
                0%, 90%, 100% { text-shadow: 0 0 20px rgba(0, 153, 255, 0.5); }
                92% { text-shadow: 5px 0 20px rgba(255, 0, 0, 0.5), -5px 0 20px rgba(0, 255, 255, 0.5); }
                94% { text-shadow: -5px 0 20px rgba(255, 0, 0, 0.5), 5px 0 20px rgba(0, 255, 255, 0.5); }
            }
            
            .hero-tagline {
                font-family: 'Overpass Mono', monospace;
                font-size: clamp(16px, 2.5vw, 24px);
                color: #0099ff;
                letter-spacing: 8px;
                text-transform: uppercase;
                margin-bottom: 50px;
                opacity: 0;
                animation: fadeInUp 1s ease forwards 0.5s;
            }
            
            .cta-button {
                display: inline-block;
                padding: 20px 60px;
                background: transparent;
                color: #0099ff;
                border: 2px solid #0099ff;
                font-family: 'Space Mono', monospace;
                font-size: 16px;
                font-weight: 700;
                letter-spacing: 4px;
                text-decoration: none;
                text-transform: uppercase;
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
                opacity: 0;
                animation: fadeInUp 1s ease forwards 1s;
            }
            
            .cta-button::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: #0099ff;
                transition: left 0.3s ease;
                z-index: -1;
            }
            
            .cta-button:hover {
                color: #000;
                transform: translateY(-3px);
                box-shadow: 0 10px 30px rgba(0, 153, 255, 0.4);
            }
            
            .cta-button:hover::before {
                left: 0;
            }
            
            .features-showcase {
                background: #f5f5f5;
                padding: 120px 40px;
            }
            
            .section-header-enhanced {
                text-align: center;
                margin-bottom: 80px;
            }
            
            .section-title-enhanced {
                font-family: 'Space Mono', monospace;
                font-size: clamp(40px, 8vw, 80px);
                font-weight: 700;
                letter-spacing: 10px;
                color: #000;
                margin-bottom: 20px;
            }
            
            .section-subtitle {
                font-family: 'Overpass Mono', monospace;
                font-size: 18px;
                color: #666;
                letter-spacing: 2px;
            }
            
            .features-grid {
                max-width: 1400px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 50px;
            }
            
            .feature-card {
                background: #fff;
                padding: 50px 40px;
                text-align: center;
                border: 2px solid transparent;
                transition: all 0.4s ease;
                position: relative;
                overflow: hidden;
            }
            
            .feature-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(0, 153, 255, 0.1), transparent);
                transition: left 0.6s ease;
            }
            
            .feature-card:hover::before {
                left: 100%;
            }
            
            .feature-card:hover {
                border-color: #0099ff;
                transform: translateY(-10px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            }
            
            .feature-icon {
                font-size: 60px;
                margin-bottom: 30px;
                display: inline-block;
                transition: transform 0.3s ease;
            }
            
            .feature-card:hover .feature-icon {
                transform: scale(1.2) rotate(5deg);
            }
            
            .feature-title {
                font-family: 'Space Mono', monospace;
                font-size: 24px;
                font-weight: 700;
                letter-spacing: 3px;
                color: #000;
                margin-bottom: 15px;
            }
            
            .feature-description {
                font-family: 'Overpass Mono', monospace;
                font-size: 14px;
                color: #666;
                line-height: 1.8;
                letter-spacing: 1px;
            }
            
            .products-preview {
                background: #000;
                padding: 120px 40px;
                color: #fff;
            }
            
            .products-grid-enhanced {
                max-width: 1400px;
                margin: 60px auto 0;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 40px;
            }
            
            .product-card-enhanced {
                background: #1a1a1a;
                padding: 40px;
                text-align: center;
                border: 1px solid #333;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .product-card-enhanced::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 3px;
                background: #0099ff;
                transform: scaleX(0);
                transition: transform 0.3s ease;
            }
            
            .product-card-enhanced:hover {
                transform: translateY(-10px);
                border-color: #0099ff;
                box-shadow: 0 20px 40px rgba(0, 153, 255, 0.2);
            }
            
            .product-card-enhanced:hover::after {
                transform: scaleX(1);
            }
            
            .product-image-enhanced {
                width: 100%;
                height: 250px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 30px;
                background: #222;
                border-radius: 10px;
            }
            
            .product-image-enhanced img {
                max-width: 180px;
                max-height: 180px;
                object-fit: contain;
                transition: transform 0.3s ease;
            }
            
            .product-card-enhanced:hover .product-image-enhanced img {
                transform: scale(1.1);
            }
            
            .product-name {
                font-family: 'Space Mono', monospace;
                font-size: 18px;
                font-weight: 700;
                letter-spacing: 2px;
                color: #fff;
                margin-bottom: 15px;
            }
            
            .product-category {
                font-family: 'Overpass Mono', monospace;
                font-size: 12px;
                color: #0099ff;
                letter-spacing: 2px;
                text-transform: uppercase;
            }
            
            .stats-bar {
                background: #0099ff;
                padding: 80px 40px;
                text-align: center;
            }
            
            .stats-container {
                max-width: 1200px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 60px;
            }
            
            .stat-item {
                text-align: center;
            }
            
            .stat-number {
                font-family: 'Space Mono', monospace;
                font-size: clamp(50px, 8vw, 80px);
                font-weight: 700;
                color: #000;
                letter-spacing: 3px;
                margin-bottom: 10px;
            }
            
            .stat-label {
                font-family: 'Overpass Mono', monospace;
                font-size: 16px;
                color: #000;
                letter-spacing: 2px;
                text-transform: uppercase;
            }
        </style>
    </head>
    <body>
        <?php include 'navbar.php'; ?>

        <!-- Enhanced Hero Section -->
        <section class="enhanced-hero">
            <div class="grid-overlay"></div>
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>
            <div class="hero-enhanced-content">
                <h1 class="glitch-title">NEXUS</h1>
                <p class="hero-tagline">Technology Elevated</p>
                <a href="products.php" class="cta-button">Explore Products</a>
            </div>
        </section>

        <!-- Features Showcase -->
        <section class="features-showcase">
            <div class="section-header-enhanced">
                <h2 class="section-title-enhanced">WHY NEXUS</h2>
                <p class="section-subtitle">Performance. Design. Innovation.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">PERFORMANCE</h3>
                    <p class="feature-description">Cutting-edge technology that delivers maximum efficiency and speed for all your needs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎨</div>
                    <h3 class="feature-title">AESTHETICS</h3>
                    <p class="feature-description">Premium design meets functionality. Products that look as good as they perform.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🚀</div>
                    <h3 class="feature-title">INNOVATION</h3>
                    <p class="feature-description">Leading the future with the latest technological breakthroughs and features.</p>
                </div>
            </div>
        </section>

        <!-- Stats Bar -->
        <section class="stats-bar">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number">150+</div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">99%</div>
                    <div class="stat-label">Satisfaction</div>
                </div>
            </div>
        </section>

        <!-- Products Preview -->
        <section class="products-preview">
            <div class="section-header-enhanced">
                <h2 class="section-title-enhanced" style="color: #fff;">FEATURED</h2>
                <p class="section-subtitle" style="color: #999;">Our Most Popular Products</p>
            </div>
            <div class="products-grid-enhanced">
                <div class="product-card-enhanced">
                    <div class="product-image-enhanced">
                        <img src="images/apple-phone.png" alt="PowerCase">
                    </div>
                    <h3 class="product-name">POWERCASE</h3>
                    <p class="product-category">Accessories</p>
                </div>
                <div class="product-card-enhanced">
                    <div class="product-image-enhanced">
                        <img src="images/cable.png" alt="Charging Cable">
                    </div>
                    <h3 class="product-name">ULTRA CABLE</h3>
                    <p class="product-category">Accessories</p>
                </div>
                <div class="product-card-enhanced">
                    <div class="product-image-enhanced">
                        <img src="images/power-bank.png" alt="Power Bank">
                    </div>
                    <h3 class="product-name">POWER BANK</h3>
                    <p class="product-category">Accessories</p>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories-section">
            <div class="categories-content">
                <h2 class="categories-title">categories</h2>
                <div class="category-list">
                    <div class="category-item">
                        <a href="products.php" class="category-link">phones /</a>
                    </div>
                    <div class="category-item">
                        <a href="products.php" class="category-link">tablets /</a>
                    </div>
                    <div class="category-item">
                        <a href="products.php" class="category-link">accessories /</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p class="footer-text">Contact us at Colegio de San Juan de Letran</p>
            </div>
        </footer>

        <script src="script.js"></script>
    </body>
</html>