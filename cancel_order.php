<?php
require_once 'config.php';

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: account.php');
    exit;
}

// Must be logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php?return=account.php');
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);
if ($order_id <= 0) {
    $_SESSION['account_flash_err'] = 'Invalid order.';
    header('Location: account.php');
    exit;
}

// Fetch order — must belong to this user and still be pending
$row = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
$row->execute([$order_id, $user_id]);
$order = $row->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['account_flash_err'] = 'Order not found.';
    header('Location: account.php');
    exit;
}

if ($order['status'] !== 'pending') {
    $_SESSION['account_flash_err'] = 'Only pending orders can be cancelled.';
    header('Location: account.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Restore stock for each item in the order
    $items = $pdo->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
    $items->execute([$order_id]);
    $orderItems = $items->fetchAll(PDO::FETCH_ASSOC);

    $upProd = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    $upVar  = $pdo->prepare("UPDATE product_variants SET stock = stock + ? WHERE id = ?");
    $log    = $pdo->prepare("INSERT INTO stock_log (product_id, change_qty, reason, order_id) VALUES (?,?,?,?)");

    foreach ($orderItems as $it) {
        $qty = (int)$it['quantity'];
        $upProd->execute([$qty, (int)$it['product_id']]);
        if (!empty($it['variant_id'])) {
            $upVar->execute([$qty, (int)$it['variant_id']]);
        }
        $log->execute([(int)$it['product_id'], $qty, 'adjustment', $order_id]);
    }

    // Mark order as cancelled
    $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?")
        ->execute([$order_id, $user_id]);

    $pdo->commit();
    $_SESSION['account_flash_msg'] = 'Order NX' . str_pad($order_id, 5, '0', STR_PAD_LEFT) . ' has been cancelled.';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['account_flash_err'] = 'Could not cancel the order. Please try again.';
}

header('Location: account.php');
exit;
