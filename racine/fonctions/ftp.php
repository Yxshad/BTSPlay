<?php


/**
 * Fonction qui établit une connexion FTP.
 * Prend en paramètre : nom du serveur / login / password (ex : NAS_H264, user2, pass2)
 * Retourne $conn_id. Il sera nécessaire de fermer la connexion avec "ftp_close($conn_id)"
 */
function connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass){

    $conn_id = ftp_connect($ftp_server);

    if (!$conn_id) {
        die("Impossible de se connecter au serveur FTP : $ftp_server\n");
    }

    if (!ftp_login($conn_id, $ftp_user, $ftp_pass)) {
        die("Échec de la connexion pour l'utilisateur $ftp_user\n");
    }

    echo "Connexion réussie à $ftp_server\n";
    
    return $conn_id;
}

?>