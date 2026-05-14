<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?return=account.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$msg = $err = '';

// Pull any one-time flash from cancel_order.php redirect
if (!empty($_SESSION['account_flash_msg'])) {
    $msg = $_SESSION['account_flash_msg'];
    unset($_SESSION['account_flash_msg']);
}
if (!empty($_SESSION['account_flash_err'])) {
    $err = $_SESSION['account_flash_err'];
    unset($_SESSION['account_flash_err']);
}

// ── EDIT PROFILE ─────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'edit_profile') {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    if (!$fullname || !$email) {
        $err = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Enter a valid email address.';
    } else {
        // check email not taken by another user
        $ck = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
        $ck->execute([$email, $user_id]);
        if ($ck->fetch()) {
            $err = 'That email is already used by another account.';
        } else {
            $pdo->prepare("UPDATE users SET fullname=?, email=? WHERE id=?")
                ->execute([$fullname, $email, $user_id]);
            $_SESSION['user_name'] = $fullname;
            $msg = 'Profile updated successfully.';
        }
    }
}

// ── CHANGE PASSWORD ───────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $row = $pdo->prepare("SELECT password FROM users WHERE id=?");
    $row->execute([$user_id]);
    $hash = $row->fetchColumn();
    if (!password_verify($current, $hash)) {
        $err = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $err = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $err = 'New passwords do not match.';
    } else {
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")
            ->execute([password_hash($new, PASSWORD_BCRYPT), $user_id]);
        $msg = 'Password changed successfully.';
    }
}

