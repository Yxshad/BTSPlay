<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fonction de réconciliation</title>
</head>
<body>

<h1> Fonction de réconciliation </h1>

<!-- Formulaire pour choisir les NAS -->
<form method="post">
    <h2>Choisissez les NAS à comparer :</h2>
    <button type="submit" name="declencherReconciliation">Réconciliation</button>
	<br> <br>
</form>

</body>
</html>

<?php

require '../fonctions/fonctions.php';
require '../fonctions/ftp.php';
require '../ressources/constantes.php';
require '../fonctions/ffmpeg.php';

if (isset($_POST['declencherReconciliation'])) {
	fonctionReconciliationAffichee();
}

function fonctionReconciliationAffichee() {
	// Algorithme qui vérifie la présence des vidéos dans les 2 NAS.
	// Si une vidéo n'est pas présente dans les 2 NAS, une alerte est lancée

	$listeVideos_NAS_1 = [];
	$listeVideos_NAS_2 = [];
	$listeVideos_NAS_1 = recupererNomsVideosNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_RACINE_NAS_PAD, $listeVideos_NAS_1);
	$listeVideos_NAS_2 = recupererNomsVideosNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_RACINE_NAS_ARCH, $listeVideos_NAS_2);

	echo "<h2>Vidéos présentes sur " .NAS_PAD.": </h2>";
	echo "<pre>" . print_r($listeVideos_NAS_1, true) . "</pre>";

	echo "<h2>Vidéos présentes sur " .NAS_ARCH.": </h2>";
	echo "<pre>" . print_r($listeVideos_NAS_2, true) . "</pre>";

	$listeVideosManquantes = [];
	$listeVideosManquantes = trouverVideosManquantes(NAS_PAD, NAS_ARCH, $listeVideos_NAS_1, $listeVideos_NAS_2, $listeVideosManquantes);

	afficherVideosManquantes($listeVideosManquantes);

	ajouterLog(LOG_SUCCESS, "Fonction de réconciliation effectuée avec succès.");
}

?>