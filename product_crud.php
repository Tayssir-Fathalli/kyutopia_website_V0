<?php
require 'db.php';

$action = $_POST['action'] ?? '';

// ── CREATE — Ajouter un produit ────────────────────────────────
if ($action === 'create') {

    $name     = htmlspecialchars(trim($_POST['name']));
    $price    = floatval($_POST['price']);
    $category = htmlspecialchars(trim($_POST['category']));
    $image    = htmlspecialchars(trim($_POST['image']));

    if (empty($name) || empty($image) || $price <= 0) {
        header('Location: admin.php?tab=products&error=Champs manquants');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, price, category, image) VALUES (:name, :price, :category, :image)");
    $stmt->execute([
        ':name'     => $name,
        ':price'    => $price,
        ':category' => $category,
        ':image'    => $image
    ]);

    header('Location: admin.php?tab=products&success=Produit ajouté');
    exit;
}

// ── UPDATE — Modifier un produit ───────────────────────────────
if ($action === 'update') {

    $id       = intval($_POST['id']);
    $name     = htmlspecialchars(trim($_POST['name']));
    $price    = floatval($_POST['price']);
    $category = htmlspecialchars(trim($_POST['category']));
    $image    = htmlspecialchars(trim($_POST['image']));

    $stmt = $pdo->prepare("UPDATE products SET name=:name, price=:price, category=:category, image=:image WHERE id=:id");
    $stmt->execute([
        ':name'     => $name,
        ':price'    => $price,
        ':category' => $category,
        ':image'    => $image,
        ':id'       => $id
    ]);

    header('Location: admin.php?tab=products&success=Produit modifié');
    exit;
}

// ── DELETE — Supprimer un produit ──────────────────────────────
if ($action === 'delete') {

    $id = intval($_POST['id']);

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: admin.php?tab=products&success=Produit supprimé');
    exit;
}

header('Location: admin.php');
?>