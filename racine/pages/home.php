<?php 

#$ftp_file = '/home/ftpusers/user1/23_6h_GRUNT.mp4';  // NE MARCHE PAS
$ftp_file = '23_6h_GRUNT.mp4';
$local_file = '../videos/videosAAnalyser/23_6h_GRUNT.mp4';


require '../fonctions/ftp.php';

echo "Page d'accueil, connexion au NAS ARCH  <br>";

$conn_id = connexionFTP_NAS("NAS_ARCH", "user2", "pass2");

telechargerFichier($conn_id, $local_file,  $ftp_file);

ftp_close($conn_id);

?>