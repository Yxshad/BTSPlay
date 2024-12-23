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
    echo "Connexion réussie à $ftp_server<br>";
    return $conn_id;
}

/**
 * Fonction qui télécharge un fichier dans un répertoire local
 * Prend en paramètre l'id de connexion, le fichier à obtenir en local et le fichier sutué dans le NAS
 */
function telechargerFichier($conn_id, $local_file, $ftp_file){
    if (ftp_get($conn_id, $local_file, $ftp_file, FTP_BINARY)) {
        echo "Le fichier a été téléchargé avec succès.<br>";
    }
    else {
        echo "Échec du téléchargement du fichier.<br>";
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