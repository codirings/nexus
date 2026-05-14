<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: account.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: login.php?return=account.php');
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);

$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['account_flash_err'] = 'Order not found.';
    header('Location: account.php');
    exit;
}

if (!in_array($order['status'], ['completed', 'shipped'])) {
    $_SESSION['account_flash_err'] = 'This order is not eligible for refund.';
    header('Location: account.php');
    exit;
}

$pdo->prepare("UPDATE orders SET status = 'refund_requested' WHERE id = ?")
    ->execute([$order_id]);

$_SESSION['account_flash_msg'] = 'Refund request submitted successfully.';

header('Location: account.php');
exit;
