<?php

/**
 * Fonction qui affiche à l'écran une collection passée en paramètre sous forme de tableau
 * $titre : Le titre de la collection à afficher
 * $COLLECT_NAS : Un tableau de métadonnées des vidéos présentes dans le NAS
 */
function afficherCollect($titre, $COLLECT_NAS) {
    echo "<h2>$titre</h2>";
    if (empty($COLLECT_NAS)) {
        echo "<p>Tableau videos</p>";
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
 * Fonction qui retourne la liste des métadonnées techniques d'une vidéo passée en paramètre
 * $fichier : le titre de la vidéo dont on veut récupérer les métadonnées
 * $URI_ESPACE_LOCAL : le chemin d'accès à la vidéo par exemple : " videos/videosAConvertir/attenteDeConvertion "
 */
function recupererMetadonnees($fichier, $URI_ESPACE_LOCAL){
	$fichier_source = $URI_ESPACE_LOCAL . '/' . $fichier;
    $command = "ffmpeg -i $fichier_source 2>&1";
    exec($command, $output);
    $meta = implode($output);
    // #RISQUE : Changment des REGEX selon les vidéos
    preg_match("/'[^']/(.)'/",$meta,$nom);
    preg_match("/[0-9](?= fps)/",$meta,$fps);
    preg_match("/(?<=yuv420p(progressive), )[0-9]x[0-9]/",$meta,$resolution);
    preg_match("/(?<=Duration: )[0-9]:[0-9]:[0-9].[0-9]/",$meta,$duree);
    preg_match("/(?<=DAR)[0-9]:[0-9]*/",$meta,$format);
    $liste = ["nom" => $nom[1],
                "fps" => $fps[0],
                "resolution" => $resolution[0],
                "duree" => $duree[0],
                "format" => $format[0]];

    return $liste;
}

/**
 * Fonction qui vérifie la correspondance de toutes les métadonnées techniques entre 2 vidéos passées en paramètre
 * Une vidéo est un tableau qui contient les métadonnées techniques d'une vidéo (titre, durée, ...)
 */
function verifierCorrespondanceMdtTechVideos($Video_1, $video_2){
    if (pathinfo($Video_1['TITRE'], PATHINFO_FILENAME) == pathinfo($video_2['TITRE'], PATHINFO_FILENAME)
        && $Video_1['FORMAT'] == $video_2['FORMAT']
        //&& $Video_1['FPS'] == $video_2['FPS']
        && $Video_1['RESOLUTION'] == $video_2['RESOLUTION']
        && $Video_1['DUREE'] == $video_2['DUREE'] ){
        return true;
    }
    else {
        return false;
    }
}

?>