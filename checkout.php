<?php
require_once 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php?return=cart.php');
    exit;
}

// ── PROCESS ORDER ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $address_raw     = trim($_POST['address']          ?? '');
    $city            = trim($_POST['city']             ?? '');
    $province        = trim($_POST['province']         ?? '');
    $phone           = trim($_POST['phone']            ?? '');
    $payment_code    = trim($_POST['payment_method']   ?? 'cod');

    if (!$address_raw || !$city || !$phone) {
        $checkout_error = 'Please fill in all required shipping fields.';
    } else {
        // Fetch cart with normalized joins
        $stmt = $pdo->prepare("
            SELECT c.product_id, c.variant_id, c.quantity,
                   p.name AS product_name, p.price, p.stock,
                   pv.name AS variant_name
            FROM cart c
            INNER JOIN products p ON p.id = c.product_id
            LEFT JOIN product_variants pv ON pv.id = c.variant_id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cart_items)) {
            header('Location: cart.php');
            exit;
        }

        // Recalculate totals server-side
        $subtotal = 0;
        foreach ($cart_items as $item) $subtotal += $item['price'] * $item['quantity'];
        $shipping = 50.00;
        $tax      = round($subtotal * 0.12, 2);
        $total    = $subtotal + $shipping + $tax;

        // Resolve payment_method_id
        $pmRow = $pdo->prepare("SELECT id FROM payment_methods WHERE code = ?");
        $pmRow->execute([$payment_code]);
        $payment_method_id = $pmRow->fetchColumn() ?: 1;

        try {
            $pdo->beginTransaction();

            // Save address
            $pdo->prepare("
                INSERT INTO addresses (user_id, address, city, province, phone, is_default)
                VALUES (?,?,?,?,?,0)
            ")->execute([$user_id, $address_raw, $city, $province, $phone]);
            $address_id = $pdo->lastInsertId();

            // Create order
            $pdo->prepare("
                INSERT INTO orders
                    (user_id, address_id, payment_method_id, subtotal, shipping, tax, total, status)
                VALUES (?,?,?,?,?,?,?,'pending')
            ")->execute([$user_id, $address_id, $payment_method_id,
                          $subtotal, $shipping, $tax, $total]);
            $order_id = $pdo->lastInsertId();

            // Insert order items + deduct stock
            $ins = $pdo->prepare("
                INSERT INTO order_items
                    (order_id, product_id, variant_id, product_name, variant_name, quantity, price)
                VALUES (?,?,?,?,?,?,?)
            ");
            $upd = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $log = $pdo->prepare("INSERT INTO stock_log (product_id,change_qty,reason,order_id) VALUES (?,?,?,?)");

            foreach ($cart_items as $item) {
                $ins->execute([
                    $order_id,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['product_name'],
                    $item['variant_name'],
                    $item['quantity'],
                    $item['price']
                ]);
                $upd->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                $log->execute([$item['product_id'], -$item['quantity'], 'sale', $order_id]);
            }

            // Clear cart
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

            $pdo->commit();

            // Fetch address for confirmation display
            $addrRow = $pdo->prepare("SELECT address, city FROM addresses WHERE id=?");
            $addrRow->execute([$address_id]);
            $addrData = $addrRow->fetch();

            ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - NEXUS</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .confirm-wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; background:#000; padding:120px 20px 60px; }
        .confirm-box  { background:#fff; padding:60px 50px; max-width:580px; width:100%; text-align:center; }
        .confirm-icon { font-size:56px; margin-bottom:16px; }
        .confirm-title{ font-family:'Space Mono',monospace; font-size:32px; font-weight:700; color:#0099ff; letter-spacing:3px; margin-bottom:16px; }
        .confirm-sub  { font-family:'Overpass Mono',monospace; font-size:14px; color:#555; line-height:1.8; margin-bottom:12px; }
        .order-num    { font-family:'Space Mono',monospace; font-size:22px; font-weight:700; color:#000; margin:20px 0; }
        .confirm-meta { background:#f7f7f7; padding:20px; margin:20px 0; text-align:left; font-family:'Overpass Mono',monospace; font-size:13px; color:#444; line-height:2; }
        .confirm-actions { display:flex; gap:16px; justify-content:center; margin-top:36px; flex-wrap:wrap; }
        .btn-cf { padding:14px 28px; font-family:'Overpass Mono',monospace; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:2px; text-decoration:none; display:inline-block; }
        .btn-cf-primary   { background:#000; color:#fff; }
        .btn-cf-primary:hover { background:#0099ff; }
        .btn-cf-secondary { background:#eee; color:#333; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="confirm-wrap">
        <div class="confirm-box">
            <div class="confirm-icon">✓</div>
            <h1 class="confirm-title">ORDER CONFIRMED</h1>
            <p class="confirm-sub">Thank you for your purchase! Your order is now being processed.</p>
            <div class="order-num">NX<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></div>
            <div class="confirm-meta">
                <div><strong>Ship to:</strong> <?= htmlspecialchars($addrData['address']) ?>, <?= htmlspecialchars($addrData['city']) ?></div>
                <div><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></div>
                <div><strong>Total:</strong> ₱<?= number_format($total, 2) ?></div>
                <div><strong>Status:</strong> Pending</div>
            </div>
            <div class="confirm-actions">
                <a href="products.php" class="btn-cf btn-cf-primary">Continue Shopping</a>
                <a href="account.php"  class="btn-cf btn-cf-secondary">View My Orders</a>
            </div>
        </div>
    </div>
</body>
</html>
            <?php
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $checkout_error = 'Error processing order. Please try again.';
        }
    }
}

// ── SHOW CHECKOUT FORM ────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT c.product_id, c.variant_id, c.quantity,
           p.name, p.price, p.image,
           pv.name AS variant_name
    FROM cart c
    INNER JOIN products p ON p.id = c.product_id
    LEFT  JOIN product_variants pv ON pv.id = c.variant_id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) { header('Location: cart.php'); exit; }

$subtotal = 0;
foreach ($cart_items as $item) $subtotal += $item['price'] * $item['quantity'];
$shipping = 50.00;
$tax      = round($subtotal * 0.10, 2);
$total    = $subtotal + $shipping + $tax;

$uRow = $pdo->prepare("SELECT fullname, email FROM users WHERE id=?");
$uRow->execute([$user_id]);
$uData = $uRow->fetch();

// Fetch payment methods from DB
$payMethods = $pdo->query("SELECT code, label FROM payment_methods ORDER BY id")->fetchAll();

// Fetch last saved address for prefill
$lastAddr = $pdo->prepare("SELECT * FROM addresses WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
$lastAddr->execute([$user_id]);
$savedAddr = $lastAddr->fetch() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NEXUS</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .checkout-page { background:#f5f5f5; min-height:100vh; padding:120px 40px 60px; }
        .checkout-grid { max-width:1100px; margin:0 auto; display:grid; grid-template-columns:1fr 380px; gap:40px; }
        @media(max-width:800px){ .checkout-grid{ grid-template-columns:1fr; } }
        .co-panel  { background:#fff; padding:35px; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,.06); margin-bottom:28px; }
        .co-title  { font-family:'Space Mono',monospace; font-size:17px; letter-spacing:2px; margin-bottom:24px; border-bottom:2px solid #f0f0f0; padding-bottom:14px; }
        .form-row  { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        @media(max-width:500px){ .form-row{ grid-template-columns:1fr; } }
        .fg        { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
        .fg label  { font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#888; font-family:'Overpass Mono',monospace; }
        .fg input, .fg select { padding:12px; border:1px solid #ddd; border-radius:4px; font-family:'Overpass Mono',monospace; font-size:14px; }
        .fg input:focus, .fg select:focus { outline:none; border-color:#0099ff; }
        .fg input[readonly] { background:#f9f9f9; color:#aaa; }
        .btn-place { width:100%; padding:17px; background:#000; color:#fff; border:none; font-family:'Overpass Mono',monospace; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:2px; cursor:pointer; border-radius:4px; margin-top:8px; transition:.2s; }
        .btn-place:hover { background:#0099ff; }
        .summary-item { display:flex; justify-content:space-between; font-size:13px; font-family:'Overpass Mono',monospace; padding:10px 0; border-bottom:1px solid #f0f0f0; gap:8px; }
        .summary-item:last-child { border:none; }
        .iname { flex:1; color:#333; }
        .ivariant { font-size:11px; color:#888; display:block; }
        .iqty  { color:#888; white-space:nowrap; }
        .iprc  { font-weight:700; white-space:nowrap; }
        .summary-line { display:flex; justify-content:space-between; font-size:13px; font-family:'Overpass Mono',monospace; padding:8px 0; color:#666; }
        .summary-total-row { display:flex; justify-content:space-between; font-family:'Space Mono',monospace; font-size:17px; font-weight:700; padding:14px 0 0; border-top:2px solid #000; margin-top:8px; }
        .alert-error { background:#f8d7da; border:1px solid #dc3545; color:#721c24; padding:14px 18px; border-radius:6px; margin-bottom:20px; font-size:14px; font-family:'Overpass Mono',monospace; }
        .page-heading { font-family:'Space Mono',monospace; font-size:28px; font-weight:700; letter-spacing:3px; margin-bottom:30px; }
        .back-link { display:inline-block; font-family:'Overpass Mono',monospace; font-size:13px; color:#0099ff; text-decoration:none; margin-bottom:24px; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="checkout-page">
        <div style="max-width:1100px;margin:0 auto;">
            <a href="cart.php" class="back-link">← Back to Cart</a>
            <h1 class="page-heading">CHECKOUT</h1>
        </div>

        <?php if (!empty($checkout_error)): ?>
            <div style="max-width:1100px;margin:0 auto 20px;">
                <div class="alert-error"><?= htmlspecialchars($checkout_error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" class="checkout-grid">
            <div>
                <div class="co-panel">
                    <div class="co-title">CONTACT INFORMATION</div>
                    <div class="fg"><label>Full Name</label>
                        <input type="text" value="<?= htmlspecialchars($uData['fullname']) ?>" readonly></div>
                    <div class="fg"><label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($uData['email']) ?>" readonly></div>
                    <div class="fg"><label>Phone *</label>
                        <input type="tel" name="phone" placeholder="09XX XXX XXXX" required
                               value="<?= htmlspecialchars($_POST['phone'] ?? $savedAddr['phone'] ?? '') ?>"></div>
                </div>

                <div class="co-panel">
                    <div class="co-title">SHIPPING ADDRESS</div>
                    <div class="fg"><label>Street / Barangay *</label>
                        <input type="text" name="address" placeholder="123 Sample St, Brgy Example" required
                               value="<?= htmlspecialchars($_POST['address'] ?? $savedAddr['address'] ?? '') ?>"></div>
                    <div class="form-row">
                        <div class="fg"><label>City / Municipality *</label>
                            <input type="text" name="city" placeholder="Calamba City" required
                                   value="<?= htmlspecialchars($_POST['city'] ?? $savedAddr['city'] ?? '') ?>"></div>
                        <div class="fg"><label>Province</label>
                            <input type="text" name="province" placeholder="Laguna"
                                   value="<?= htmlspecialchars($_POST['province'] ?? $savedAddr['province'] ?? '') ?>"></div>
                    </div>
                </div>

                <div class="co-panel">
                    <div class="co-title">PAYMENT METHOD</div>
                    <div class="fg"><label>Select Payment</label>
                        <select name="payment_method">
                            <?php foreach ($payMethods as $pm): ?>
                                <option value="<?= htmlspecialchars($pm['code']) ?>"
                                    <?= ($_POST['payment_method'] ?? 'cod') === $pm['code'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pm['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p style="font-size:12px;color:#888;font-family:'Overpass Mono',monospace;line-height:1.7;">
                        COD: pay on delivery. GCash/Maya: our team will contact you.
                    </p>
                </div>
            </div>

            <!-- ORDER SUMMARY -->
            <div>
                <div class="co-panel" style="position:sticky;top:100px;">
                    <div class="co-title">ORDER SUMMARY</div>
                    <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <span class="iname">
                            <?= htmlspecialchars($item['name']) ?>
                            <?php if ($item['variant_name']): ?>
                                <span class="ivariant"><?= htmlspecialchars($item['variant_name']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="iqty">×<?= (int)$item['quantity'] ?></span>
                        <span class="iprc">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div style="margin-top:16px;">
                        <div class="summary-line"><span>Subtotal</span><span>₱<?= number_format($subtotal,2) ?></span></div>
                        <div class="summary-line"><span>Shipping</span><span>₱<?= number_format($shipping,2) ?></span></div>
                        <div class="summary-line"><span>Tax (12%)</span><span>₱<?= number_format($tax,2) ?></span></div>
                        <div class="summary-total-row"><span>TOTAL</span><span>₱<?= number_format($total,2) ?></span></div>
                    </div>
                    <button type="submit" class="btn-place">Place Order</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>