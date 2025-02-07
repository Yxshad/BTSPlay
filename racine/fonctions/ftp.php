<?php

/**
 * \file ftp.php
 * \version 1.1
 * \brief Fichier regroupant toutes les fonctions lié au processus ftp
 * \author Axel Marrier/Julien Loridant
 */

/**
 * \fn connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass)
 * \brief Fonction qui établit une connexion FTP
 * \param ftp_server - nom du serveur 
 * \param ftp_user - login de l'utilisateur 
 * \param ftp_pass - password de l'utilisateur
 * \return conn_id - id de la connexion
 */
function connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass){
    $conn_id = ftp_connect($ftp_server);
    if (!$conn_id) {
        ajouterLog(LOG_FAIL, "Impossible de se connecter au serveur FTP : $ftp_server.");
        exit();
    }
    elseif (!ftp_login($conn_id, $ftp_user, $ftp_pass)) {
        ajouterLog(LOG_FAIL, "Échec de la connexion au serveur FTP $ftp_server pour l'utilisateur $ftp_user.");
        exit();
    }
    #PROD : À DECOMMENTER LORS DU PASSAGE EN PROD
    //ftp_pasv($conn_id, true);
    return $conn_id;
}

/**
 * \fn telechargerFichier($conn_id, $local_file, $ftp_file)
 * \brief Fonction qui télécharge un fichier dans un répertoire local
 * \param conn_id - id de la connexion
 * \param local_file - le fichier que l'on cherche en local
 * \param ftp_file - le fichier situé sur le NAS où on se connecte
 */
function telechargerFichier($conn_id, $local_file, $ftp_file){

    if (file_exists($local_file)) {
        ajouterLog(LOG_INFORM, "Le fichier $local_file existe déjà. Téléchargement ignoré.");
        return;
    }

    if ((ftp_get($conn_id, $local_file, $ftp_file, FTP_BINARY))) {
        ajouterLog(LOG_SUCCESS, "Fichier $ftp_file téléchargé avec succès dans $local_file.");
    }
    else{
        ajouterLog(LOG_FAIL, "Échec du téléchargement du fichier $ftp_file.");
    }
}


/**
 * \fn exporterFichierVersNAS($cheminLocal, $cheminDistantNAS, $nomFichier, $ftp_server, $ftp_user, $ftp_pass)
 * \brief Fonction qui exporte un fichier local vers un serveur NAS.
 * \param cheminLocalComplet - Chemin du fichier local
 * \param cheminDistantNAS - Chemin du fichier sur le NAS distant
 * \param nomFichier - nom du fichier
 * \param ftp_server - le server ftp sur lequel on se connecte
 * \param ftp_user - Identifiant de l'utilisateur qui se connecte en FTP
 * \param ftp_pass - Mot de passe de l'utilisateur qui se connecte en FTP
 * \return resultat de la fonction exporterFichierVersNASAvecCheminComplet
 */
function exporterFichierVersNAS($cheminLocal, $cheminDistantNAS, $nomFichier, $ftp_server, $ftp_user, $ftp_pass) {
    // Construire le chemin complet de destination pour le fichier
    $cheminDistantNASComplet = $cheminDistantNAS . $nomFichier;
    $cheminLocalComplet = $cheminLocal . $nomFichier;
    // Envoyer le fichier
    return exporterFichierVersNASAvecCheminComplet($cheminLocalComplet, $cheminDistantNASComplet, $ftp_server, $ftp_user, $ftp_pass);
}

/**
 * \fn exporterFichierVersNASAvecCheminComplet($cheminLocalComplet, $cheminDistantNASComplet, $ftp_server, $ftp_user, $ftp_pass)
 * \brief Fonction qui exporte un fichier  local vers un NAS distant.
 * \param cheminLocalComplet - Chemin du fichier local
 * \param cheminDistantNASComplet - Chemin du fichier sur le NAS distant
 * \param ftp_server - le server ftp sur lequel on se connecte
 * \param ftp_user - Identifiant de l'utilisateur qui se connecte en FTP
 * \param ftp_pass - Mot de passe de l'utilisateur qui se connecte en FTP
 */
