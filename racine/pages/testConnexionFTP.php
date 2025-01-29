<?php


require_once "../ressources/constantes.php";
require_once "../fonctions/ftp.php";
require_once "../fonctions/ffmpeg.php";
require_once "../fonctions/modele.php";
require_once "../fonctions/fonctions.php";


echo "TEST DE CONNEXION FTP <br>";

$cheminLocalComplet = './video.php';
$cheminDistantNASComplet = './video2.php';

exporterFichierVersNASAvecCheminComplet($cheminLocalComplet, $cheminDistantNASComplet, NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH);

$conn_id = connexionFTP_NAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH);

$cheminLocalComplet = './video3.php';
$cheminDistantNASComplet = './video2.php';

telechargerFichier($conn_id, $cheminLocalComplet, $cheminDistantNASComplet);

ftp_close($conn_id);

?>