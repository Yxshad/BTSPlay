<?php

/**
 * \file controleur.php
 * \version 1.1
 * \brief Controleur servant d'intermédiaire entre les pages et les fonctions/méthodes exécutées
 * en fond
 * \author Nicolas Conguisti
 */

require_once "../ressources/constantes.php";
require_once "ftp.php";
require_once "ffmpeg.php";
require_once "modele.php";
require_once "fonctions.php";

/**
 * \fn checkHeader()
 * \brief Regarde le Header de la page pour exécuter les fonctions correspondantes
 */
function checkHeader(){
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
        if ($_POST["action"] == "connexionUtilisateur") {
            $loginUser = $_POST['loginUser'];
            $passwordUser = $_POST['passwordUser'];
            controleurIdentifierUtilisateur($loginUser, $passwordUser);
        }
        if ($_POST["action"] == "diffuserVideo") {
            $cheminVideoComplet = $_POST['cheminVideoComplet'];
            controleurDiffuserVideo($cheminVideoComplet);
            // #RISQUE : DIFFUSION stoppée, en attente du dev nico
        }
    }
}
checkHeader();

/**
 * \fn controleurRecupererTitreIdVideo()
 * \brief Fonction qui permet de récupérer des URIS, titres et id de X vidéos situées dans le stockage local
 * \return un tableau d'URIS/titres/id et cheminMiniature
 */
function controleurRecupererTitreIdVideo() {
    $tabURIS = getTitreURIEtId(NB_VIDEOS_PAR_SWIPER);
    $videos = [];
    if (!$tabURIS) {
        return $videos;
    }
    ajouterLog(LOG_INFORM, "Récupération des informations à afficher sur la page d'accueil.");
    foreach ($tabURIS as $video) {
        $id = $video['id'];
        $URIEspaceLocal = '/stockage/' .$video['URI_STOCKAGE_LOCAL'];
        $titreSansExtension = recupererNomFichierSansExtension($video['mtd_tech_titre']);

        $nomFichierMiniature = trouverNomMiniature($video['mtd_tech_titre']);
        $cheminMiniatureComplet = $URIEspaceLocal . $nomFichierMiniature;
        
        $videos[] = [
            'id' => $id,
            'URIEspaceLocal' => $URIEspaceLocal,
            'titre' => $titreSansExtension,
            'cheminMiniatureComplet' => $cheminMiniatureComplet
        ];
    }
    return $videos;
}


/**
 * \fn controleurRecupererInfosVideo()
 * \brief Fonction qui permet de récupérer les métadonnées techniques liées à une vidéo
 * \return tableau de métadonnées techniques
 */
function controleurRecupererInfosVideo() {
    $idVideo = controleurVerifierVideoParametre();
    $video = getInfosVideo($idVideo);
    if ($video == null) {
        header('Location: erreur.php?code=404');
        exit();
    }
    ajouterLog(LOG_INFORM, "Chargement des informations de la vidéo n° $idVideo");
    $nomFichier = $video["mtd_tech_titre"];
    $titreVideo = recupererTitreVideo($video["mtd_tech_titre"]);
    $mtdEdito = getMetadonneesEditorialesVideo($video);
    $promotion = $video["promotion"];

    $URIEspaceLocal = '/stockage/' .$video['URI_STOCKAGE_LOCAL'];
    $nomFichierMiniature = trouverNomMiniature($video['mtd_tech_titre']);
    $cheminMiniatureComplet = $URIEspaceLocal . $nomFichierMiniature;

    $cheminVideoComplet = $URIEspaceLocal . $nomFichier;
    return [
        "idVideo" => $idVideo,
        "mtdTech" => $video,
        "nomFichier" => $nomFichier,
        "cheminMiniatureComplet" => $cheminMiniatureComplet,
        "cheminVideoComplet" => $cheminVideoComplet,
        "titreVideo" => $titreVideo,
        "mtdEdito" => $mtdEdito,
        "promotion" => $promotion,
    ];
}

/**
 * \fn controleurPreparerMetadonnees($idVideo)
 * \brief Prépare les métadonnées à mettre dans la vidéo une fois qu'il y a modification
 * \param idVideo - L'Id de la vidéo
 */
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


