<?php require_once 'config.php'; ?>
 
<nav class="navbar">
    <div class="nav-container">
 
        <div class="nav-left">
            <a href="about.php"    class="nav-link">ABOUT</a>
            <a href="contact.php"  class="nav-link">CONTACT</a>
            <a href="products.php" class="nav-link">PRODUCTS</a>
        </div>
 
        <div class="nav-center">
            <a href="index.php" class="logo">
                <svg width="40" height="40" viewBox="0 0 40 40">
                    <text x="50%" y="50%"
                          dominant-baseline="middle"
                          text-anchor="middle"
                          fill="#0099ff"
                          font-family="Space Mono"
                          font-size="24"
                          font-weight="700">N</text>
                </svg>
            </a>
        </div>
 
        <div class="nav-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="account.php" class="nav-link">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'Account') ?>
                </a>
                <a href="cart.php"    class="nav-link">CART</a>
                <a href="logout.php"  class="nav-link">LOGOUT</a>
            <?php else: ?>
                <a href="cart.php"     class="nav-link">CART<?php
                    $gc = $_SESSION['guest_cart'] ?? [];
                    if (!empty($gc)) {
                        $count = array_sum(array_column($gc, 'quantity'));
                        echo ' (' . (int)$count . ')';
                    }
                ?></a>
                <a href="login.php"    class="nav-link">LOGIN</a>
                <a href="register.php" class="nav-link">REGISTER</a>
            <?php endif; ?>
        </div>
 
    </div>
</nav>