function exporterFichierVersNASAvecCheminComplet($cheminLocalComplet, $cheminDistantNASComplet, $ftp_server, $ftp_user, $ftp_pass) {
    $conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);

    // Envoyer le fichier
    $exportSucces = true;
    // Vérifier si le fichier existe déjà sur le NAS
    if (ftp_size($conn_id, $cheminDistantNASComplet) !== -1) {
        ajouterLog(LOG_INFORM, "Le fichier $cheminDistantNASComplet existe déjà dans $cheminDistantNASComplet. Export annulé.");
        $exportSucces = false;
    }
    elseif (!(ftp_put($conn_id, $cheminDistantNASComplet, $cheminLocalComplet, FTP_BINARY))){
        ajouterLog(LOG_FAIL, "Échec de l'export du fichier $cheminLocalComplet vers $cheminDistantNASComplet");
        $exportSucces = false;
    }
    else{
        ajouterLog(LOG_SUCCESS, "Fichier $cheminLocalComplet exporté avec succès dans $cheminDistantNASComplet.");
    }
    ftp_close($conn_id);
    return $exportSucces;
}


/**
 * \fn creerDossierFTP($conn_id, $cheminDossier)
 * \brief Fonction qui permet de créer un dossier via FTP
 * \param conn_id - l'id de la connexion ftp
 * \param cheminDossier - chemin vers où on veut créer le dossier
 */
function creerDossierFTP($conn_id, $cheminDossier) {
    $cheminDossier = rtrim($cheminDossier, '/');
    $dossiers = explode('/', $cheminDossier);
    $cheminCourant = '';

    foreach ($dossiers as $dossier) {
        $cheminCourant .= $dossier . '/';
        // Vérifie si le dossier existe, sinon le crée
        if (!@ftp_chdir($conn_id, $cheminCourant)) {
            if (!(ftp_mkdir($conn_id, $cheminCourant))) {
                ajouterLog(LOG_FAIL, "Échec lors de la création du dossier $cheminCourant.");
                exit();
            }
        }
    }
}


/**
 * \fn listerFichiersCompletFTP($conn_id, $repertoire) 
 * \brief Fonction qui retourne un tableau de fichiers avec les chemins complets.
 * \param conn_id - l'id de connexion et le repertoire à partir duquel analyser (normalement la racine).
 * \param repertoire - repertoire où on liste les fichiers
 * \return fichiersComplet - liste complète des fichiers
 */
function listerFichiersCompletFTP($conn_id, $repertoire) {
    $pile = [$repertoire];
    $fichiersComplet = [];
    // #PROD, on ne récupère que les 2 premiers fichiers pour ne pas surcharger - À DECOMMENTER LORS DES TESTS EN PROD
    //while (!empty($pile) && count($fichiersComplet) < 2){
    while (!empty($pile)) {
        $dossierCourant = array_pop($pile); 
        $elements = ftp_nlist($conn_id, $dossierCourant);
        foreach ($elements as $element) {

            // Vérifier si le répertoire courant est la racine
            if ($dossierCourant === '/') {
                // Si on est à la racine, on enlève le slash initial du fichier
                $elementComplet = ltrim($element, '/');
            }
            else {
                // Si ce n'est pas la racine, on concatène le dossier courant avec le fichier
                $elementComplet = rtrim($dossierCourant, '/') . '/' . ltrim($element, '/');
            }
            $nomFichier = basename($elementComplet);

            if ($nomFichier === '.' || $nomFichier === '..') {
                continue;
            }

            if (ftp_size($conn_id, $elementComplet) == -1) {
                $pile[] = $elementComplet;
            }
            else {
                $fichiersComplet[] = $elementComplet;
            }
        }
    }
        return $fichiersComplet;
}


/**
 * \fn recupererNomsVideosNAS($ftp_server, $ftp_user, $ftp_pass, $URI_NAS, $nomsVideos_NAS)
 * \brief Fonction qui récupère les noms des vidéos situées dans un NAS ($ftp_server). Créé une connexion FTP
 * \param ftp_server - Le serveur ftp sur lequel on se connecte
 * \param ftp_user - L'utilisateur utilisé pour se connecter
 * \param ftp_pass - Le mot de passe pour se connecter
 * \param URI_NAS - L'url du NAS où récupérer la vidéo
 * \param nomVideos_NAS - Nom de la vidéo sur le NAS
 */
function recupererNomsVideosNAS($ftp_server, $ftp_user, $ftp_pass, $URI_NAS, $nomsVideos_NAS){
	
	$conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);

	// Lister les fichiers sur le serveur FTP
	$fichiers_NAS = listerFichiersCompletFTP($conn_id, $URI_NAS);

	foreach ($fichiers_NAS as $fichier) {
        $nom_fichier = basename($fichier); // Récupérer uniquement le nom du fichier
		if ($nom_fichier !== '.' && $nom_fichier !== '..') {

			$nomsVideos_NAS[] = $fichier;
		}
    }
	ftp_close($conn_id);
	return $nomsVideos_NAS;
}


?>