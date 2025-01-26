<?php 
session_start();
require_once '../fonctions/controleur.php';
$tabVideos = controleurRecupererTitreIdVideo();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/logo_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/menuFiltres.css" rel="stylesheet">
    <link href="../ressources/Style/home.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link href="../ressources/lib/Swiper/swiper-bundle.min.css" rel="stylesheet">
    <script src="../ressources/lib/Swiper/swiper-bundle.min.js"></script>

<?php require_once '../ressources/Templates/header.php'; ?>

<?php require_once '../ressources/Templates/menuFiltres.php'; ?>

<div class="container">
    <div class="sliderVideo">
        <h2>Vos vidéos</h2>
        <div class="swiperVideo">
            <div class="swiper-wrapper">
                <?php
                    foreach ($tabVideos as $video) {
                        $id = $video['id'];
                        $uriNAS = $video['uriNAS'];
                        $titre = $video['titre'];
                        $cheminLocalComplet = $video['cheminMiniature'];
                        echo "<div class='swiper-slide'>";
                            echo "<a href='video.php?v=$id'>";
                                echo "<div class='miniature'>";
                                    echo "<img src='$cheminLocalComplet' alt='Miniature de la vidéo' class='imageMiniature'/>";
                                echo "</div>";
                                echo "<h3>$titre</h3>";
                            echo "</a>";
                        echo "</div>";
                    }
                ?>
            </div>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>

<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        affichageFiltres();
        initCarrousel();
    });
</script>
