<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    if (isset($_POST['cart_id'])) {
        $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")
            ->execute([(int)$_POST['cart_id'], $user_id]);
    }
} else {
    // Guest: remove by index
    if (isset($_POST['index']) && isset($_SESSION['guest_cart'])) {
        $idx = (int)$_POST['index'];
        if (isset($_SESSION['guest_cart'][$idx])) {
            array_splice($_SESSION['guest_cart'], $idx, 1);
        }
    }
}

header('Location: cart.php');
exit;