<?php
ob_start(); // Buffer any stray output (warnings, notices) before JSON
require_once 'config.php';

// Discard any output that leaked before this point (e.g. BOM, PHP notices)
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id    = $_SESSION['user_id'] ?? null;
$product_id = (int)($_POST['product_id'] ?? 0);
$variant_id = (int)($_POST['variant_id'] ?? 0) ?: null;
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check stock (variant takes priority if present)
if ($variant_id) {
    $stockStmt = $pdo->prepare("SELECT stock FROM product_variants WHERE id = ? AND product_id = ?");
    $stockStmt->execute([$variant_id, $product_id]);
    $available = $stockStmt->fetchColumn();
    if ($available === false) {
        echo json_encode(['success' => false, 'message' => 'Variant not found']);
        exit;
    }
} else {
    $stockStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stockStmt->execute([$product_id]);
    $available = $stockStmt->fetchColumn();
    if ($available === false) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
}

if ((int)$available < 1) {
    echo json_encode(['success' => false, 'message' => 'Out of stock']);
    exit;
}
if ($quantity > (int)$available) {
    echo json_encode(['success' => false, 'message' => 'Only ' . $available . ' left in stock']);
    exit;
}

// ─────────────────────────────────────────────────────────────────
// LOGGED-IN USER: upsert into cart table
// ─────────────────────────────────────────────────────────────────
if ($user_id) {
    // Verify user still exists (guards against stale/deleted sessions)
    $userCheck = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $userCheck->execute([$user_id]);
    if (!$userCheck->fetchColumn()) {
        session_destroy();
        echo json_encode([
            'success'  => false,
            'message'  => 'Session expired. Please log in again.',
            'redirect' => 'login.php'
        ]);
        exit;
    }

    try {
        if ($variant_id) {
            $existing = $pdo->prepare(
                "SELECT id, quantity FROM cart WHERE user_id=? AND product_id=? AND variant_id=?"
            );
            $existing->execute([$user_id, $product_id, $variant_id]);
        } else {
            $existing = $pdo->prepare(
                "SELECT id, quantity FROM cart WHERE user_id=? AND product_id=? AND variant_id IS NULL"
            );
            $existing->execute([$user_id, $product_id]);
        }
        $row = $existing->fetch();

        if ($row) {
            $newQty = $row['quantity'] + $quantity;
            if ($newQty > (int)$available) $newQty = (int)$available;
            $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?")
                ->execute([$newQty, $row['id']]);
        } else {
            $pdo->prepare(
                "INSERT INTO cart (user_id, product_id, variant_id, quantity) VALUES (?,?,?,?)"
            )->execute([$user_id, $product_id, $variant_id, $quantity]);
        }

        echo json_encode(['success' => true, 'message' => 'Added to cart']);

    } catch (PDOException $e) {
        // SQLSTATE 23000 = integrity constraint violation (FK, duplicate, etc.)
        if (in_array($e->getCode(), ['23000', 23000], true)) {
            echo json_encode([
                'success' => false,
                'message' => 'Could not add to cart: invalid product or variant reference. Please refresh the page and try again.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
        }
    }
    exit;
}

// ─────────────────────────────────────────────────────────────────
// GUEST: store in $_SESSION['guest_cart']
// ─────────────────────────────────────────────────────────────────
if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) {
    $_SESSION['guest_cart'] = [];
}

$found = false;
foreach ($_SESSION['guest_cart'] as $i => $it) {
    $itVar = isset($it['variant_id']) && $it['variant_id'] ? (int)$it['variant_id'] : null;
    if ((int)$it['product_id'] === $product_id && $itVar === $variant_id) {
        $newQty = (int)$it['quantity'] + $quantity;
        if ($newQty > (int)$available) $newQty = (int)$available;
        $_SESSION['guest_cart'][$i]['quantity'] = $newQty;
        $found = true;
        break;
    }
}
if (!$found) {
    $_SESSION['guest_cart'][] = [
        'product_id' => $product_id,
        'variant_id' => $variant_id,
        'quantity'   => $quantity,
    ];
    $_SESSION['guest_cart'] = array_values($_SESSION['guest_cart']);
}

echo json_encode(['success' => true, 'message' => 'Added to cart']);