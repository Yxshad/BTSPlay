<?php
require_once '../fonctions/controleur.php';

$annee = isset($_POST['annee']) ? intval($_POST['annee']) : null;
$niveau = isset($_POST['niveau']) ? intval($_POST['niveau']) : null;
$prof = isset($_POST['prof']) ? $_POST['prof'] : null;

$tabVideos = controleurRecupererTitreIdVideoFiltre($annee, $niveau, $prof); // Nouvelle fonction filtrée

if (!empty($tabVideos)) {
    echo '<div class="sliderVideo">';
    echo '<h2>Vos vidéos</h2>';
    echo '<div class="swiperVideo">';
    echo '<div class="swiper-wrapper">';
    foreach ($tabVideos as $video) {
        $id = $video['id'];
        $titre = $video['mtd_tech_titre'];
        $cheminMiniature = URI_RACINE_STOCKAGE_LOCAL . $video['URI_STOCKAGE_LOCAL'] . trouverNomMiniature($titre);
        echo "<div class='swiper-slide'>";
        echo "<a href='video.php?v=$id'>";
        echo "<div class='miniature'>";
        echo "<img src='$cheminMiniature' alt='Miniature' class='imageMiniature'/>";
        echo "</div>";
        echo "<h3>$titre</h3>";
        echo "</a>";
        echo "</div>";
    }
    echo '</div>';
    echo '</div>';
    echo '<div class="swiper-button-next"></div>';
    echo '<div class="swiper-button-prev"></div>';
    echo '</div>';
} else {
    echo "<p>Aucune vidéo trouvée.</p>";
}
?> 
