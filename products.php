<?php
require_once 'config.php';

// Products — show all, including out-of-stock (we handle display in JS)
$stmt = $pdo->query("
    SELECT p.id, p.name, p.price, p.stock, p.image, cat.name AS category
    FROM products p
    INNER JOIN categories cat ON cat.id = p.category_id
    ORDER BY cat.id, p.name
");
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];
foreach ($all as $row) {
    $grouped[$row['category']][] = $row;
}

// Variants with stock per product
$variantRows = $pdo->query("
    SELECT product_id, id, name, stock
    FROM product_variants
    ORDER BY product_id, sort_order
")->fetchAll(PDO::FETCH_ASSOC);

$variantMap = [];
foreach ($variantRows as $v) {
    $variantMap[$v['product_id']][] = $v;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS - Products</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="products.css">
    <!-- No external font requests — uses system monospace stack -->
    <style>
        .toast {
            position: fixed; bottom: 30px; right: 30px;
            background: #111; border: 1px solid #0099ff; color: #fff;
            padding: 14px 22px; font-family: ui-monospace,'Cascadia Code','Fira Code','Consolas',monospace;
            font-size: 13px; letter-spacing: 1px; z-index: 9999;
            opacity: 0; transform: translateY(20px);
            transition: opacity .3s, transform .3s; pointer-events: none;
        }
        .toast.show  { opacity: 1; transform: translateY(0); }
        .toast.error { border-color: #ff4444; color: #ff4444; }

        .stock-badge {
            display: inline-block;
            font-family: ui-monospace,'Cascadia Code','Fira Code','Consolas',monospace;
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 3px 10px;
            margin-bottom: 8px;
            border-radius: 2px;
        }
        .stock-ok   { background: #0d2b0d; color: #00cc66; border: 1px solid #00cc66; }
        .stock-low  { background: #2b1a00; color: #ffaa00; border: 1px solid #ffaa00; }
        .stock-out  { background: #2b0000; color: #ff4444; border: 1px solid #ff4444; }

        .add-to-cart:disabled { opacity: 0.4; cursor: not-allowed; }

        /* ── Search + Category Filters ─────────────────────────────── */
        .products-filters {
            padding: 10px 40px 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .filter-search-wrap {
            position: relative;
            max-width: 480px;
        }
        .filter-search-wrap input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            font-family: ui-monospace,'Cascadia Code','Fira Code','Consolas',monospace;
            font-size: 14px;
            border: 2px solid #000;
            background: #fff;
            color: #000;
            border-radius: 4px;
            outline: none;
            transition: border-color .2s;
        }
        .filter-search-wrap input:focus { border-color: #0099ff; }
        .filter-search-wrap .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #666;
            pointer-events: none;
        }
        .filter-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-btn {
            font-family: ui-monospace,'Cascadia Code','Fira Code','Consolas',monospace;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 8px 18px;
            background: #fff;
            color: #000;
            border: 2px solid #000;
            border-radius: 4px;
            cursor: pointer;
            transition: .2s;
        }
        .filter-btn:hover { background: #0099ff; border-color: #0099ff; color: #fff; }
        .filter-btn.active { background: #000; color: #fff; }
        .no-results-msg {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 14px;
            letter-spacing: 1px;
            font-family: ui-monospace,'Cascadia Code','Fira Code','Consolas',monospace;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <section class="products-hero"><h1>PRODUCTS</h1></section>

    <!-- ── SEARCH + CATEGORY FILTERS ──────────────────────────────── -->
    <section class="products-filters">
        <div class="filter-search-wrap">
            <input type="text" id="product-search" placeholder="Search products..." autocomplete="off">
            <span class="search-icon">⌕</span>
        </div>
        <div class="filter-categories" id="filter-categories">
            <button type="button" class="filter-btn active" data-category="all">All</button>
            <?php foreach (array_keys($grouped) as $catName): ?>
                <button type="button" class="filter-btn" data-category="<?= htmlspecialchars($catName, ENT_QUOTES) ?>">
                    <?= ucfirst(htmlspecialchars($catName)) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="products-container">

    <?php foreach ($grouped as $categoryName => $products): ?>
        <div class="section-header product-group" data-category="<?= htmlspecialchars($categoryName, ENT_QUOTES) ?>"><h1><?= ucfirst(htmlspecialchars($categoryName)) ?></h1><hr></div>

        <?php foreach ($products as $p):
            $uid      = 'p' . $p['id'];
            $variants = $variantMap[$p['id']] ?? [];

            // Build variant stock map as JSON for JS: {variantId: stock, ...}
            $variantStockJson = '{}';
            if (!empty($variants)) {
                $vsMap = [];
                foreach ($variants as $v) {
                    $vsMap[$v['id']] = (int)$v['stock'];
                }
                $variantStockJson = json_encode($vsMap);
            }

            // Initial stock to display
            $initialStock = !empty($variants) ? (int)$variants[0]['stock'] : (int)$p['stock'];
        ?>
        <div class="product-card product-item"
             data-category="<?= htmlspecialchars($p['category'], ENT_QUOTES) ?>"
             data-name="<?= htmlspecialchars(strtolower($p['name']), ENT_QUOTES) ?>">
            <div class="product-image">
                <?php if ($p['image']): ?>
                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                <?php else: ?>
                    <div style="width:100%;height:160px;background:#1a1a1a;display:flex;align-items:center;justify-content:center;color:#444;font-size:12px;">No Image</div>
                <?php endif; ?>
            </div>

            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <p>₱<?= number_format($p['price'], 0) ?></p>

            <!-- Stock badge — updates on variant change -->
            <div id="<?= $uid ?>-stock-badge" class="stock-badge <?= $initialStock <= 0 ? 'stock-out' : ($initialStock <= 10 ? 'stock-low' : 'stock-ok') ?>">
                <?php if ($initialStock <= 0): ?>
                    Out of Stock
                <?php else: ?>
                    <?= $initialStock ?> in stock
                <?php endif; ?>
            </div>

            <?php if (!empty($variants)): ?>
            <select id="<?= $uid ?>-variant"
                    data-stock-map="<?= htmlspecialchars($variantStockJson, ENT_QUOTES) ?>"
                    data-badge-id="<?= $uid ?>-stock-badge"
                    data-btn-id="<?= $uid ?>-btn"
                    data-qty-id="<?= $uid ?>-qty"
                    onchange="updateVariantStock(this)">
                <?php foreach ($variants as $v): ?>
                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <input type="number" value="1" min="1" max="<?= $initialStock > 0 ? $initialStock : 1 ?>" id="<?= $uid ?>-qty">

            <button class="add-to-cart nexus-atc"
                    id="<?= $uid ?>-btn"
                    data-product-id="<?= (int)$p['id'] ?>"
                    data-variant-select="<?= !empty($variants) ? $uid . '-variant' : '' ?>"
                    data-qty-id="<?= $uid ?>-qty"
                    <?= $initialStock <= 0 ? 'disabled' : '' ?>>
                <?= $initialStock <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
            </button>
        </div>
        <?php endforeach; ?>

    <?php endforeach; ?>

    <?php if (empty($grouped)): ?>
        <p style="text-align:center;color:#666;padding:60px;font-size:14px;letter-spacing:1px;">
            No products available right now.
        </p>
    <?php endif; ?>

    </section>

    <div class="toast" id="nexus-toast"></div>
    <script src="script.js"></script>
    <script>
    // ── Search + Category Filter ──────────────────────────────────
    (function () {
        var searchInput = document.getElementById('product-search');
        var categoryBtns = document.querySelectorAll('.filter-btn');
        var container    = document.querySelector('.products-container');

        // No-results message (added once, shown/hidden as needed)
        var noResults = document.createElement('p');
        noResults.className = 'no-results-msg';
        noResults.textContent = 'No products match your search.';
        noResults.style.display = 'none';
        container.appendChild(noResults);

        var activeCategory = 'all';

        function applyFilters() {
            var term = (searchInput.value || '').trim().toLowerCase();
            var anyVisible = false;

            // Filter individual product cards
            document.querySelectorAll('.product-item').forEach(function (card) {
                var matchesCat  = activeCategory === 'all' || card.dataset.category === activeCategory;
                var matchesTerm = !term || (card.dataset.name || '').indexOf(term) !== -1;
                var visible = matchesCat && matchesTerm;
                card.style.display = visible ? '' : 'none';
                if (visible) anyVisible = true;
            });

            // Hide category headers whose group has zero visible cards
            document.querySelectorAll('.product-group').forEach(function (header) {
                if (activeCategory !== 'all' && header.dataset.category !== activeCategory) {
                    header.style.display = 'none';
                    return;
                }
                // count siblings of same category that are visible
                var hasVisible = false;
                document.querySelectorAll('.product-item[data-category="' + header.dataset.category + '"]').forEach(function (c) {
                    if (c.style.display !== 'none') hasVisible = true;
                });
                header.style.display = hasVisible ? '' : 'none';
            });

            noResults.style.display = anyVisible ? 'none' : 'block';
        }

        // Wire up search input
        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        // Wire up category buttons
        categoryBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                categoryBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                activeCategory = btn.dataset.category;
                applyFilters();
            });
        });
    })();

    // ── Variant stock badge updater ───────────────────────────────
    function updateVariantStock(sel) {
        var stockMap  = JSON.parse(sel.dataset.stockMap || '{}');
        var variantId = sel.value;
        var stock     = stockMap[variantId] !== undefined ? stockMap[variantId] : 0;

        var badge = document.getElementById(sel.dataset.badgeId);
        var btn   = document.getElementById(sel.dataset.btnId);
        var qtyEl = document.getElementById(sel.dataset.qtyId);

        // Update badge
        badge.className = 'stock-badge';
        if (stock <= 0) {
            badge.className += ' stock-out';
            badge.textContent = 'Out of Stock';
        } else if (stock <= 10) {
            badge.className += ' stock-low';
            badge.textContent = stock + ' in stock';
        } else {
            badge.className += ' stock-ok';
            badge.textContent = stock + ' in stock';
        }

        // Enable/disable button
        btn.disabled = stock <= 0;
        if (!btn.disabled && btn.textContent.trim() === 'Out of Stock') {
            btn.textContent = 'Add to Cart';
        }
        if (btn.disabled) {
            btn.textContent = 'Out of Stock';
        }

        // Cap qty input to available stock
        if (qtyEl && stock > 0) {
            qtyEl.max = stock;
            if (parseInt(qtyEl.value) > stock) qtyEl.value = stock;
        }
    }

    // ── Add to Cart ───────────────────────────────────────────────
    (function () {
        function showToast(msg, isError) {
            var t = document.getElementById('nexus-toast');
            t.textContent = msg;
            t.className   = 'toast' + (isError ? ' error' : '');
            void t.offsetWidth;
            t.classList.add('show');
            clearTimeout(t._timer);
            t._timer = setTimeout(function(){ t.classList.remove('show'); }, 2500);
        }

        document.querySelectorAll('.nexus-atc').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopImmediatePropagation();

                var productId  = btn.dataset.productId;
                var variantSel = btn.dataset.variantSelect;
                var variantId  = variantSel ? document.getElementById(variantSel).value : '';
                var qtyEl      = document.getElementById(btn.dataset.qtyId);
                var quantity   = qtyEl ? parseInt(qtyEl.value, 10) : 1;

                btn.disabled    = true;
                btn.textContent = 'Adding…';

                var fd = new FormData();
                fd.append('product_id', productId);
                fd.append('quantity',   quantity);
                if (variantId) fd.append('variant_id', variantId);

                fetch('add_to_cart.php', { method: 'POST', body: fd })
                    .then(function(r){
                        if (!r.ok) {
                            return r.text().then(function(t){
                                throw new Error('Server error ' + r.status + ': ' + t.substring(0, 120));
                            });
                        }
                        return r.text().then(function(t){
                            try {
                                return JSON.parse(t);
                            } catch(e) {
                                throw new Error('Bad response: ' + t.substring(0, 120));
                            }
                        });
                    })
                    .then(function(data){
                        if (data.success) {
                            btn.textContent = 'ADDED ✓';
                            btn.style.background = '#0099ff';
                            showToast('Added to cart!', false);
                            setTimeout(function(){
                                btn.textContent = 'Add to Cart';
                                btn.style.background = '';
                                btn.disabled = false;
                            }, 1200);
                        } else {
                            showToast(data.message || 'Could not add to cart.', true);
                            if ((data.message||'').toLowerCase().includes('login')) {
                                setTimeout(function(){ window.location.href = 'login.php?return=products.php'; }, 1500);
                            }
                            btn.textContent = 'Add to Cart';
                            btn.style.background = '';
                            btn.disabled = false;
                        }
                    })
                    .catch(function(err){
                        showToast('Error: ' + (err.message || 'Network error. Try again.'), true);
                        btn.textContent = 'Add to Cart';
                        btn.style.background = '';
                        btn.disabled = false;
                    });
            }, true);
        });
    })();
    </script>
</body>
</html>