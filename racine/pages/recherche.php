<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/menuFiltres.css" rel="stylesheet">
    <link href="../ressources/Style/recherche.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

<?php require_once '../ressources/Templates/header.php'; ?>

<?php require_once '../ressources/Templates/menuFiltres.php'; ?>

<div class="container">
    <?php for ($i=0; $i < 5; $i++) { ?>
        <a href="video.php" class="video">
            <div class="miniature"></div>
            <div class="description">
                <h2 class="titre">Video <?php echo $i; ?></h2>
                <p class="description"> Métadonnées : Lorem Ipsum</p>
            </div>
        </a>
    <?php } ?>
</div>

<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        affichageFiltres();
    });
</script>