// ── DELETE ACCOUNT ────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    $pass = $_POST['delete_password'] ?? '';
    $row  = $pdo->prepare("SELECT password FROM users WHERE id=?");
    $row->execute([$user_id]);
    $hash = $row->fetchColumn();
    if (!password_verify($pass, $hash)) {
        $err = 'Incorrect password. Account not deleted.';
    } else {
        $pdo->prepare("DELETE FROM cart        WHERE user_id=?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM orders      WHERE user_id=?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM users       WHERE id=?"     )->execute([$user_id]);
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

// ── FETCH USER ────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT fullname, email FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { session_destroy(); header("Location: login.php"); exit(); }

// ── FETCH ORDERS ──────────────────────────────────────────────────
$orders = $pdo->prepare("
    SELECT o.id, o.total, o.status, o.created_at,
           a.address, a.city,
           pm.label AS payment_method,
           COUNT(oi.id) AS item_count,
           GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', ') AS item_names
    FROM orders o
    LEFT JOIN addresses a        ON a.id  = o.address_id
    LEFT JOIN payment_methods pm ON pm.id = o.payment_method_id
    LEFT JOIN order_items oi     ON oi.order_id = o.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders->execute([$user_id]);
$orderList = $orders->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS | Account</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Sometype+Mono:wght@400;500;600;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .alert-success { background:#d4edda; border:1px solid #28a745; color:#155724; padding:14px 18px; border-radius:6px; margin-bottom:20px; font-size:14px; }
        .alert-error   { background:#f8d7da; border:1px solid #dc3545; color:#721c24; padding:14px 18px; border-radius:6px; margin-bottom:20px; font-size:14px; }
        .orders-table  { width:100%; border-collapse:collapse; font-size:14px; margin-top:10px; }
        .orders-table th { text-align:left; padding:10px 12px; border-bottom:2px solid #eee; font-family:'Space Mono',monospace; font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px; }
        .orders-table td { padding:12px; border-bottom:1px solid #f0f0f0; font-family:'Overpass Mono',monospace; }
        .status-badge  { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
        .status-pending    { background:#fff3cd; color:#856404; }
        .status-processing { background:#cce5ff; color:#004085; }
        .status-shipped    { background:#d1ecf1; color:#0c5460; }
        .status-completed  { background:#d4edda; color:#155724; }
        .status-cancelled  { background:#f8d7da; color:#721c24; }
        .status-refund_requested { background:#fff3cd; color:#856404; }
        .status-refunded { background:#d1ecf1; color:#0c5460; }
        .no-orders { color:#aaa; font-size:14px; padding:20px 0; font-family:'Overpass Mono',monospace; }
        .account-card form input,
        .account-card form select { font-family:'Overpass Mono',monospace; }
        .full-width { width:100%; }
        .modal-bg { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:1000; align-items:center; justify-content:center; }
        .modal-bg.open { display:flex; }
        .modal-box { background:#fff; padding:40px; max-width:420px; width:90%; border-radius:10px; }
        .modal-box h3 { font-family:'Space Mono',monospace; margin-bottom:16px; font-size:18px; }
        .modal-box p  { font-size:14px; color:#666; margin-bottom:20px; line-height:1.6; font-family:'Overpass Mono',monospace; }
        .modal-box input { width:100%; padding:12px; border:1px solid #ccc; border-radius:6px; font-family:'Overpass Mono',monospace; margin-bottom:16px; }
        .modal-actions { display:flex; gap:12px; }
        .btn-confirm-del { flex:1; padding:12px; background:red; color:#fff; border:none; cursor:pointer; font-family:'Overpass Mono',monospace; font-weight:700; border-radius:4px; }
        .btn-confirm-del:hover { background:#c00; }
        .btn-modal-cancel { flex:1; padding:12px; background:#eee; color:#333; border:none; cursor:pointer; font-family:'Overpass Mono',monospace; border-radius:4px; }
        .cancel-order-btn {
            padding:6px 14px; background:#fff; color:#dc3545; border:1px solid #dc3545;
            font-family:'Overpass Mono',monospace; font-size:12px; font-weight:600;
            text-transform:uppercase; letter-spacing:1px; cursor:pointer; border-radius:4px;
            transition:.2s;
        }
        .cancel-order-btn:hover { background:#dc3545; color:#fff; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<section class="account-hero">
    <h1>ACCOUNT</h1>
    <p>Manage your NEXUS account settings</p>
</section>

<section class="account-container">

<?php if ($msg): ?><div class="alert-success" style="grid-column:1/-1"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert-error"   style="grid-column:1/-1"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- PROFILE -->
    <div class="account-card">
        <h2>Profile & Personal Information</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit_profile">
            <input type="text"  name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" placeholder="Full Name" required>
            <input type="email" name="email"    value="<?= htmlspecialchars($user['email']) ?>"    placeholder="Email" required>
            <button class="account-btn full-width" type="submit">Save Changes</button>
        </form>
    </div>

    <!-- PASSWORD -->
    <div class="account-card">
        <h2>Change Password</h2>
        <form method="POST">
            <input type="hidden"   name="action" value="change_password">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password"     placeholder="New Password (min 6 chars)" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button class="account-btn full-width" type="submit">Update Password</button>
        </form>
    </div>

    <!-- ORDER HISTORY -->
    <div class="account-card" style="grid-column:1/-1">
        <h2>Order History</h2>
        <?php if (empty($orderList)): ?>
           <p class="no-orders">You haven't placed any orders yet. <a href="products.php" style="color:#0099ff;">Browse products →</a></p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Items Ordered</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderList as $o): ?>
                    <tr>
                        <td><strong>NX<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                        <td>
                            <span style="font-size:13px;color:#333"><?= htmlspecialchars($o['item_names'] ?? '—') ?></span>
                            <span style="font-size:11px;color:#aaa;display:block"><?= (int)$o['item_count'] ?> item(s)</span>
                        </td>
                        <td>₱<?= number_format($o['total'], 2) ?></td>
                        <td>
                            <span class="status-badge status-<?= htmlspecialchars($o['status']) ?>">
                                <?php
                                $icons = ['pending'=>'🕐','processing'=>'⚙️','shipped'=>'🚚','completed'=>'✅','cancelled'=>'✖','refund_requested'=>'💸','refunded'=>'↩️'];
                                echo ($icons[$o['status']] ?? '') . ' ' . ucfirst($o['status']);
                                ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                        <td>
                            <?php if ($o['status'] === 'pending'): ?>
                                <form method="POST" action="cancel_order.php" onsubmit="return confirm('Cancel order NX<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?>? This cannot be undone.');" style="margin:0;">
                                    <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                                    <button type="submit" class="cancel-order-btn">Cancel</button>
                                </form>
                            <?php elseif (in_array($o['status'], ['completed','shipped'])): ?>
                                <form method="POST" action="refund_order.php" onsubmit="return confirm('Request refund for order NX<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?>?');" style="margin:0;">
                                    <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                                    <button type="submit" class="cancel-order-btn">Refund</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#bbb;font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- DELETE ACCOUNT -->
    <div class="account-card danger-card" style="grid-column:1/-1">
        <h2>Delete Account</h2>
        <p>Permanently remove your account and all order data. This cannot be undone.</p>
        <button class="danger-btn" onclick="document.getElementById('deleteModal').classList.add('open')">
            Delete Account
        </button>
    </div>

</section>

<!-- Delete Confirmation Modal -->
<div class="modal-bg" id="deleteModal">
    <div class="modal-box">
        <h3>Delete Account</h3>
        <p>This will permanently delete your account and all associated data. Enter your password to confirm.</p>
        <form method="POST">
            <input type="hidden" name="action" value="delete_account">
            <input type="password" name="delete_password" placeholder="Your password" required>
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('deleteModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn-confirm-del">Delete Forever</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>