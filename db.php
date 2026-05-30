<?php

// Informations de connexion à la base de données
$host     = "localhost";
$dbname   = "kyutopia";
$username = "root";      // utilisateur par défaut sur XAMPP
$password = "";          // mot de passe vide par défaut sur XAMPP

// On tente la connexion avec PDO
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );

    // Si une erreur SQL arrive → lancer une exception (plus facile à débugger)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si la connexion échoue → afficher l'erreur et stopper le script
    die("Erreur de connexion : " . $e->getMessage());
}
?>