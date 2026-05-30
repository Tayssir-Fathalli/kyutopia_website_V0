<?php
require 'db.php';

// On lit l'action demandée : "save" ou "get"
$action = $_GET['action'] ?? '';

// ── SAUVEGARDER UN NOUVEL AVIS ─────────────────────────────────
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $product = htmlspecialchars(trim($_POST['product']));
    $name    = htmlspecialchars(trim($_POST['name']));
    $stars   = intval($_POST['stars']);
    $comment = htmlspecialchars(trim($_POST['comment']));

    // Validation basique
    if (empty($product) || empty($name) || empty($comment) || $stars < 1 || $stars > 5) {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        exit;
    }

    $sql  = "INSERT INTO reviews (product, name, stars, comment) VALUES (:product, :name, :stars, :comment)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':product' => $product,
        ':name'    => $name,
        ':stars'   => $stars,
        ':comment' => $comment
    ]);

    echo json_encode(['success' => true]);
    exit;
}

// ── RÉCUPÉRER LES AVIS D'UN PRODUIT ───────────────────────────
if ($action === 'get') {

    $product = htmlspecialchars(trim($_GET['product'] ?? ''));

    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE product = :product ORDER BY created_at DESC");
    $stmt->execute([':product' => $product]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // On calcule la moyenne des étoiles
    $average = 0;
    if (count($reviews) > 0) {
        $total   = array_sum(array_column($reviews, 'stars'));
        $average = round($total / count($reviews), 1);
    }

    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'average' => $average,
        'count'   => count($reviews)
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action inconnue']);
?>