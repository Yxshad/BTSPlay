<?php 

require '../fonctions/ftp.php';

echo "coucou voici la page d'accueil";

$conn_id = connexionFTP_NAS("NAS_ARCH", "user2", "pass2");
ftp_close($conn_id);

?>