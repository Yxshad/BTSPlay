<?php
require_once "../ressources/constantes.php";
require_once "ftp.php";
require_once "ffmpeg.php";
require_once "modele.php";
require_once "fonctions.php";

//CONTROLE AJAX
if (isset($_POST["action"])) {

    if ($_POST["action"] == "scanDossierDecoupeVideo") {
        header('Content-Type: application/json');
        scanDossierDecoupeVideo(); 
        exit();
    }
    if ($_POST["action"] == "lancerConversion") {
        fonctionTransfert();
    }
}

/**
 * Fonction qui permet de récupérer des URIS, titres et id de X vidéos situées dans le NAS MPEG
 * Prend en paramètre le nombre d'URIS et titres à récupérer
 * Retourne un tableau d'URIS/titres/id et cheminMiniature
 */
function controleurRecupererTitreIdVideo() {
    $tabURIS = getUriNASetTitreMPEGEtId(10);
    $videos = [];
    if (!$tabURIS) {
        return $videos;
    }
    foreach ($tabURIS as $video) {
        $id = $video['id'];
        $uriNAS = URI_RACINE_NAS_MPEG . $video['URI_NAS_MPEG'];
        $titre = $video['mtd_tech_titre'];
        $cheminLocalComplet = chargerMiniature($uriNAS, $titre, NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
        $titreSansExtension = pathinfo($titre, PATHINFO_FILENAME);

        $videos[] = [
            'id' => $id,
            'uriNAS' => $uriNAS,
            'titre' => $titreSansExtension,
            'cheminMiniature' => $cheminLocalComplet
        ];
    }
    return $videos;
}

function controleurRecupererInfosVideo() {
    // Vérifie si le paramètre 'v' est présent dans l'URL
    if (!isset($_GET['v']) || empty($_GET['v']) || !is_numeric($_GET['v'])) {
        header('Location: erreur.php?code=404');
        exit();
    }
    $idVideo = intval($_GET['v']);

    // Récupère les informations de la vidéo
    $video = fetchAll("SELECT * FROM Media WHERE id=$idVideo;");
    if ($video == null) {
        header('Location: erreur.php?code=404');
        exit();
    }
    $video = $video[0];

    // Prépare les chemins nécessaires
    $nomFichier = $video["mtd_tech_titre"];

    $miniature = trouverNomMiniature($nomFichier);
    $cheminMiniature = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $miniature;

    // Télécharge la vidéo depuis le serveur FTP
    $cheminLocal = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $video["mtd_tech_titre"];
    $cheminDistant = URI_RACINE_NAS_MPEG . $video["URI_NAS_MPEG"] . $video["mtd_tech_titre"];
    $conn_id = connexionFTP_NAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
    telechargerFichier($conn_id, $cheminLocal, $cheminDistant);
    ftp_close($conn_id);

    // Prépare les métadonnées et le titre
    $titreVideo = recupererTitreVideo($video["mtd_tech_titre"]);
    $mtdEdito = getMetadonneesEditorialesVideo($video);

    // Retourne toutes les informations sous forme de tableau
    return [
        "idVideo" => $idVideo,
        "mtdTech" => $video,
        "nomFichier" => $nomFichier,
        "cheminMiniature" => $cheminMiniature,
        "cheminLocal" => $cheminLocal,
        "cheminDistant" => $cheminDistant,
        "titreVideo" => $titreVideo,
        "mtdEdito" => $mtdEdito,
    ];
}
?>