<?php
$ftp_server = "127.0.0.1"; // Adresse de ton NAS ou du serveur FTP
$ftp_port = 21000; // Port FTP du NAS
$ftp_user = "user1"; // Nom d'utilisateur FTP
$ftp_pass = "pass1"; // Mot de passe FTP

// Connexion FTP
$conn = ftp_connect($ftp_server, $ftp_port);
if (!$conn) {
    die("Impossible de se connecter au serveur FTP : $ftp_server:$ftp_port");
}

// Mode passif
ftp_pasv($conn, true);

// Authentification
if (!ftp_login($conn, $ftp_user, $ftp_pass)) {
    die("Échec de l'authentification FTP pour l'utilisateur $ftp_user");
}

echo "Connexion FTP réussie à $ftp_server:$ftp_port\n";

// Fermer la connexion FTP
ftp_close($conn);
?>
