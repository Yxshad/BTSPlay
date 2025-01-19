<?php
require_once "../ressources/constantes.php";
require_once "ftp.php";
require_once "ffmpeg.php";
require_once "modele.php";
require_once "fonctions.php";


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

function controleurRecupererInfosVideo() {
    // Vérifie si le paramètre 'v' est présent dans l'URL
    if (!isset($_GET['v']) || empty($_GET['v']) || !is_numeric($_GET['v'])) {
        header('Location: erreur.php?code=404');
        exit();
    }

    // Récupère l'ID vidéo
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
    $miniature = $nomFichier . "_miniature.png";
    $cheminMiniature = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $miniature;

    $cheminLocal = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $video["mtd_tech_titre"];
    $cheminDistant = URI_RACINE_NAS_MPEG . $video["URI_NAS_MPEG"] . $video["mtd_tech_titre"];

    // Télécharge la vidéo depuis le serveur FTP
    $conn_id = connexionFTP_NAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
    telechargerFichier($conn_id, $cheminLocal, $cheminDistant);
    ftp_close($conn_id);

    // Prépare les métadonnées et le titre
    $titreVideo = recupererTitreVideo($video["mtd_tech_titre"]);
    $meta = getMetadonneesEditorialesVideo($video);

    // Retourne toutes les informations sous forme de tableau
    return [
        "idVideo" => $idVideo,
        "video" => $video,
        "nomFichier" => $nomFichier,
        "cheminMiniature" => $cheminMiniature,
        "cheminLocal" => $cheminLocal,
        "cheminDistant" => $cheminDistant,
        "titreVideo" => $titreVideo,
        "meta" => $meta,
    ];
}
?>