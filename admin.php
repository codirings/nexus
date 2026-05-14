<?php
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$message = '';
$error   = '';

/* ─────────────────────────────────────────────
   HELPERS
───────────────────────────────────────────── */
function post(string $key, $default = ''): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function nexus_effective_stock(array $p): int {
    if (!empty($p['variants'])) {
        $lines = array_filter(array_map('trim', explode("\n", $p['variants'])));
        if (!empty($lines)) {
            $sum = 0;
            foreach ($lines as $line) {
                $parts = explode('|', $line, 2);
                $sum  += isset($parts[1]) ? max(0, (int)trim($parts[1])) : 0;
            }
            return $sum;
        }
    }
    return (int)$p['stock'];
}

function insert_variants(PDO $pdo, int $product_id, string $raw): void {
    $lines   = array_filter(array_map('trim', explode("\n", $raw)));
    $varStmt = $pdo->prepare(
        "INSERT INTO product_variants (product_id, name, stock, sort_order) VALUES (?, ?, ?, ?)"
    );
    foreach (array_values($lines) as $i => $line) {
        $parts  = explode('|', $line, 2);
        $vname  = trim($parts[0]);
        $vstock = isset($parts[1]) ? max(0, (int)trim($parts[1])) : 0;
        if ($vname !== '') {
            $varStmt->execute([$product_id, $vname, $vstock, $i + 1]);
        }
    }
}

/* ─────────────────────────────────────────────
   ACTIONS
───────────────────────────────────────────── */
$action = post('action');

// ADD PRODUCT
if ($action === 'add') {
    $name     = post('name');
    $category = post('category');
    $price    = (float) post('price', '0');
    $stock    = (int)   post('stock',  '0');
    $image    = post('image') ?: null;
    $variants = post('variants');

    if ($name === '' || $category === '' || $price <= 0) {
        $error = "Name, category and price are required.";
    } else {
        $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $catStmt->execute([$category]);
        $category_id = $catStmt->fetchColumn();

        $pdo->prepare(
            "INSERT INTO products (name, category_id, price, stock, image) VALUES (?, ?, ?, ?, ?)"
        )->execute([$name, $category_id, $price, $stock, $image]);

        $pid = (int) $pdo->lastInsertId();

        if ($variants !== '') {
            insert_variants($pdo, $pid, $variants);
        }
        if ($stock > 0) {
            $pdo->prepare(
                "INSERT INTO stock_log (product_id, change_qty, reason) VALUES (?, ?, 'manual')"
            )->execute([$pid, $stock]);
        }
        $message = "Product '$name' added successfully.";
    }
}

// EDIT PRODUCT
if ($action === 'edit') {
    $id       = (int)   post('product_id');
    $name     = post('name');
    $category = post('category');
    $price    = (float) post('price', '0');
    $newStock = (int)   post('stock',  '0');
    $image    = post('image') ?: null;
    $variants = post('variants');

    $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $catStmt->execute([$category]);
    $category_id = $catStmt->fetchColumn();

    $oldStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $oldStmt->execute([$id]);
    $oldStock = (int) $oldStmt->fetchColumn();
    $diff = $newStock - $oldStock;

    $pdo->prepare(
        "UPDATE products SET name = ?, category_id = ?, price = ?, stock = ?, image = ? WHERE id = ?"
    )->execute([$name, $category_id, $price, $newStock, $image, $id]);

    $pdo->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);

    if ($variants !== '') {
        insert_variants($pdo, $id, $variants);
    }
    if ($diff !== 0) {
        $pdo->prepare(
            "INSERT INTO stock_log (product_id, change_qty, reason) VALUES (?, ?, 'adjustment')"
        )->execute([$id, $diff]);
    }
    $message = "Product updated successfully.";
}

// DELETE PRODUCT
if ($action === 'delete') {
    $id = (int) post('product_id');
    if ($id > 0) {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $message = "Product deleted.";
    }
}

