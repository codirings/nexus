<?php
require_once 'config.php';

$error = '';
$success = '';
$returnUrl = $_GET['return'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city']    ?? '');
    $province = trim($_POST['province'] ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $password = $_POST['password'];
    $confirm = $_POST['confirm-password'];

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm) || empty($address) || empty($phone)) {
        $error = 'All required fields must be filled in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'That email is already registered. Please log in or use a different email.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$fullname, $email, $hashed]);

            // Auto-login after registration
            $newUserId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $fullname;

            // Save default address
            $pdo->prepare("
                INSERT INTO addresses (user_id, address, city, province, phone, is_default)
                VALUES (?,?,?,?,?,1)
            ")->execute([$newUserId, $address, $city, $province, $phone]);

            // Merge guest cart into user cart
            if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
                foreach ($_SESSION['guest_cart'] as $item) {
                    $pdo->prepare("INSERT INTO cart (user_id, product_id, variant_id, quantity) VALUES (?,?,?,?)")
                        ->execute([$newUserId, $item['product_id'], $item['variant_id'], $item['quantity']]);
                }
                unset($_SESSION['guest_cart']);
            }

            header("Location: " . $returnUrl);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register - NEXUS</title>
        <link rel="stylesheet" href="stylesheet.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Sometype+Mono:wght@400;500;600;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .register-container{
                min-height:100vh;
                display:flex;
                justify-content:center;
                align-items:center;
                background-color:#000;
                padding:120px 40px 40px
            }
            .register-box{
                background-color:#fff;
                padding:60px 50px;
                max-width:450px;
                width:100%;
                box-shadow:0 10px 50px rgba(0,153,255,.2)
            }
            .register-title{
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
            .register-btn{
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
            .register-btn:hover{
                background-color:#0099ff;
                transform:translateY(-3px)
            }
            .register-links{
                margin-top:30px;
                text-align:center
            }
            .register-links a{
                font-family:'Overpass Mono',monospace;
                font-size:14px;
                color:#666;
                text-decoration:none;
                transition:color .3s
            }
            .register-links a:hover{
                color:#0099ff
            }
            .alert{
                padding:12px 16px;
                margin-bottom:20px;
                font-family:'Overpass Mono',monospace;
                font-size:14px
            }
            .alert-error{
                background:#ffe5e5;
                border:1px solid #f44;
                color:#c00
            }
            .alert-success{
                background:#e5ffe5;
                border:1px solid #4b4;
                color:#060
            }
            .alert-success a{
                color:#060
            }
            .form-row-2{
                display:grid;
                grid-template-columns:1fr 1fr;
                gap:14px;
            }
            @media (max-width:520px){
                .form-row-2{ grid-template-columns:1fr; }
            }
            .field-status{
                font-family:'Overpass Mono',monospace;
                font-size:12px;
                margin-top:6px;
                min-height:16px;
                letter-spacing:.5px;
            }
            .field-status.ok    { color:#060; }
            .field-status.bad   { color:#c00; }
            .field-status.checking { color:#888; }
            .register-box{
                max-width:520px !important;
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

        <div class="register-container">
            <div class="register-box">
                <h1 class="register-title">REGISTER</h1>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php?return=<?= urlencode($returnUrl) ?>" id="register-form">
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <div id="email-status" class="field-status"></div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" required placeholder="09XX XXX XXXX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="address">Street Address *</label>
                        <input type="text" id="address" name="address" required placeholder="123 Sample St, Brgy Example" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" placeholder="Calamba City" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="province">Province</label>
                            <input type="text" id="province" name="province" placeholder="Laguna" value="<?= htmlspecialchars($_POST['province'] ?? '') ?>">
                        </div>
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
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm-password" name="confirm-password" required>
                            <button type="button" class="password-toggle" data-target="confirm-password" aria-label="Show password">
                                <svg class="icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="register-btn">Create Account</button>
                </form>

                <div class="register-links">
                    <span style="font-family:'Overpass Mono',monospace;font-size:14px;color:#666;">Already have an account?</span>
                    <a href="login.php?return=<?= urlencode($returnUrl) ?>"> Sign In</a>
                </div>
            </div>
        </div>

        <script src="script.js"></script>
        <script>
        // ── Password show/hide toggles ──
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
        <script>
        // ── Real-time email-already-registered check ──
        (function () {
            var emailInput = document.getElementById('email');
            var status     = document.getElementById('email-status');
            var form       = document.getElementById('register-form');
            var debounceTimer = null;
            var lastChecked = '';
            var emailTaken = false;

            function setStatus(text, cls) {
                status.textContent = text;
                status.className = 'field-status' + (cls ? ' ' + cls : '');
            }

            function checkEmail() {
                var email = emailInput.value.trim();
                if (!email) { setStatus('', ''); emailTaken = false; return; }
                // basic format check
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    setStatus('Enter a valid email address.', 'bad');
                    emailTaken = false;
                    return;
                }
                if (email === lastChecked) return;
                lastChecked = email;
                setStatus('Checking availability...', 'checking');

                var fd = new FormData();
                fd.append('email', email);
                fetch('check_email.php', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.taken) {
                            setStatus('This email is already registered. Try logging in.', 'bad');
                            emailTaken = true;
                        } else {
                            setStatus('Email is available.', 'ok');
                            emailTaken = false;
                        }
                    })
                    .catch(function () { setStatus('', ''); });
            }

            if (emailInput) {
                emailInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(checkEmail, 450);
                });
                emailInput.addEventListener('blur', checkEmail);
            }

            // Block submit if email is known-taken (server-side still re-checks)
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (emailTaken) {
                        e.preventDefault();
                        setStatus('This email is already registered. Try logging in.', 'bad');
                        emailInput.focus();
                    }
                });
            }
        })();
        </script>
    </body>
</html>