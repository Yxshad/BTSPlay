<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <script src="../ressources/Script/scripts.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php include '../ressources/Templates/header.php'; //Appel du header ?>

<div class="container">
    <div class="lecteurVideo">
        <video src=""></video>
    </div>
    <h1 class="titre">Titre de la video</h1>
    <div class="colonnes">
        <div class="colonne-1">
            <p class="description">Lorem ipsum</p>
            <p class="meta">15 fps, 1920x1080, 16:9</p>
            <?php $i = 0;
            while($i < 3){ //tant qu'on trouve des metadonnées editoriales ?>
                <p>Acteur : José</p>
                <?php $i++;
            } ?>
        </div>
        <div class="colonne-2">
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/download.webp" alt="">
                </div>
                <p>Télécharger</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/antenne.png" alt="">
                </div>
                <p>Diffuser</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/modif.png" alt="">
                </div>
                <p>Modifier</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/trash.png" alt="">
                </div>
                <p>Supprimer</p>
            </a>
        </div>
    </div>
   
</div>