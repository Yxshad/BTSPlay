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
    if ($_POST["action"] == "ModifierMetadonnees") {
        $idVideo = $_POST['idVideo'];
        controleurPreparerMetadonnees($idVideo);
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

function controleurTelechargerFichier($cheminDistantVideo, $nomFichier){
    // Télécharge la vidéo depuis le serveur FTP
    $cheminLocal = URI_VIDEOS_A_LIRE . $cheminDistantVideo . $nomFichier;
    $cheminDistant = URI_RACINE_NAS_MPEG . $cheminDistantVideo . $nomFichier;
    $conn_id = connexionFTP_NAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
    telechargerFichier($conn_id, $cheminLocal, $cheminDistant);
    ftp_close($conn_id);
    return $cheminLocal;
}

function controleurRecupererInfosVideo() {
    $idVideo = controleurVerifierVideoParametre();
    // Récupère les informations de la vidéo

    // #RISQUE : Ne plus passer une requete directement, appeler une fonction

    $video = fetchAll("SELECT * FROM Media WHERE id=$idVideo;");
    if ($video == null) {
        header('Location: erreur.php?code=404');
        exit();
    }
    $video = $video[0];
    // Prépare les chemins nécessaires
    $nomFichier = $video["mtd_tech_titre"];
    $miniature = trouverNomMiniature($nomFichier);
    $titreVideo = recupererTitreVideo($video["mtd_tech_titre"]);
    $mtdEdito = getMetadonneesEditorialesVideo($video);
    $promotion = $video["promotion"];
    $cheminMiniature = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $miniature;
    $cheminDistantVideo = $video["URI_NAS_MPEG"];
    return [
        "idVideo" => $idVideo,
        "mtdTech" => $video,
        "nomFichier" => $nomFichier,
        "cheminMiniature" => $cheminMiniature,
        "cheminDistantVideo" => $cheminDistantVideo,
        "titreVideo" => $titreVideo,
        "mtdEdito" => $mtdEdito,
        "promotion" => $promotion,
    ];
}

function controleurPreparerMetadonnees($idVideo){
    if (
        isset($_POST["profReferent"]) ||
        isset($_POST["realisateur"]) || 
        isset($_POST["promotion"]) || 
        isset($_POST["projet"]) || 
        isset($_POST["cadreur"]) || 
        isset($_POST["responsableSon"])
    ) {
        // Récupération des champs entrés dans le formulaire
        $profReferent = $_POST["profReferent"];
        $realisateur = $_POST["realisateur"];
        $promotion = $_POST["promotion"];
        $projet = $_POST["projet"];
        $cadreur = $_POST["cadreur"];
        $responsableSon = $_POST["responsableSon"];
        miseAJourMetadonneesVideo(
            $idVideo, 
            $profReferent, 
            $realisateur, 
            $promotion, 
            $projet, 
            $cadreur, 
            $responsableSon
        );
    }
}

function controleurRecupererListeProfesseurs(){
    $listeProfesseurs = fetchAll("SELECT nom, prenom FROM Professeur;");

	$resultat = array_map(function($item) {
		return $item['nom'] . " " . $item['prenom'];
	}, $listeProfesseurs);

	return $resultat;
}

function controleurVerifierVideoParametre(){
    // Vérifie si le paramètre 'v' est présent dans l'URL
    if (!isset($_GET['v']) || empty($_GET['v']) || !is_numeric($_GET['v'])) {
        header('Location: erreur.php?code=404');
        exit();
    }
    $idVideo = intval($_GET['v']);

    return $idVideo;
}

?>