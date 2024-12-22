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

?>