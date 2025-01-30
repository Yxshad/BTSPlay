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

<div class="container">
    <div>
        <form method="POST" action="#">
        <input type="hidden" name="action" value="connexionUtilisateur">
            <div class="profile_picture">
                <img src="../ressources/Images/account.png" alt="profile-picture">
            </div>
            <div class="username">
                <p>Nom d'utilisateur : </p>
                <input type="text" name="loginUser">
            </div>
            <div class="password">
                <p>Mot de passe : </p>
                <input type="password" name="passwordUser">
            </div>
            <div class="confirmer">
                <button type="submit" class="btn">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../ressources/Templates/footer.php';?>