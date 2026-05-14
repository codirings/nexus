<?php
require_once 'config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

// Already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header("Location: admin.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — NEXUS</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0a0a;
            font-family: 'Overpass Mono', monospace;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #111;
            border: 1px solid #222;
            padding: 50px 45px;
            width: 100%;
            max-width: 420px;
        }
        .logo {
            font-family: 'Space Mono', monospace;
            font-size: 28px;
            font-weight: 700;
            color: #0099ff;
            letter-spacing: 4px;
            margin-bottom: 4px;
        }
        .logo-sub {
            font-size: 11px;
            color: #555;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 40px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #666;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            background: #1a1a1a;
            border: 1px solid #333;
            color: #fff;
            padding: 13px 14px;
            font-family: 'Overpass Mono', monospace;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #0099ff;
        }
        .btn-login {
            width: 100%;
            background: #0099ff;
            color: #fff;
            border: none;
            padding: 14px;
            font-family: 'Overpass Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }
        .btn-login:hover { background: #007acc; }
        .error {
            background: #330000;
            border: 1px solid #ff4444;
            color: #ff4444;
            padding: 12px 15px;
            font-size: 13px;
            margin-bottom: 22px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            font-size: 12px;
            color: #444;
            text-decoration: none;
            letter-spacing: 1px;
        }
        .back-link:hover { color: #888; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">NEXUS</div>
        <div class="logo-sub">Admin Panel</div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" autocomplete="username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>

        <a href="index.php" class="back-link">← Back to Store</a>
    </div>
</body>
</html>