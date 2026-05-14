<?php
require_once 'config.php';

$user_id    = $_SESSION['user_id'] ?? null;
$cart_items = [];
$subtotal = $shipping = $tax = $total = 0;

if ($user_id) {
    // ── LOGGED-IN: load from DB ───────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT
            c.id            AS cart_id,
            c.quantity,
            p.id            AS product_id,
            p.name,
            p.price,
            p.stock         AS product_stock,
            p.image,
            cat.name        AS category,
            pv.id           AS variant_id,
            pv.name         AS variant_name,
            pv.stock        AS variant_stock
        FROM cart c
        INNER JOIN products   p   ON p.id  = c.product_id
        INNER JOIN categories cat ON cat.id = p.category_id
        LEFT  JOIN product_variants pv ON pv.id = c.variant_id
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC
    ");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $r['cart_key'] = (int)$r['cart_id'];          // DOM identifier
        $r['ref_type'] = 'cart_id';                   // tells JS which param name to send
        $r['stock']    = $r['variant_id']
            ? (int)$r['variant_stock']
            : (int)$r['product_stock'];
        $cart_items[] = $r;
    }
} else {
    // ── GUEST: build from $_SESSION['guest_cart'] ─────────────────
    $guest = $_SESSION['guest_cart'] ?? [];
    if (!empty($guest)) {
        // Hydrate by joining against products / variants / categories
        foreach ($guest as $idx => $it) {
            $pid = (int)$it['product_id'];
            $vid = !empty($it['variant_id']) ? (int)$it['variant_id'] : null;

            $q = $pdo->prepare("
                SELECT p.id AS product_id, p.name, p.price, p.stock AS product_stock,
                       p.image, cat.name AS category
                FROM products p
                INNER JOIN categories cat ON cat.id = p.category_id
                WHERE p.id = ?
            ");
            $q->execute([$pid]);
            $prod = $q->fetch(PDO::FETCH_ASSOC);
            if (!$prod) continue; // product was deleted — silently skip

            $vname = null; $vstock = null;
            if ($vid) {
                $vq = $pdo->prepare("SELECT name, stock FROM product_variants WHERE id=? AND product_id=?");
                $vq->execute([$vid, $pid]);
                $vr = $vq->fetch(PDO::FETCH_ASSOC);
                if (!$vr) continue;
                $vname  = $vr['name'];
                $vstock = (int)$vr['stock'];
            }

            $cart_items[] = [
                'cart_key'      => $idx,
                'ref_type'      => 'index',
                'quantity'      => (int)$it['quantity'],
                'product_id'    => $prod['product_id'],
                'name'          => $prod['name'],
                'price'         => $prod['price'],
                'image'         => $prod['image'],
                'category'      => $prod['category'],
                'variant_id'    => $vid,
                'variant_name'  => $vname,
                'stock'         => $vid ? $vstock : (int)$prod['product_stock'],
            ];
        }
    }
}

foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
if ($subtotal > 0) {
    $shipping = 50.00;
    $tax      = round($subtotal * 0.12, 2);
    $total    = $subtotal + $shipping + $tax;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - NEXUS</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="cart.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Sometype+Mono:wght@400;500;600;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .qty-controls { display:flex; align-items:center; gap:8px; margin-top:10px; }
        .qty-btn {
            width:30px; height:30px; border:1px solid #000; background:#fff; color:#000;
            font-family:'Space Mono',monospace; font-weight:700; font-size:16px;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:.2s;
        }
        .qty-btn:hover:not(:disabled) { background:#000; color:#fff; }
        .qty-btn:disabled { opacity:.4; cursor:not-allowed; }
        .qty-display {
            font-family:'Overpass Mono',monospace; font-size:15px; font-weight:600;
            min-width:30px; text-align:center;
        }
        .stock-note { font-size:11px; color:#888; margin-top:4px; font-family:'Overpass Mono',monospace; }
        .guest-banner {
            background:#fff8e1; border-left:4px solid #ffaa00; padding:14px 18px;
            font-family:'Overpass Mono',monospace; font-size:14px; color:#5a4500;
            margin-bottom:24px;
        }
        .guest-banner a { color:#0099ff; font-weight:600; }
        .cart-toast {
            position:fixed; bottom:30px; right:30px; background:#111; border:1px solid #0099ff;
            color:#fff; padding:14px 22px; font-family:'Sometype Mono',monospace; font-size:13px;
            letter-spacing:1px; z-index:9999; opacity:0; transform:translateY(20px);
            transition:opacity .3s, transform .3s; pointer-events:none;
        }
        .cart-toast.show { opacity:1; transform:translateY(0); }
        .cart-toast.error { border-color:#ff4444; color:#ff4444; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-hero">
        <h1 class="page-title">CART</h1>
    </section>

    <section class="cart-section">
        <div class="cart-container">
            <div class="cart-items">
                <?php if (!$user_id && !empty($cart_items)): ?>
                    <div class="guest-banner">
                        You're shopping as a guest. <a href="login.php?return=cart.php">Log in</a> or <a href="register.php?return=cart.php">create an account</a> to save your cart and check out.
                    </div>
                <?php endif; ?>

                <?php if (empty($cart_items)): ?>
                    <h2 style='font-family:"Overpass Mono",monospace;'>Your cart is empty.</h2>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-cart-key="<?= htmlspecialchars((string)$item['cart_key']) ?>" data-ref-type="<?= htmlspecialchars($item['ref_type']) ?>" data-stock="<?= (int)$item['stock'] ?>" data-price="<?= (float)$item['price'] ?>">
                        <div class="item-image">
                            <?php if ($item['image']): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="max-width:80px;max-height:80px;object-fit:contain;">
                            <?php else: ?>
                                <span>IMAGE</span>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p style="color:#888;font-size:12px;"><?= htmlspecialchars(ucfirst($item['category'])) ?></p>
                            <?php if ($item['variant_name']): ?>
                                <p style="color:#0099ff;font-size:12px;">Variant: <?= htmlspecialchars($item['variant_name']) ?></p>
                            <?php endif; ?>
                            <div class="qty-controls">
                                <button type="button" class="qty-btn qty-dec" aria-label="Decrease">−</button>
                                <span class="qty-display"><?= (int)$item['quantity'] ?></span>
                                <button type="button" class="qty-btn qty-inc" aria-label="Increase" <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>+</button>
                            </div>
                            <div class="stock-note"><?= (int)$item['stock'] ?> available</div>
                        </div>
                        <div class="item-price">
                            <span class="price item-line-total">&#8369;<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            <form method="POST" action="remove_from_cart.php" style="margin-top:auto;">
                                <input type="hidden" name="<?= $item['ref_type'] ?>" value="<?= htmlspecialchars((string)$item['cart_key']) ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="cart-summary">
                <h2 class="summary-title">SUMMARY</h2>
                <div class="summary-row"><span>Subtotal</span><span id="sum-subtotal">&#8369;<?= number_format($subtotal, 2) ?></span></div>
                <div class="summary-row"><span>Shipping</span><span id="sum-shipping">&#8369;<?= number_format($shipping, 2) ?></span></div>
                <div class="summary-row"><span>Tax (12%)</span><span id="sum-tax">&#8369;<?= number_format($tax, 2) ?></span></div>
                <div class="summary-total">
                    <div class="summary-row"><span>Total</span><span id="sum-total">&#8369;<?= number_format($total, 2) ?></span></div>
                </div>
                <?php if (!$user_id && !empty($cart_items)): ?>
                    <a href="login.php?return=checkout.php">
                        <button class="checkout-btn">Login to Checkout</button>
                    </a>
                <?php else: ?>
                    <a href="checkout.php">
                        <button class="checkout-btn" <?= empty($cart_items) ? 'disabled' : '' ?>>
                            Proceed to Checkout
                        </button>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="cart-toast" id="cart-toast"></div>

    <footer class="footer">
        <div class="footer-content">
            <p class="footer-text">Contact us at Colegio de San Juan de Letran</p>
        </div>
    </footer>

    <script>
    (function() {
        function showToast(msg, isError) {
            var t = document.getElementById('cart-toast');
            t.textContent = msg;
            t.className = 'cart-toast' + (isError ? ' error' : '');
            void t.offsetWidth;
            t.classList.add('show');
            clearTimeout(t._timer);
            t._timer = setTimeout(function(){ t.classList.remove('show'); }, 2500);
        }

        function peso(n) { return '\u20B1' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

        function recalcTotals() {
            var sub = 0;
            document.querySelectorAll('.cart-item').forEach(function(row){
                var qty = parseInt(row.querySelector('.qty-display').textContent, 10);
                var price = parseFloat(row.dataset.price);
                sub += qty * price;
            });
            var shipping = sub > 0 ? 50 : 0;
            var tax = sub > 0 ? Math.round(sub * 0.10 * 100) / 100 : 0;
            var total = sub + shipping + tax;
            document.getElementById('sum-subtotal').textContent = peso(sub);
            document.getElementById('sum-shipping').textContent = peso(shipping);
            document.getElementById('sum-tax').textContent      = peso(tax);
            document.getElementById('sum-total').textContent    = peso(total);
        }

        function sendUpdate(row, action) {
            var fd = new FormData();
            fd.append('action', action);
            var refType = row.dataset.refType;
            fd.append(refType, row.dataset.cartKey);

            // disable buttons during request
            var btns = row.querySelectorAll('.qty-btn');
            btns.forEach(function(b){ b.disabled = true; });

            fetch('update_cart.php', { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if (!data.success) {
                        showToast(data.message || 'Could not update', true);
                        // re-enable buttons
                        btns.forEach(function(b){ b.disabled = false; });
                        // re-cap +
                        capPlus(row);
                        return;
                    }

                    if (data.removed) {
                        row.remove();
                        if (!document.querySelector('.cart-item')) {
                            // empty — reload to show empty state cleanly
                            window.location.reload();
                            return;
                        }
                        recalcTotals();
                        showToast('Item removed');
                        return;
                    }

                    // Update displayed quantity & line total
                    var qty = data.quantity;
                    row.querySelector('.qty-display').textContent = qty;
                    var price = parseFloat(row.dataset.price);
                    row.querySelector('.item-line-total').innerHTML = '\u20B1' + (qty * price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                    recalcTotals();

                    // re-enable buttons & cap +
                    btns.forEach(function(b){ b.disabled = false; });
                    capPlus(row);
                })
                .catch(function(){
                    showToast('Network error', true);
                    btns.forEach(function(b){ b.disabled = false; });
                    capPlus(row);
                });
        }

        function capPlus(row) {
            var qty = parseInt(row.querySelector('.qty-display').textContent, 10);
            var stock = parseInt(row.dataset.stock, 10);
            row.querySelector('.qty-inc').disabled = qty >= stock;
        }

        document.querySelectorAll('.cart-item').forEach(function(row) {
            row.querySelector('.qty-inc').addEventListener('click', function() {
                sendUpdate(row, 'inc');
            });
            row.querySelector('.qty-dec').addEventListener('click', function() {
                var qty = parseInt(row.querySelector('.qty-display').textContent, 10);
                if (qty <= 1) {
                    if (!confirm('Remove this item from your cart?')) return;
                }
                sendUpdate(row, 'dec');
            });
        });
    })();
    </script>
</body>
</html>