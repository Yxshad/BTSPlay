<?php 

session_start(); 
 if(isset($_POST["username"])){
    $_SESSION["username"] = $_POST["username"];
}
require_once '../fonctions/controleur.php';
$tabVideos = recupererURIEtTitreVideosEtId();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/logo_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/home.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

    
<?php
    require '../ressources/Templates/header.php';
?>

<aside class="filtres">
    
    <form action="recherche.php">
        <div>
            <label>Année</label>
            <input type="number">
        </div>

        <div>
            <label>Niveau</label>
            <input type="number">
        </div>
        
        <input value="Rechercher" type="submit">
    </form>

    <button class="afficherFiltres"> > </button>
</aside>

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

<div class="voile"></div>

<footer>
<?php require_once '../ressources/Templates/footer.php';?>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        affichageFiltres();
        initCarrousel();
    });
</script>
