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
    <link href="../ressources/Style/compte.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

<?php require_once '../ressources/Templates/header.php';?>

<div class="login-container">
    <form method="POST" action="#">
        <img class="userIcon" src="../ressources/Images/user.png">
        <input type="hidden" name="action" value="connexionUtilisateur">
        <p>Nom d'utilisateur :</p>
        <input type="text" name="loginUser">
        <p>Mot de passe :</p>
        <input type="password" name="passwordUser">
        <button type="submit" class="btn">Confirmer</button>
    </form>
</div>

<?php require_once '../ressources/Templates/footer.php';?>