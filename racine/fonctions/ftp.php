<?php

/**
 * Fonction qui établit une connexion FTP.
 * Prend en paramètre : nom du serveur / login / password (ex : NAS_H264, user2, pass2)
 * Retourne $conn_id. Il sera nécessaire de fermer la connexion avec "ftp_close($conn_id)"
 */
function connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass){
    $conn_id = ftp_connect($ftp_server);
    if (!$conn_id) {
        die("Impossible de se connecter au serveur FTP : $ftp_server<br>");
    }
    if (!ftp_login($conn_id, $ftp_user, $ftp_pass)) {
        die("Échec de la connexion pour l'utilisateur $ftp_user<br>");
    }
    return $conn_id;
}

/**
 * Fonction qui télécharge un fichier dans un répertoire local
 * Prend en paramètre l'id de connexion, le fichier à obtenir en local et le fichier sutué dans le NAS
 */
function telechargerFichier($conn_id, $local_file, $ftp_file){
    if (!(ftp_get($conn_id, $local_file, $ftp_file, FTP_BINARY))) {
        echo "Échec du téléchargement du fichier.<br>";
    }
}

/**
 * Fonction qui exporte un fichier vidéo local vers le NAS MPEG.
 * Prend en paramètre : chemin du fichier local, chemin distant sur le NAS MPEG.
 */
function exporterVideoVersNAS($fichierLocal, $cheminDistantNAS, $ftp_server, $ftp_user, $ftp_pass) {
    $conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);

    // Extraire le chemin du dossier distant
    $cheminDossierDistant = dirname($cheminDistantNAS);
    // Vérifier et créer le dossier distant si nécessaire
    if (!@ftp_chdir($conn_id, $cheminDossierDistant)) {
        creerDossierFTP($conn_id, $cheminDossierDistant);
    }
    ftp_chdir($conn_id, URI_RACINE_NAS_MPEG);

    if (!(ftp_put($conn_id, $cheminDistantNAS, $fichierLocal, FTP_BINARY))) {
        echo "Échec de l'export du fichier '$fichierLocal' vers '$cheminDistantNAS'<br>";
    }
    ftp_close($conn_id);
}


/**
 * Fonction qui permet de créer un dossier via FTP
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
                echo "Erreur lors de la création du dossier : $cheminCourant<br>";
                return false;
            }
        }
    }
}


/**
 * Fonction qui retourne un tableau de fichiers avec les chemins complets.
 * Prend en paramètre l'id de connexion et le repertoire à partir duquel analyser (normalement la racine).
 * exemple : 2024-2025/video.mp4
 * Si une vidéo est située à la racine, elle se nomme video.mp4
 */
function listerFichiersCompletFTP($conn_id, $repertoire) {
    $pile = [$repertoire];
    $fichiersComplet = [];
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


?>