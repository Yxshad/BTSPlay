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
 * Fonction qui retourne la liste des métadonnées techniques d'une vidéo passée en paramètre
 * Vidéo située sur un espace local
 * $fichier : le titre de la vidéo dont on veut récupérer les métadonnées
 * $URI_ESPACE_LOCAL : le chemin d'accès à la vidéo par exemple : " videos/videosAConvertir/attenteDeConvertion "
 */
function recupererMetadonneesVideoLocale($fichier, $URI_ESPACE_LOCAL){
	$fichier_source = $URI_ESPACE_LOCAL . '/' . $fichier;
    $command = "ffmpeg -i $fichier_source 2>&1";
    exec($command, $output);
    $meta = implode($output);
    return recupererMetadonnees($meta, $fichier);
}

/**
 * Fonction qui retourne la liste des métadonnées techniques d'une vidéo passée en paramètre
 * Vidéo située sur un NAS distant, connexion via FTP
 * $fichier : le titre de la vidéo dont on veut récupérer les métadonnées
 * $URI_ESPACE_LOCAL : le chemin d'accès à la vidéo par exemple : " videos/videosAConvertir/attenteDeConvertion "
 */
function recupererMetadonneesVideoViaFTP($ftp_server, $ftp_user, $ftp_pass, $cheminFichier, $nomFichier) {
    $fileUrl = "ftp://$ftp_user:$ftp_pass@$ftp_server/$cheminFichier/$nomFichier";
    $command = "ffmpeg -i \"$fileUrl\" 2>&1";
    exec($command, $output);
    $meta = implode($output);
    return recupererMetadonnees($meta, $nomFichier);
}

/**
 * Fonction de récupération des métadonnées d'un $meta (bloc de métadonnées) via REGEX
 * #RISQUE : Changment des REGEX selon les vidéos
 */
function recupererMetadonnees($meta, $fichier){
    preg_match("/'[^']*\/(.*)'/",$meta,$nom);
    preg_match("/(\d+(.\d+)?)(?= fps)/", $meta, $fps);
    preg_match("/(\d{2,4}x\d{2,4})/", $meta, $resolution);
    preg_match("/(?<=Duration: )(\d{2}:\d{2}:\d{2}.\d{2})/", $meta, $duree);
    preg_match("/(?<=DAR )([0-9]+:[0-9]+)/", $meta, $format);
    // #RISQUE : Attention aux duree des vidéos qui varient selon l'extension-  J'ai arrondi mais solution partiellement viable
    $dureeFormatee = preg_replace('/\.\d+/', '', $duree[1]); //Arrondir pour ne pas tenir compte des centièmes
    $liste = [MTD_TITRE => $fichier,
                MTD_FPS => $fps[0],
                MTD_RESOLUTION => $resolution[0],
                MTD_DUREE => $dureeFormatee,
                MTD_FORMAT => $format[1]
                ];
    return $liste;
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
 */
function verifierCorrespondanceNomsVideos($nomVideo_1, $nomVideo_2){
    if (pathinfo($nomVideo_1, PATHINFO_FILENAME) == pathinfo($nomVideo_2, PATHINFO_FILENAME)){
        return true;
    }
    else {
        return false;
    }
}

?>