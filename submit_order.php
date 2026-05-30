<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: order.html');
    exit;
}

$first_name = htmlspecialchars(trim($_POST['First_name']));
$last_name  = htmlspecialchars(trim($_POST['Last_name']));
$email      = htmlspecialchars(trim($_POST['email']));
$phone      = htmlspecialchars(trim($_POST['tel']));
$product    = htmlspecialchars(trim($_POST['product']));
$quantity   = intval($_POST['cart_quantity']);
$notes      = htmlspecialchars(trim($_POST['notes'] ?? ''));

if (empty($product)) {
    header('Location: order.html?error=empty_cart');
    exit;
}

$sql = "INSERT INTO orders (first_name, last_name, email, phone, product, quantity, notes) 
        VALUES (:first_name, :last_name, :email, :phone, :product, :quantity, :notes)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':first_name' => $first_name,
    ':last_name'  => $last_name,
    ':email'      => $email,
    ':phone'      => $phone,
    ':product'    => $product,
    ':quantity'   => $quantity,
    ':notes'      => $notes
]);

header('Location: success.html');
exit;
?>