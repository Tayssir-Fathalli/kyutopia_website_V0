<?php
session_start();

$admin_password = "kyutopia2024";

if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin'] = true;
    } else {
        $error = "Mot de passe incorrect.";
    }
}

if (!isset($_SESSION['admin'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Kyutopia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width:400px;margin-top:80px;text-align:center">
        <h2 style="color:#e91e63">Admin Login</h2>
        <?php if (isset($error)): ?>
            <p style="color:red"><?= $error ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Mot de passe" required>
            <br><br>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

require 'db.php';

$tab       = $_GET['tab'] ?? 'orders';
$success   = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';

if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $update = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $update->execute([
        ':status' => $_POST['new_status'],
        ':id'     => intval($_POST['order_id'])
    ]);
}

$orders   = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// ── STATS DASHBOARD ────────────────────────────────────────────
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending      = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$done         = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='done'")->fetchColumn();
$cancelled    = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='cancelled'")->fetchColumn();

// Récupérer tous les champs product + quantity de la DB
$rows = $pdo->query("SELECT product, quantity FROM orders")->fetchAll(PDO::FETCH_ASSOC);

// Tableau pour compter les quantités par produit
$product_counts = [];

foreach ($rows as $row) {
    // Chaque commande contient : "Sukuna x2 | Berserk x1"
    // On split par " | " pour avoir chaque produit séparément
    $items = explode(' | ', $row['product']);

    foreach ($items as $item) {
        // On cherche le pattern "Nom du produit xQUANTITÉ"
        // preg_match extrait le nom et le chiffre après "x"
        if (preg_match('/^(.+)\s+x(\d+)$/', trim($item), $matches)) {
            $name = trim($matches[1]);  // ex: "Sukuna Finger Lighter Case"
            $qty  = intval($matches[2]); // ex: 2

            // On additionne les quantités pour ce produit
            if (isset($product_counts[$name])) {
                $product_counts[$name] += $qty;
            } else {
                $product_counts[$name] = $qty;
            }
        }
    }
}

// Trier par quantité décroissante
arsort($product_counts);

// Prendre le premier = best seller
$top_product = null;
if (!empty($product_counts)) {
    $top_name    = array_key_first($product_counts);
    $top_product = [
        'product' => $top_name,
        'total'   => $product_counts[$top_name]
    ];
}

$daily = $pdo->query("
    SELECT DATE(created_at) as day, COUNT(*) as count
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);

$chart_labels = array_column($daily, 'day');
$chart_data   = array_column($daily, 'count');

$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => intval($_GET['edit'])]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Kyutopia</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <style>
        .tabs{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap}
        .tab-btn{padding:8px 20px;border-radius:20px;border:1px solid #e91e63;background:white;color:#e91e63;cursor:pointer;font-size:14px;text-decoration:none}
        .tab-btn.active{background:#e91e63;color:white}
        table{width:100%;border-collapse:collapse;font-size:14px}
        th,td{padding:10px 12px;border-bottom:1px solid #eee;text-align:left}
        th{background:#e91e63;color:white}
        tr:hover{background:#fce4ec}
        .action-btns{display:flex;gap:6px}
        .btn-edit{background:#1976D2;color:white;border:none;padding:5px 12px;border-radius:5px;cursor:pointer;font-size:12px}
        .btn-delete{background:#e53935;color:white;border:none;padding:5px 12px;border-radius:5px;cursor:pointer;font-size:12px}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
        .alert-success{background:#d4edda;color:#155724;padding:10px 16px;border-radius:6px;margin-bottom:16px}
        .alert-error{background:#f8d7da;color:#721c24;padding:10px 16px;border-radius:6px;margin-bottom:16px}
        .product-img-preview{width:50px;height:50px;object-fit:cover;border-radius:6px}
        .stat-card{border-radius:10px;padding:20px;text-align:center}
        .stat-card p{font-size:13px;margin:0 0 8px}
        .stat-card h2{margin:0;font-size:32px}
    </style>
</head>
<body>

<div class="logo-section">
    <img src="images/kyutopia.png" alt="kyutopia">
</div>

<div class="container">

    <h2 style="color:#e91e63">⚙️ Admin Kyutopia</h2>

    <div class="tabs">
        <a href="admin.php?tab=orders"    class="tab-btn <?= $tab==='orders'    ? 'active' : '' ?>">📦 Commandes (<?= count($orders) ?>)</a>
        <a href="admin.php?tab=products"  class="tab-btn <?= $tab==='products'  ? 'active' : '' ?>">🛍️ Produits (<?= count($products) ?>)</a>
        <a href="admin.php?tab=dashboard" class="tab-btn <?= $tab==='dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert-success">✅ <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert-error">❌ <?= $error_msg ?></div>
    <?php endif; ?>


    <!-- ══ ONGLET COMMANDES ══════════════════════════════════════ -->
    <?php if ($tab === 'orders'): ?>

    <table>
        <tr>
            <th>#</th><th>Nom</th><th>Email</th><th>Téléphone</th>
            <th>Produits</th><th>Qté</th><th>Notes</th><th>Date</th><th>Statut</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
            <td><?= $order['email'] ?></td>
            <td><?= $order['phone'] ?></td>
            <td><?= $order['product'] ?></td>
            <td><?= $order['quantity'] ?></td>
            <td><?= $order['notes'] ?: '—' ?></td>
            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <select name="new_status" onchange="this.form.submit()" style="font-size:12px;padding:4px">
                        <option value="pending"   <?= $order['status']==='pending'   ? 'selected':'' ?>>En attente</option>
                        <option value="done"      <?= $order['status']==='done'      ? 'selected':'' ?>>Terminé</option>
                        <option value="cancelled" <?= $order['status']==='cancelled' ? 'selected':'' ?>>Annulé</option>
                    </select>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>


    <!-- ══ ONGLET PRODUITS ═══════════════════════════════════════ -->
    <?php elseif ($tab === 'products'): ?>

    <div style="border:1px solid #eee;border-radius:10px;padding:20px;margin-bottom:24px">
        <h3 style="color:#e91e63;margin-top:0">
            <?= $edit_product ? '✎ Modifier le produit' : '+ Ajouter un produit' ?>
        </h3>
        <form action="product_crud.php" method="post">
            <input type="hidden" name="action" value="<?= $edit_product ? 'update' : 'create' ?>">
            <?php if ($edit_product): ?>
                <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div>
                    <label>Nom du produit</label>
                    <input type="text" name="name" required
                           value="<?= $edit_product['name'] ?? '' ?>"
                           placeholder="ex: Kirby Necklace">
                </div>
                <div>
                    <label>Prix (DT)</label>
                    <input type="number" name="price" step="0.01" min="0" required
                           value="<?= $edit_product['price'] ?? '' ?>"
                           placeholder="ex: 30">
                </div>
                <div>
                    <label>Catégorie</label>
                    <select name="category">
                        <?php
                        $cats = ['lighter','keychain','necklace','earring','ashtray'];
                        foreach ($cats as $cat):
                            $sel = ($edit_product['category'] ?? '') === $cat ? 'selected' : '';
                        ?>
                        <option value="<?= $cat ?>" <?= $sel ?>><?= ucfirst($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Chemin image</label>
                    <input type="text" name="image" required
                           value="<?= $edit_product['image'] ?? '' ?>"
                           placeholder="ex: images/kirby.png">
                </div>
            </div>
            <button type="submit" style="background:#e91e63;color:white;padding:10px 24px;border-radius:6px;border:none;cursor:pointer">
                <?= $edit_product ? 'Enregistrer les modifications' : 'Ajouter le produit' ?>
            </button>
            <?php if ($edit_product): ?>
                <a href="admin.php?tab=products" style="margin-left:12px;color:#888;font-size:14px">Annuler</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <tr>
            <th>#</th><th>Image</th><th>Nom</th><th>Prix</th><th>Catégorie</th><th>Actions</th>
        </tr>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><img src="<?= $p['image'] ?>" class="product-img-preview" alt="<?= $p['name'] ?>"></td>
            <td><?= $p['name'] ?></td>
            <td><?= $p['price'] ?> DT</td>
            <td><?= $p['category'] ?></td>
            <td>
                <div class="action-btns">
                    <a href="admin.php?tab=products&edit=<?= $p['id'] ?>" class="btn-edit">✎ Modifier</a>
                    <form method="post" action="product_crud.php"
                          onsubmit="return confirm('Supprimer <?= $p['name'] ?> ?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn-delete">✕</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>


    <!-- ══ ONGLET DASHBOARD ══════════════════════════════════════ -->
    <?php elseif ($tab === 'dashboard'): ?>

    <!-- CARTES STATS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">

        <div class="stat-card" style="background:#fce4ec">
            <p style="color:#e91e63">Total commandes</p>
            <h2 style="color:#c2185b"><?= $total_orders ?></h2>
        </div>

        <div class="stat-card" style="background:#E8F5E9">
            <p style="color:#2e7d32">Terminées</p>
            <h2 style="color:#2e7d32"><?= $done ?></h2>
        </div>

        <div class="stat-card" style="background:#FFF3E0">
            <p style="color:#e65100">En attente</p>
            <h2 style="color:#e65100"><?= $pending ?></h2>
        </div>

        <div class="stat-card" style="background:#ffebee">
            <p style="color:#c62828">Annulées</p>
            <h2 style="color:#c62828"><?= $cancelled ?></h2>
        </div>

    </div>

    <!-- PRODUIT TOP -->
    <?php if ($top_product): ?>
    <div style="background:#f3e5f5;border-radius:10px;padding:16px;margin-bottom:24px;display:flex;align-items:center;gap:16px">
        <span style="font-size:32px">🏆</span>
        <div>
            <p style="margin:0;font-size:13px;color:#7b1fa2">Produit le plus commandé</p>
            <p style="margin:4px 0 0;font-weight:bold;color:#4a148c;font-size:16px">
                <?= $top_product['product'] ?> — <?= $top_product['total'] ?> commande(s)
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- GRAPHIQUE -->
    <div style="background:#fff;border:1px solid #eee;border-radius:10px;padding:20px">
        <h3 style="color:#e91e63;margin-top:0">Commandes des 7 derniers jours</h3>
        <?php if (empty($chart_data)): ?>
            <p style="color:#aaa;text-align:center;padding:40px 0">Aucune commande cette semaine.</p>
        <?php else: ?>
            <canvas id="ordersChart" height="100"></canvas>
            <script>
            const ctx = document.getElementById('ordersChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        label: 'Commandes',
                        data: <?= json_encode($chart_data) ?>,
                        borderColor: '#e91e63',
                        backgroundColor: 'rgba(233,30,99,0.08)',
                        borderWidth: 2,
                        pointBackgroundColor: '#e91e63',
                        pointRadius: 5,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
            </script>
        <?php endif; ?>
    </div>

    <?php endif; ?>

    <br>
    <a href="index.php" class="btn">← Retour au site</a>

</div>
</body>
</html>