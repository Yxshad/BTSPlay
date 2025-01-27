<?php

//importer modele 
require_once "../../fonctions/modele.php" 

// Connexion à la base de données


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération du terme recherché
if (isset($_GET['term'])) {
    $term = htmlspecialchars($_GET['term']);
    $stmt = $pdo->prepare("SELECT nom, prenom FROM utilisateurs WHERE nom LIKE :term OR prenom LIKE :term LIMIT 10");
    $stmt->execute([':term' => "%$term%"]);

    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultats);
}
?>
