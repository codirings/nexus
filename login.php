<?php
require_once 'config.php';

$error = '';
$returnUrl = $_GET['return'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Account not found. Please register first.";
    } else {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            
            // Merge guest cart into user cart
            if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
                foreach ($_SESSION['guest_cart'] as $item) {
                    $product_id = $item['product_id'];
                    $variant_id = $item['variant_id'];
                    $quantity = $item['quantity'];
                    
                    if ($variant_id) {
                        $existing = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND product_id=? AND variant_id=?");
                        $existing->execute([$user['id'], $product_id, $variant_id]);
                    } else {
                        $existing = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND product_id=? AND variant_id IS NULL");
                        $existing->execute([$user['id'], $product_id]);
                    }
                    $row = $existing->fetch();

                    if ($row) {
                        $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?")
                            ->execute([$quantity, $row['id']]);
                    } else {
                        $pdo->prepare("INSERT INTO cart (user_id, product_id, variant_id, quantity) VALUES (?,?,?,?)")
                            ->execute([$user['id'], $product_id, $variant_id, $quantity]);
                    }
                }
                unset($_SESSION['guest_cart']);
            }
            
            header("Location: " . $returnUrl);
        } else {
            $error = "Invalid password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - NEXUS</title>
        <link rel="stylesheet" href="stylesheet.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Sometype+Mono:wght@400;500;600;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .login-container{
                min-height:100vh;
                display:flex;
                justify-content:center;
                align-items:center;
                background-color:#000;
                padding:120px 40px 40px
            }
            .login-box{
                background-color:#fff;
                padding:60px 50px;
                max-width:450px;
                width:100%;
                box-shadow:0 10px 50px rgba(0,153,255,.2)
            }
            .login-title{
                font-family:'Space Mono',monospace;
                font-size:36px;
                font-weight:700;
                color:#000;
                text-align:center;
                margin-bottom:40px;
                letter-spacing:3px
            }
            .form-group{
                margin-bottom:25px
            }
            .form-group label{
                font-family:'Overpass Mono',monospace;
                font-size:14px;
                font-weight:600;
                color:#000;
                display:block;
                margin-bottom:10px;
                text-transform:uppercase;
                letter-spacing:1px
            }
            .form-group input{
                width:100%;
                padding:15px;
                font-family:'Overpass Mono',monospace;
                font-size:16px;
                border:2px solid #e0e0e0;
                background-color:#f5f5f5;
                color:#000;
                transition:border-color .3s,background-color .3s;
                box-sizing:border-box
            }
            .form-group input:focus{
                outline:none;
                border-color:#0099ff;
                background-color:#fff
            }
            .login-btn{
                width:100%;
                font-family:'Overpass Mono',monospace;
                font-size:16px;
                font-weight:600;
                text-transform:uppercase;
                letter-spacing:2px;
                padding:18px;
                background-color:#000;
                color:#fff;
                border:none;
                cursor:pointer;
                transition:background-color .3s,transform .3s;
                margin-top:20px
            }
            .login-btn:hover{
                background-color:#0099ff;
                transform:translateY(-3px)
            }
            .login-links{
                margin-top:30px;
                text-align:center
            }
            .login-links a{
                font-family:'Overpass Mono',monospace;
                font-size:14px;
                color:#666;
                text-decoration:none;
                transition:color .3s
            }
            .login-links a:hover{
                color:#0099ff
            }
            .divider{
                margin:0 10px;
                color:#ccc
            }
            .alert{
                padding:12px 16px;
                margin-bottom:20px;
                font-family:'Overpass Mono',monospace;
                font-size:14px;
                background:#ffe5e5;
                border:1px solid #f44;
                color:#c00
            }
            .password-wrapper{
                position:relative;
            }
            .password-wrapper input{
                padding-right:50px !important;
            }
            .password-toggle{
                position:absolute;
                right:12px;
                top:50%;
                transform:translateY(-50%);
                background:none;
                border:none;
                cursor:pointer;
                padding:6px;
                display:flex;
                align-items:center;
                justify-content:center;
                color:#666;
                transition:color .2s;
            }
            .password-toggle:hover{
                color:#0099ff;
            }
            .password-toggle svg{
                width:22px;
                height:22px;
                display:block;
            }
            .password-toggle .icon-hide{ display:none; }
            .password-toggle.is-visible .icon-show{ display:none; }
            .password-toggle.is-visible .icon-hide{ display:block; }
        </style>
    </head>
    <body>
        <?php include 'navbar.php'; ?>

        <div class="login-container">
            <div class="login-box">
                <h1 class="login-title">LOGIN</h1>

                <?php if ($error): ?>
                    <div class="alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php?return=<?= urlencode($returnUrl) ?>">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
                                <svg class="icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="login-btn">Sign In</button>
                </form>

                <div class="login-links">
                    <a href="register.php?return=<?= urlencode($returnUrl) ?>">Create Account</a>
                </div>
            </div>
        </div>

        <script src="script.js"></script>
        <script>
        // ── Password show/hide toggle ──
        document.querySelectorAll('.password-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.target);
                if (!input) return;
                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                btn.classList.toggle('is-visible', !showing);
                btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        });
        </script>
    </body>
</html>