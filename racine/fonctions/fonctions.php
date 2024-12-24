<?php

/**
 * Fonction qui affiche à l'écran une collection passée en paramètre sous forme de tableau
 * $titre : Le titre de la collection à afficher
 * $COLLECT_NAS : Un tableau de métadonnées des vidéos présentes dans le NAS
 */
function afficherCollect($titre, $COLLECT_NAS) {
    echo "<h2>$titre</h2>";
    if (empty($COLLECT_NAS)) {
        echo "<p>Tableau vide</p>";
        return;
    }
    $first_item = reset($COLLECT_NAS); //Récupère le 1er élément, merci le chat j'avais une erreur
    // Vérification si le tableau est vide ou ne contient pas d'éléments valides
    if (!$first_item) {
        echo "<p>Aucun élément valide dans le tableau</p>";
        return;
    }
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>";
    // En-têtes des colonnes
    foreach ($first_item as $key => $value) {
        echo "<th>$key</th>";
    }
    echo "</tr>";
    //Lignes pour chaque élément
    foreach ($COLLECT_NAS as $item) {
        echo "<tr>";
        foreach ($item as $key => $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table><br><br>";
}

/**
 * Fonction qui vérifie la correspondance de toutes les métadonnées techniques entre 2 vidéos passées en paramètre
 * Une vidéo est un tableau qui contient les métadonnées techniques d'une vidéo (titre, durée, ...)
 * (pathinfo pour ne pas tenir compte de l'extension)
 */
function verifierCorrespondanceMdtTechVideos($video_1, $video_2){
    
    if (pathinfo($video_1[MTD_TITRE], PATHINFO_FILENAME) == pathinfo($video_2[MTD_TITRE], PATHINFO_FILENAME)
        && $video_1[MTD_FORMAT] == $video_2[MTD_FORMAT]
        && $video_1[MTD_FPS] == $video_2[MTD_FPS]
        && $video_1[MTD_RESOLUTION] == $video_2[MTD_RESOLUTION]
        && $video_1[MTD_DUREE] == $video_2[MTD_DUREE]
        && $video_1[MTD_URI] == $video_2[MTD_URI]) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Fonction qui vérifie la correspondance des noms des 2 vidéos passées en paramètre
 * On compare les noms des fichiers sans tenir compte de leur extension (video.mp4 = video.mxf)
 * (pathinfo pour ne pas tenir compte de l'extension)
 * On prend cependant compte du chemin du fichier
 */
function verifierCorrespondanceNomsVideos($nomVideo_1, $nomVideo_2) {

    $cheminFichier_1 = pathinfo($nomVideo_1, PATHINFO_DIRNAME);
    $cheminFichier_2 = pathinfo($nomVideo_2, PATHINFO_DIRNAME);

    $nomFichier_1 = pathinfo($nomVideo_1, PATHINFO_FILENAME);
    $nomFichier_2 = pathinfo($nomVideo_2, PATHINFO_FILENAME);

    if ($cheminFichier_1 == $cheminFichier_2 && $nomFichier_1 == $nomFichier_2) {
        return true;
    } else {
        return false;
    }
}

?>