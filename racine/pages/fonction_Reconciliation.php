<?php session_start();?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
    
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

	
<?php require '../ressources/Templates/header.php';?>

<h1> Fonction de réconciliation </h1>

<!-- Formulaire pour choisir les NAS -->
<form method="post">
    <button type="submit" name="declencherReconciliation">Réconciliation</button>
	<br> <br>
</form>

</html>

<?php

include_once '../fonctions/fonctions.php';
include_once '../fonctions/ftp.php';
require_once '../ressources/constantes.php';
include_once '../fonctions/ffmpeg.php';

if (isset($_POST['declencherReconciliation'])) {
    if (isset($_POST['NAS_choisi_1']) && isset($_POST['NAS_choisi_2'])) {
        $NASChoisi1 = $_POST['NAS_choisi_1'];
        $NASChoisi2 = $_POST['NAS_choisi_2'];

        // Lancer la réconciliation entre les NAS sélectionnés
        reconciliation($NASChoisi1, $NASChoisi2);
    }
}

function reconciliation($NASChoisi1, $NASChoisi2) {
	// Algorithme qui vérifie la présence des vidéos dans les 2 NAS.
	// Si une vidéo n'est pas présente dans les 2 NAS, une alerte est lancée

	// #RISQUE : Incomprehension sur les spec de la fonction de réconciliation

	// SelectALL en BD pour récupérer tous les noms des vidéos -- Dans les faits on les récupère dans les NAS
	$listeVideos_NAS_1 = [];
	$listeVideos_NAS_2 = [];

	// Initialisation des paramètres du NAS 1
	switch ($NASChoisi1) {
		case "NAS_PAD":
			$server_1 = NAS_PAD;
			$nomNAS_1 = NAS_PAD;
			$login_1 = LOGIN_NAS_PAD;
			$password_1 = PASSWORD_NAS_PAD;
			$URI_1 = URI_RACINE_NAS_PAD;
			break;
		case "NAS_ARCH":
			$server_1 = NAS_ARCH;
			$nomNAS_1 = NAS_ARCH;
			$login_1 = LOGIN_NAS_ARCH;
			$password_1 = PASSWORD_NAS_ARCH;
			$URI_1 = URI_RACINE_NAS_ARCH;
			break;
	}

	// Initialisation des paramètres du NAS 2
	switch ($NASChoisi2) {
		case "NAS_PAD":
			$server_2 = NAS_PAD;
			$nomNAS_2 = NAS_PAD;
			$login_2 = LOGIN_NAS_PAD;
			$password_2 = PASSWORD_NAS_PAD;
			$URI_2 = URI_RACINE_NAS_PAD;
			break;
		case "NAS_ARCH":
			$server_2 = NAS_ARCH;
			$nomNAS_2 = NAS_ARCH;
			$login_2 = LOGIN_NAS_ARCH;
			$password_2 = PASSWORD_NAS_ARCH;
			$URI_2 = URI_RACINE_NAS_ARCH;
			break;
	}

	$listeVideos_NAS_1 = recupererNomsVideosNAS($server_1, $login_1, $password_1, $URI_1, $listeVideos_NAS_1);
	$listeVideos_NAS_2 = recupererNomsVideosNAS($server_2, $login_2, $password_2, $URI_2, $listeVideos_NAS_2);

	echo "<h2>Vidéos présentes sur " .$nomNAS_1.": </h2>";
	echo "<pre>" . print_r($listeVideos_NAS_1, true) . "</pre>";

	echo "<h2>Vidéos présentes sur " .$nomNAS_2.": </h2>";
	echo "<pre>" . print_r($listeVideos_NAS_2, true) . "</pre>";

	$listeVideosManquantes = [];
	$listeVideosManquantes = trouverVideosManquantes($nomNAS_1, $nomNAS_2, $listeVideos_NAS_1, $listeVideos_NAS_2, $listeVideosManquantes);

	afficherVideosManquantes($listeVideosManquantes);
}

	ajouterLog(LOG_SUCCESS, "Fonction de réconciliation effectuée avec succès.");

	require '../ressources/Templates/footer.php';


?>