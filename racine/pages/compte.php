<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/compte.css" rel="stylesheet">
    <script src="../ressources/Script/scripts.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php include '../ressources/Templates/header.php';?>

<div class="container">
    <div>
        <div class="profile_picture">
            <img src="../ressources/Images/account.png" alt="profile-picture">
        </div>
        <div class="username">
            <p>Nom d'utilisateur : </p>
            <input type="text">
        </div>
        <div class="password">
            <p>Mot de passe : </p>
            <input type="password">
        </div>
        <div class="confirmer">
            <button>Valider</button>
        </div>
    </div>
</div>