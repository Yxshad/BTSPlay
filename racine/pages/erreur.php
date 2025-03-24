<?php 
session_start();
require_once '../fonctions/controleur.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/transfert.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

<?php 
require_once '../ressources/Templates/header.php';
?>

<?php
$code = $_GET['code'];
switch ($code) {
    case 404:
        $message = "Erreur 404 : La ressource demandée est introuvable.";
        break;
    case 403:
        $message = "Erreur 403 : Accès refusé.";
        break;
    case 415:
        $message = "Erreur 415 : Le fichier n'a pas encore été transféré. Veuillez contacter votre administrateur.";
        break;
    case 500:
        $message = "Erreur 500 : Erreur interne du serveur. Veuillez contacter votre administrateur.";
        break;
    default:
        $message = "Une erreur inconnue est survenue. Veuillez contacter votre administrateur.";
        break;
}

echo ($message);
?>

<?php require_once '../ressources/Templates/footer.php';?>