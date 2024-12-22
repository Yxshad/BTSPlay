<?php 

// $ftp_file = '/home/ftpusers/user1/23_6h_JIN_PUB_OUT.mp4';  // NE MARCHE PAS
$ftp_file = '23_6h_JIN_PUB_OUT.mp4';
$local_file = '../videos/videosAAnalyser/'.$ftp_file;


require '../fonctions/ftp.php';

echo "Page d'accueil, connexion au NAS ARCH  <br>";

$conn_id = connexionFTP_NAS("NAS_ARCH", "user2", "pass2");

//telechargerFichier($conn_id, $local_file,  $ftp_file);

ftp_close($conn_id);

?>