/**
 * \fn controleurRecupererListeProfesseurs()
 * \brief Renvoie la liste des professeurs
 * \return resultat - la liste des professeurs
 */
function controleurRecupererListeProfesseurs() {
    $listeProfesseurs = getAllProfesseurs();
    $resultat = array_map(function($item) {
        return $item['nom'] . " " . $item['prenom'];
    }, $listeProfesseurs);
    return $resultat;
}

/**
 * \fn controleurVerifierVideoParametre(){
 * \brief Vérifie qu'on a la bonne vidéo passée en paramètre quand on clique dessus
 * \return idVideo - l'id de la vidéo
 */
function controleurVerifierVideoParametre(){
    // Vérifie si le paramètre 'v' est présent dans l'URL
    if (!isset($_GET['v']) || empty($_GET['v']) || !is_numeric($_GET['v'])) {
        header('Location: erreur.php?code=404');
        exit();
    }
    $idVideo = intval($_GET['v']);

    return $idVideo;
}


/**
 * \fn controleurIdentifierUtilisateur($loginUser, $passwordUser)
 * \brief Vérifie les autorisations d'accès de l'utilisateur et le renvoie sur la page correspondante en fonction
 * \param loginUser - identifiant de connexion de l'utilisateur
 * \param passwordUser - mot de passe de connexion de l'utilisateur
 */
function controleurIdentifierUtilisateur($loginUser, $passwordUser){

    $passwordHache = hash('sha256', $passwordUser);

    //regarder si login + mdp en base, récupérer le rôle si trouvé. Sinon, message d'erreur
    $role = connexionProfesseur($loginUser, $passwordHache);

    if($role == false){
        ajouterLog(LOG_FAIL, "Erreur d'authentification pour l'utilisateur $loginUser.");
    }
    else{
        ajouterLog(LOG_INFORM, "L'utilisateur $loginUser s'est connecté.");
        $_SESSION["loginUser"] = $loginUser;
        $_SESSION["role"] = $role["role"];

        header('Location: home.php');
        exit();
    }
}


/**
 * \fn controleurVerifierAcces($rolesAutorises)
 * \brief Vérifie les autorisations d'accès de l'utilisateur et le renvoie sur la page correspondante en fonction
 * \param rolesAutorises - Rôles autorisés pour l'utilisateur
 */
// Si l'utilisateur n'a pas les autorisations pour accèder à la page, il est alors renvoyé sur la page d'accueil
// $rolesAutorises est une liste des roles autorisé
function controleurVerifierAcces($rolesAutorises){
    if ((!isset($_SESSION["role"])) || (!in_array($_SESSION["role"], $rolesAutorises))) {
        header('Location: home.php');
        exit();
    }
}

/**
 * \fn controleurDiffuserVideo($cheminLocalComplet)
 * \brief Fonction qui permet de diffuser une vidéo dont l'id est passé en paramètre sur le NAS DIFF.
 * \param cheminLocalComplet - Le chemin d'accès à la vidéo
 */
function controleurDiffuserVideo($cheminLocalComplet){
    // Récupération de la vidéo en qualité optimale
    // - Récupération des URIS en base avec $ID
    // - Téléchargement du fichier dans videoADiffuser
    
    // #RISQUE : Changement des répertoires du NAS de diffusion

    // $nomFichier = basename($cheminLocalComplet);
    // $cheminDistantComplet = URI_RACINE_NAS_DIFF . $nomFichier;

    $cheminLocalComplet = URI_RACINE_STOCKAGE_LOCAL . '2023-2024/_BTSPLAY_bomba/bomba.mp4';
    $cheminDistantComplet = URI_RACINE_NAS_DIFF . 'bomba.mp4';

    $exportSucces = exporterFichierVersNASAvecCheminComplet($cheminLocalComplet, $cheminDistantComplet, NAS_DIFF, LOGIN_NAS_DIFF, PASSWORD_NAS_DIFF);
    if($exportSucces){
        // #RISQUE : Message de validation à l'utilisateur
        return;
    }
    else{
        //Message d'erreur
        return;
    }
}
?>