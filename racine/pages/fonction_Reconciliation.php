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

    <label for="NAS_choisi_1">Sélectionnez le premier NAS :</label>
    <select name="NAS_choisi_1" id="NAS_choisi_1">
        <option value="NAS_PAD">NAS PAD</option>
        <option value="NAS_ARCH">NAS ARCH</option>
        <option value="NAS_MPEG">NAS MPEG</option>
    </select>

    <label for="NAS_choisi_2">Sélectionnez le deuxième NAS :</label>
    <select name="NAS_choisi_2" id="NAS_choisi_2">
        <option value="NAS_PAD">NAS PAD</option>
        <option value="NAS_ARCH">NAS ARCH</option>
        <option value="NAS_MPEG">NAS MPEG</option>
    </select>

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
		case "NAS_MPEG":
			$server_1 = NAS_MPEG;
			$nomNAS_1 = NAS_MPEG;
			$login_1 = LOGIN_NAS_MPEG;
			$password_1 = PASSWORD_NAS_MPEG;
			$URI_1 = URI_RACINE_NAS_MPEG;
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
		case "NAS_MPEG":
			$server_2 = NAS_MPEG;
			$nomNAS_2 = NAS_MPEG;
			$login_2 = LOGIN_NAS_MPEG;
			$password_2 = PASSWORD_NAS_MPEG;
			$URI_2 = URI_RACINE_NAS_MPEG;
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


function trouverVideosManquantes($nomNAS_1, $nomNAS_2, $nomsVideos_NAS1, $nomsVideos_NAS2, $listeVideosManquantes) {
    foreach ($nomsVideos_NAS1 as $key1 => $nomVideoNAS1) {
        $videoManquanteDansNAS2 = true;
        foreach ($nomsVideos_NAS2 as $key2 => $nomVideoNAS2) {

            if (verifierCorrespondanceNomsVideos($nomVideoNAS1, $nomVideoNAS2)) {
				unset($nomsVideos_NAS1[$key1]);
                unset($nomsVideos_NAS2[$key2]);
                $videoManquanteDansNAS2 = false;
                break;
            }
        }
		if ($videoManquanteDansNAS2) {
            $listeVideosManquantes[] = [
                MTD_TITRE => $nomVideoNAS1,
                EMPLACEMENT_MANQUANT => $nomNAS_2
            ];
			unset($nomsVideos_NAS1[$key1]);
        }
    }
    // Ajouter les vidéos restantes dans NAS2 qui ne sont pas dans NAS1
    foreach ($nomsVideos_NAS2 as $nomVideoNAS2Restant) {
        $listeVideosManquantes[] = [
            MTD_TITRE => $nomVideoNAS2Restant,
            EMPLACEMENT_MANQUANT => $nomNAS_1
        ];
    }
    return $listeVideosManquantes;
}

function afficherVideosManquantes($listeVideosManquantes) {
    echo "<h2>Tableau des vidéos manquantes :</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
	echo "<tr>";
		echo "<th>".MTD_TITRE."</th>";
		echo "<th>".EMPLACEMENT_MANQUANT."</th>";
    echo "</tr>";
    // Parcours de la liste des vidéos manquantes
    foreach ($listeVideosManquantes as $video) {
		$nomVideo = $video[MTD_TITRE];
        $emplacementManquant = $video[EMPLACEMENT_MANQUANT];
		//Lignes pour chaque élément
		echo "<tr>";
		echo "<td>$nomVideo</td>";
		echo "<td>$emplacementManquant</td>";
		echo "</tr>";
    }
    echo "</table>";
}

?>