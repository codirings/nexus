<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action = $_POST['action'] ?? 'set'; // 'inc', 'dec', or 'set'
$user_id = $_SESSION['user_id'] ?? null;

// ── Helper: get stock for a product or variant ────────────────────
function getAvailableStock($pdo, $product_id, $variant_id) {
    if ($variant_id) {
        $s = $pdo->prepare("SELECT stock FROM product_variants WHERE id=? AND product_id=?");
        $s->execute([$variant_id, $product_id]);
    } else {
        $s = $pdo->prepare("SELECT stock FROM products WHERE id=?");
        $s->execute([$product_id]);
    }
    $v = $s->fetchColumn();
    return $v === false ? null : (int)$v;
}

// ─────────────────────────────────────────────────────────────────
// LOGGED-IN USER: update `cart` table row
// ─────────────────────────────────────────────────────────────────
if ($user_id) {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    if ($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
        exit;
    }

    $row = $pdo->prepare("SELECT id, product_id, variant_id, quantity FROM cart WHERE id=? AND user_id=?");
    $row->execute([$cart_id, $user_id]);
    $item = $row->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    $available = getAvailableStock($pdo, $item['product_id'], $item['variant_id']);
    if ($available === null) {
        echo json_encode(['success' => false, 'message' => 'Product no longer available']);
        exit;
    }

    // Compute new quantity
    $current = (int)$item['quantity'];
    if ($action === 'inc')      $newQty = $current + 1;
    elseif ($action === 'dec')  $newQty = $current - 1;
    else                        $newQty = max(1, (int)($_POST['quantity'] ?? $current));

    if ($newQty < 1) {
        // dec below 1 → remove
        $pdo->prepare("DELETE FROM cart WHERE id=? AND user_id=?")->execute([$cart_id, $user_id]);
        echo json_encode(['success' => true, 'removed' => true, 'message' => 'Item removed']);
        exit;
    }

    if ($newQty > $available) {
        echo json_encode(['success' => false, 'message' => 'Only ' . $available . ' in stock', 'max' => $available, 'quantity' => $current]);
        exit;
    }

    $pdo->prepare("UPDATE cart SET quantity=? WHERE id=? AND user_id=?")
        ->execute([$newQty, $cart_id, $user_id]);

    echo json_encode(['success' => true, 'quantity' => $newQty]);
    exit;
}

// ─────────────────────────────────────────────────────────────────
// GUEST: update $_SESSION['guest_cart']
// guest_cart is array of ['product_id'=>..,'variant_id'=>..,'quantity'=>..]
// indexed numerically; client sends `index`
// ─────────────────────────────────────────────────────────────────
$index = (int)($_POST['index'] ?? -1);
if ($index < 0 || !isset($_SESSION['guest_cart'][$index])) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit;
}

$item = $_SESSION['guest_cart'][$index];
$available = getAvailableStock($pdo, $item['product_id'], $item['variant_id']);
if ($available === null) {
    echo json_encode(['success' => false, 'message' => 'Product no longer available']);
    exit;
}

$current = (int)$item['quantity'];
if ($action === 'inc')      $newQty = $current + 1;
elseif ($action === 'dec')  $newQty = $current - 1;
else                        $newQty = max(1, (int)($_POST['quantity'] ?? $current));

if ($newQty < 1) {
    array_splice($_SESSION['guest_cart'], $index, 1);
    // reindex automatically via array_splice
    echo json_encode(['success' => true, 'removed' => true, 'message' => 'Item removed']);
    exit;
}

if ($newQty > $available) {
    echo json_encode(['success' => false, 'message' => 'Only ' . $available . ' in stock', 'max' => $available, 'quantity' => $current]);
    exit;
}

$_SESSION['guest_cart'][$index]['quantity'] = $newQty;
echo json_encode(['success' => true, 'quantity' => $newQty]);