// UPDATE ORDER STATUS
if ($action === 'update_order_status') {
    $oid     = (int) post('order_id');
    $status  = post('order_status');
    $allowed = ['pending', 'processing', 'shipped', 'completed', 'cancelled', 'refund_requested', 'refunded'];
    if ($oid > 0 && in_array($status, $allowed, true)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $oid]);
        $message = "Order #$oid status updated to " . ucfirst($status) . ".";
    } else {
        $error = "Invalid order or status.";
    }
}

/* ─────────────────────────────────────────────
   DATA QUERIES
───────────────────────────────────────────── */
$products = $pdo->query("
    SELECT p.*, c.name AS category,
           GROUP_CONCAT(
               CONCAT(pv.name, '|', pv.stock)
               ORDER BY pv.sort_order
               SEPARATOR '\n'
           ) AS variants
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    GROUP BY p.id
    ORDER BY c.name, p.name
")->fetchAll();

$orders = $pdo->query("
    SELECT
        o.id, o.total, o.status, o.created_at,
        a.address, a.city, a.phone,
        pm.label AS payment_method,
        COALESCE(u.fullname, 'Guest') AS customer,
        GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', ') AS item_names,
        COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    LEFT JOIN addresses a ON a.id = o.address_id
    LEFT JOIN payment_methods pm ON pm.id = o.payment_method_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetchAll();

$logs = $pdo->query("
    SELECT sl.created_at, p.name, sl.change_qty, sl.reason, sl.order_id
    FROM stock_log sl
    JOIN products p ON p.id = sl.product_id
    ORDER BY sl.created_at DESC
    LIMIT 50
")->fetchAll();

$allCategories = $pdo->query("SELECT name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

$totalProducts = count($products);
$totalOrders   = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue  = (float) $pdo->query(
    "SELECT COALESCE(SUM(total), 0) FROM orders WHERE status NOT IN ('cancelled','refunded')"
)->fetchColumn();
$lowStock      = array_filter($products, fn($p) => nexus_effective_stock($p) <= 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        /* ── RESET & BASE ─────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg:       #080808;
            --surface:  #0f0f0f;
            --surface2: #161616;
            --border:   #1e1e1e;
            --border2:  #2a2a2a;
            --text:     #e8e8e8;
            --muted:    #555;
            --dim:      #333;
            --blue:     #0088ee;
            --blue-dim: #002244;
            --green:    #00bb55;
            --green-dim:#002211;
            --red:      #ee3344;
            --red-dim:  #2a0008;
            --amber:    #ffaa00;
            --amber-dim:#2a1800;
            --radius:   2px;
        }

        html, body { height: 100%; }
        body {
            background: var(--bg);
            font-family: 'DM Mono', monospace;
            color: var(--text);
            font-size: 13px;
            line-height: 1.6;
        }

        /* ── LAYOUT ───────────────────────────────── */
        .layout { display: flex; min-height: 100vh; }

        /* ── SIDEBAR ──────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 200px; height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            z-index: 50;
        }
        .sidebar-logo {
            padding: 28px 24px 24px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo .wordmark {
            font-family: 'Space Mono', monospace;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 4px;
            color: var(--blue);
        }
        .sidebar-logo .sub {
            font-size: 10px;
            letter-spacing: 2px;
            color: var(--muted);
            text-transform: uppercase;
            margin-top: 3px;
        }
        .nav { flex: 1; padding: 16px 0; }
        .nav-btn {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 12px 24px;
            background: none; border: none;
            color: var(--muted);
            font-family: 'DM Mono', monospace;
            font-size: 11px; letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer; text-align: left;
            transition: color .15s, background .15s;
            border-left: 2px solid transparent;
        }
        .nav-btn:hover { color: var(--text); background: var(--surface2); }
        .nav-btn.active { color: #fff; border-left-color: var(--blue); background: var(--surface2); }
        .nav-btn .icon { font-size: 14px; width: 18px; text-align: center; }
        .nav-logout {
            display: flex; align-items: center; gap: 10px;
            padding: 16px 24px;
            color: var(--red);
            text-decoration: none;
            font-size: 11px; letter-spacing: 1.5px;
            text-transform: uppercase;
            border-top: 1px solid var(--border);
            transition: background .15s;
        }
        .nav-logout:hover { background: var(--red-dim); }

        /* ── MAIN ─────────────────────────────────── */
        .main { margin-left: 200px; padding: 40px 44px; flex: 1; }
        .page-header { margin-bottom: 36px; }
        .page-title {
            font-family: 'Space Mono', monospace;
            font-size: 22px; font-weight: 700;
            letter-spacing: 4px; color: #fff;
        }
        .page-sub { font-size: 11px; color: var(--muted); letter-spacing: 1px; margin-top: 4px; }

        /* ── ALERTS ───────────────────────────────── */
        .alert {
            padding: 12px 16px; margin-bottom: 24px;
            font-size: 12px; border-radius: var(--radius);
            border-left: 3px solid;
        }
        .alert-success { background: var(--green-dim); border-color: var(--green); color: var(--green); }
        .alert-error   { background: var(--red-dim);   border-color: var(--red);   color: var(--red);   }

        /* ── STATS ────────────────────────────────── */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px; margin-bottom: 36px;
        }
        .stat {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 22px 24px;
        }
        .stat-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: var(--muted); margin-bottom: 10px; }
        .stat-value { font-family: 'Space Mono', monospace; font-size: 26px; font-weight: 700; }
        .stat-value.blue  { color: var(--blue); }
        .stat-value.green { color: var(--green); }
        .stat-value.red   { color: var(--red); }
        .stat-value.white { color: #fff; }

        /* ── SECTIONS ─────────────────────────────── */
        .section { display: none; }
        .section.active { display: block; }

        /* ── PANEL ────────────────────────────────── */
        .panel { background: var(--surface); border: 1px solid var(--border); margin-bottom: 24px; }
        .panel-head {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .panel-title {
            font-family: 'Space Mono', monospace;
            font-size: 13px; letter-spacing: 2px; font-weight: 700;
        }
        .panel-body { padding: 24px; }

        /* ── FORM ─────────────────────────────────── */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px; margin-bottom: 16px;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.span2 { grid-column: span 2; }
        .form-group.span3 { grid-column: span 3; }
        label {
            font-size: 10px; text-transform: uppercase;
            letter-spacing: 1.5px; color: var(--muted);
        }
        input[type="text"],
        input[type="number"],
        select, textarea {
            background: var(--surface2);
            border: 1px solid var(--border2);
            color: var(--text);
            padding: 10px 12px;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            border-radius: var(--radius);
            transition: border-color .15s;
            width: 100%;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: var(--blue);
        }
        input:disabled { opacity: 0.3; cursor: not-allowed; }
        select option { background: var(--surface2); }
        textarea { resize: vertical; min-height: 140px; line-height: 1.7; }
        .hint { font-size: 10px; color: var(--muted); margin-top: 2px; }

        /* ── BUTTONS ──────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 20px;
            font-family: 'DM Mono', monospace;
            font-size: 11px; font-weight: 500;
            letter-spacing: 1.5px; text-transform: uppercase;
            cursor: pointer; border: none;
            border-radius: var(--radius);
            transition: background .15s, opacity .15s;
        }
        .btn-primary  { background: var(--blue); color: #fff; }
        .btn-primary:hover  { background: #006ecc; }
        .btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border2); }
        .btn-secondary:hover { background: var(--dim); }
        .btn-sm { padding: 6px 12px; font-size: 10px; }
        .btn-danger { background: var(--red-dim); color: var(--red); border: 1px solid var(--red-dim); }
        .btn-danger:hover { background: var(--red); color: #fff; }
        .btn-edit { background: var(--surface2); color: var(--text); border: 1px solid var(--border2); }
        .btn-edit:hover { border-color: var(--blue); color: var(--blue); }

        /* ── TABLE ────────────────────────────────── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 10px 16px;
            font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            text-align: left; white-space: nowrap;
        }
        td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle; color: #bbb;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--surface2); }
        .td-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

        /* ── BADGES ───────────────────────────────── */
        .badge {
            display: inline-block; padding: 3px 8px;
            font-size: 10px; font-weight: 500;
            text-transform: uppercase; letter-spacing: 1px;
            border-radius: var(--radius); border: 1px solid;
        }
        .badge-ok     { background: var(--green-dim); color: var(--green); border-color: #005522; }
        .badge-warn   { background: var(--amber-dim); color: var(--amber); border-color: #443300; }
        .badge-low    { background: var(--red-dim);   color: var(--red);   border-color: #440011; }
        .badge-blue   { background: var(--blue-dim);  color: var(--blue);  border-color: #003366; }

        /* ── STATUS SELECT ROW ────────────────────── */
        .status-form { display: flex; gap: 6px; align-items: center; min-width: 200px; }
        .status-select {
            flex: 1;
            background: var(--surface2);
            border: 1px solid var(--border2);
            color: var(--text);
            padding: 6px 8px;
            font-family: 'DM Mono', monospace; font-size: 11px;
            border-radius: var(--radius);
        }
        .btn-save {
            background: var(--blue); color: #fff; border: none;
            padding: 6px 12px;
            font-family: 'DM Mono', monospace; font-size: 10px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            cursor: pointer; border-radius: var(--radius);
            white-space: nowrap;
        }
        .btn-save:hover { background: #006ecc; }

        /* ── STOCK LOG COLORS ─────────────────────── */
        .pos { color: var(--green); }
        .neg { color: var(--red); }

        /* ── MODAL ────────────────────────────────── */
        .overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.88);
            z-index: 200;
            justify-content: center; align-items: flex-start;
            padding: 40px 20px; overflow-y: auto;
        }
        .overlay.open { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border2);
            padding: 36px; width: 100%; max-width: 580px;
            border-radius: var(--radius);
        }
        .modal-title {
            font-family: 'Space Mono', monospace;
            font-size: 16px; letter-spacing: 2px;
            margin-bottom: 28px;
        }
        .modal-footer { display: flex; gap: 10px; margin-top: 24px; }

        /* ── MISC ─────────────────────────────────── */
        .empty-row td { text-align: center; color: var(--muted); padding: 40px; }
        .text-dim { color: var(--muted); }
        .text-sm  { font-size: 11px; }
        .ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 160px; }
        .divider { border: none; border-top: 1px solid var(--border); margin: 24px 0; }

        /* ── IMAGE FILE PICKER ────────────────────── */
        .img-input-wrap { display: flex; gap: 8px; align-items: stretch; }
        .img-input-wrap input[type="text"] { flex: 1; min-width: 0; }
        .btn-browse {
            padding: 0 14px;
            background: var(--surface2);
            border: 1px solid var(--border2);
            color: var(--blue);
            font-family: 'DM Mono', monospace;
            font-size: 11px; letter-spacing: 1px; text-transform: uppercase;
            cursor: pointer; border-radius: var(--radius);
            white-space: nowrap; display: flex; align-items: center; gap: 5px;
            transition: border-color .15s, background .15s;
        }
        .btn-browse:hover { border-color: var(--blue); background: var(--blue-dim); }
        .img-preview {
            margin-top: 8px;
            display: none;
            width: 72px; height: 72px;
            object-fit: cover;
            border: 1px solid var(--border2);
            border-radius: var(--radius);
        }
    </style>
</head>
<body>
<div class="layout">

    <!-- ── SIDEBAR ──────────────────────────────── -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="wordmark">NEXUS</div>
            <div class="sub">Admin Panel</div>
        </div>
        <nav class="nav">
            <button class="nav-btn active" onclick="showSection('products', this)">
                <span class="icon">📦</span> Products
            </button>
            <button class="nav-btn" onclick="showSection('orders', this)">
                <span class="icon">🧾</span> Orders
            </button>
            <button class="nav-btn" onclick="showSection('stocklog', this)">
                <span class="icon">📋</span> Stock Log
            </button>
        </nav>
        <a href="admin_login.php?logout=1" class="nav-logout">
            <span class="icon">⏻</span> Logout
        </a>
    </aside>

    <!-- ── MAIN ─────────────────────────────────── -->
    <main class="main">

        <div class="page-header">
            <div class="page-title">STOCK MANAGER</div>
            <div class="page-sub">Inventory · Orders · Activity Log</div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stats">
            <div class="stat">
                <div class="stat-label">Total Products</div>
                <div class="stat-value blue"><?= $totalProducts ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value white"><?= $totalOrders ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Gross Revenue</div>
                <div class="stat-value green">₱<?= number_format($totalRevenue, 2) ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Low Stock Items</div>
                <div class="stat-value red"><?= count($lowStock) ?></div>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             SECTION: PRODUCTS
        ══════════════════════════════════════ -->
        <div id="section-products" class="section active">

            <!-- Add Product -->
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">ADD PRODUCT</div>
                </div>
                <div class="panel-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="form-grid">
                            <div class="form-group span2">
                                <label>Product Name</label>
                                <input type="text" name="name" required placeholder="e.g. NEXUS PowerCase Pro">
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" required>
                                    <?php foreach ($allCategories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Price (₱)</label>
                                <input type="number" name="price" required min="1" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group" id="add-stock-field">
                                <label>Base Stock</label>
                                <input type="number" name="stock" id="add-stock-input" min="0" value="0">
                                <span class="hint">Ignored when variants are defined</span>
                            </div>
                            <div class="form-group">
                                <label>Image Path</label>
                                <div class="img-input-wrap">
                                    <input type="text" name="image" id="add-image" placeholder="images/product.png">
                                    <input type="file" id="add-image-file" accept="image/*" style="display:none" onchange="handleFilePick(this,'add-image','add-image-preview')">
                                    <button type="button" class="btn-browse" onclick="document.getElementById('add-image-file').click()">🗂 Browse</button>
                                </div>
                                <img id="add-image-preview" class="img-preview" alt="preview">
                                <span class="hint">Pick a file or type the path manually</span>
                            </div>
                            <div class="form-group span2">
                                <label>Variants — one per line (Name|Stock)</label>
                                <textarea
                                    name="variants"
                                    id="add-variants-input"
                                    placeholder="iPhone 17|50&#10;iPhone 16|30&#10;iPhone 15|0"
                                    oninput="syncStockField(this,'add-stock-field','add-stock-input')"
                                ></textarea>
                                <span class="hint">Format: <strong>Variant Name|Stock</strong>. When set, per-variant stock is used instead of base stock.</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </form>
                </div>
            </div>

            <!-- Product List -->
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">ALL PRODUCTS</div>
                    <span class="text-dim text-sm"><?= $totalProducts ?> total</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Variants</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $p):
                            $rawLines    = $p['variants'] ? array_filter(array_map('trim', explode("\n", $p['variants']))) : [];
                            $hasVariants = !empty($rawLines);

                            if ($hasVariants) {
                                $totalStock = array_sum(array_map(function ($line) {
                                    $parts = explode('|', $line, 2);
                                    return isset($parts[1]) ? max(0, (int)trim($parts[1])) : 0;
                                }, $rawLines));
                            } else {
                                $totalStock = (int)$p['stock'];
                            }

                            $sc = $totalStock <= 0 ? 'badge-low' : ($totalStock <= 10 ? 'badge-warn' : 'badge-ok');
                            $sl = $totalStock <= 0 ? 'Out of Stock' : ($totalStock <= 10 ? 'Low Stock' : 'In Stock');

                            $variantCount   = count($rawLines);
                            $variantsText   = implode("\n", $rawLines);
                            $variantNames   = array_map(fn($l) => explode('|', $l, 2)[0], $rawLines);
                        ?>
                            <tr>
                                <td class="text-dim"><?= $p['id'] ?></td>
                                <td style="color:#e8e8e8;font-weight:500"><?= htmlspecialchars($p['name']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($p['category'])) ?></td>
                                <td>₱<?= number_format((float)$p['price'], 2) ?></td>
                                <td>
                                    <strong style="color:#e8e8e8"><?= $totalStock ?></strong>
                                    <?php if ($hasVariants): ?>
                                        <br><span class="text-dim text-sm">via variants</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($variantCount > 0): ?>
                                        <span class="ellipsis" title="<?= htmlspecialchars(implode(', ', $variantNames)) ?>">
                                            <?= $variantCount ?> variant<?= $variantCount !== 1 ? 's' : '' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-dim">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $sc ?>"><?= $sl ?></span></td>
                                <td>
                                    <div class="td-actions">
                                        <button class="btn btn-edit btn-sm"
                                            data-id="<?= $p['id'] ?>"
                                            data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                                            data-category="<?= htmlspecialchars($p['category'], ENT_QUOTES) ?>"
                                            data-price="<?= $p['price'] ?>"
                                            data-stock="<?= $p['stock'] ?>"
                                            data-image="<?= htmlspecialchars($p['image'] ?? '', ENT_QUOTES) ?>"
                                            data-variants="<?= htmlspecialchars($variantsText, ENT_QUOTES) ?>"
                                            onclick="openEdit(this)">
                                            Edit
                                        </button>
                                        <form method="POST" onsubmit="return confirm('Delete this product?')" style="display:inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                            <tr class="empty-row"><td colspan="8">No products found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /section-products -->

        <!-- ══════════════════════════════════════
             SECTION: ORDERS
        ══════════════════════════════════════ -->
        <div id="section-orders" class="section">
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">ALL ORDERS</div>
                    <span class="text-dim text-sm"><?= $totalOrders ?> total</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Shipping</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td style="color:#e8e8e8;font-family:'Space Mono',monospace;font-weight:700">
                                    #NX<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td><?= htmlspecialchars($o['customer']) ?></td>
                                <td>
                                    <span class="text-sm"><?= htmlspecialchars($o['item_names'] ?? '—') ?></span>
                                    <br><span class="text-dim text-sm"><?= (int)$o['item_count'] ?> item(s)</span>
                                </td>
                                <td style="color:#e8e8e8">₱<?= number_format((float)$o['total'], 2) ?></td>
                                <td class="text-sm" style="max-width:160px">
                                    <?php if ($o['address']): ?>
                                        <?= htmlspecialchars($o['address']) ?>
                                        <?= $o['city'] ? ', ' . htmlspecialchars($o['city']) : '' ?>
                                        <?php if ($o['phone']): ?>
                                            <br><span class="text-dim">📞 <?= htmlspecialchars($o['phone']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-dim">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm text-dim"><?= htmlspecialchars($o['payment_method'] ?? '—') ?></td>
                                <td class="text-sm text-dim"><?= date('M d, Y', strtotime($o['created_at'])) ?><br><?= date('h:i A', strtotime($o['created_at'])) ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="action" value="update_order_status">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <select name="order_status" class="status-select">
                                            <?php foreach (['pending','processing','shipped','completed','cancelled'] as $s): ?>
                                                <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>>
                                                    <?= ucfirst($s) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-save">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                            <tr class="empty-row"><td colspan="8">No orders yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /section-orders -->

        <!-- ══════════════════════════════════════
             SECTION: STOCK LOG
        ══════════════════════════════════════ -->
        <div id="section-stocklog" class="section">
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">STOCK CHANGE LOG</div>
                    <span class="text-dim text-sm">Last 50 entries</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Date</th><th>Product</th><th>Change</th><th>Reason</th><th>Order #</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="text-sm text-dim"><?= date('M d, Y h:i A', strtotime($l['created_at'])) ?></td>
                                <td><?= htmlspecialchars($l['name']) ?></td>
                                <td class="<?= $l['change_qty'] >= 0 ? 'pos' : 'neg' ?>" style="font-family:'Space Mono',monospace;font-weight:700">
                                    <?= $l['change_qty'] >= 0 ? '+' : '' ?><?= $l['change_qty'] ?>
                                </td>
                                <td><?= ucfirst(htmlspecialchars($l['reason'])) ?></td>
                                <td><?= $l['order_id'] ? '#' . $l['order_id'] : '<span class="text-dim">—</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr class="empty-row"><td colspan="5">No stock changes logged yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /section-stocklog -->

    </main>
</div>

<!-- ── EDIT MODAL ────────────────────────────────── -->
<div class="overlay" id="editOverlay" onclick="handleOverlayClick(event)">
    <div class="modal">
        <div class="modal-title">EDIT PRODUCT</div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" id="edit-id">
            <div class="form-grid">
                <div class="form-group span2">
                    <label>Product Name</label>
                    <input type="text" name="name" id="edit-name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="edit-category">
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Price (₱)</label>
                    <input type="number" name="price" id="edit-price" required min="1" step="0.01">
                </div>
                <div class="form-group" id="edit-stock-field">
                    <label>Base Stock</label>
                    <input type="number" name="stock" id="edit-stock" min="0" value="0">
                    <span class="hint">Ignored when variants are defined</span>
                </div>
                <div class="form-group">
                    <label>Image Path</label>
                    <div class="img-input-wrap">
                        <input type="text" name="image" id="edit-image" placeholder="images/product.png">
                        <input type="file" id="edit-image-file" accept="image/*" style="display:none" onchange="handleFilePick(this,'edit-image','edit-image-preview')">
                        <button type="button" class="btn-browse" onclick="document.getElementById('edit-image-file').click()">🗂 Browse</button>
                    </div>
                    <img id="edit-image-preview" class="img-preview" alt="preview">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label>Variants — one per line (Name|Stock)</label>
                <textarea
                    name="variants"
                    id="edit-variants"
                    oninput="syncStockField(this,'edit-stock-field','edit-stock')"
                ></textarea>
                <span class="hint">Format: <strong>Variant Name|Stock</strong>. When set, per-variant stock is used instead of base stock.</span>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeEdit()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    /* ── SECTION SWITCHER ─────────────────────── */
    function showSection(name, btn) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('section-' + name).classList.add('active');
        if (btn) btn.classList.add('active');
    }

    /* ── STOCK FIELD SYNC ─────────────────────── */
    function syncStockField(textarea, fieldId, inputId) {
        const hasVariants = textarea.value.trim().length > 0;
        const field = document.getElementById(fieldId);
        const input = document.getElementById(inputId);
        field.style.opacity = hasVariants ? '0.3' : '1';
        if (input) input.disabled = hasVariants;
    }

    /* ── NATIVE FILE PICKER ───────────────────── */
    function handleFilePick(fileInput, textInputId, previewId) {
        const file = fileInput.files[0];
        if (!file) return;

        // Populate the path field with images/<filename>
        document.getElementById(textInputId).value = 'images/' + file.name;

        // Show a live preview
        const preview = document.getElementById(previewId);
        const reader  = new FileReader();
        reader.onload = e => {
            preview.src   = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    /* ── EDIT MODAL ───────────────────────────── */
    function openEdit(btn) {
        const d = btn.dataset;
        document.getElementById('edit-id').value       = d.id;
        document.getElementById('edit-name').value     = d.name;
        document.getElementById('edit-price').value    = d.price;
        document.getElementById('edit-stock').value    = d.stock;
        document.getElementById('edit-image').value    = d.image;
        document.getElementById('edit-variants').value = d.variants;

        // Reset file input & preview
        document.getElementById('edit-image-file').value = '';
        const prev = document.getElementById('edit-image-preview');
        if (d.image) {
            prev.src = d.image;
            prev.style.display = 'block';
        } else {
            prev.style.display = 'none';
        }

        const catSelect = document.getElementById('edit-category');
        [...catSelect.options].forEach(o => o.selected = (o.value === d.category));

        syncStockField(
            document.getElementById('edit-variants'),
            'edit-stock-field',
            'edit-stock'
        );

        document.getElementById('editOverlay').classList.add('open');
    }

    function closeEdit() {
        document.getElementById('editOverlay').classList.remove('open');
    }

    function handleOverlayClick(e) {
        if (e.target === document.getElementById('editOverlay')) closeEdit();
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeEdit();
    });
</script>
</body>
</html>