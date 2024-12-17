<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Algorithme de réconciliation</title>
</head>
<body>

<h1> Algorithme de réconciliation </h1>

<form method="post">
	<button type="submit" name="declencherReconciliation">Réconciliation</button>
</form>
</body>
</html>

<?php

if (isset($_POST['declencherReconciliation'])) {
	reconciliation();
}

function reconciliation() {
	// Algorithme qui vérifie la présence des vidéos dans les 2 NAS.
	// Si une vidéo n'est pas présente dans les 2 NAS, une alerte est lancée

	// #RISQUE : Changement des répertoires des NAS
	$URI_NAS_PAD = "./NAS/NAS_PAD";
	$URI_NAS_ARCH = "./NAS/NAS_ARCH";

	// SelectALL en BD pour récupérer tous les noms des vidéos -- Dans les faits on les récupère dans les NAS
	$nomsVideos_PAD = [];
	$nomsVideos_ARCH = [];

	$nomsVideos_PAD = recupererCollectNAS($URI_NAS_PAD, $nomsVideos_PAD);
	$nomsVideos_ARCH = recupererCollectNAS($URI_NAS_ARCH, $nomsVideos_ARCH);

	echo "<h2>Vidéos présentes sur NAS PAD :</h2>";
	echo "<pre>" . print_r($nomsVideos_PAD, true) . "</pre>";

	echo "<h2>Vidéos présentes sur NAS ARCH :</h2>";
	echo "<pre>" . print_r($nomsVideos_ARCH, true) . "</pre>";

	$listeVideosManquantes = [];
	$listeVideosManquantes = trouverVideosManquantes($nomsVideos_PAD, $nomsVideos_ARCH, $listeVideosManquantes);

	afficherListeVideosManquantes($listeVideosManquantes);
}

function recupererCollectNAS($URI_NAS, $nomsVideos_NAS){
	// Pour chaque fichier dans le répertoire NAS
	$fichiers_NAS = scandir($URI_NAS);

    foreach ($fichiers_NAS as $fichier) {
		if ($fichier !== '.' && $fichier !== '..') {
			$nomsVideos_NAS[] = $fichier;
		}
    }
	return $nomsVideos_NAS;
}


function trouverVideosManquantes($nomsVideos_NAS1, $nomsVideos_NAS2, $listeVideosManquantes) {

    foreach ($nomsVideos_NAS1 as $key1 => $nomVideoNAS1) {
        $videoManquanteDansNAS2 = true;

        foreach ($nomsVideos_NAS2 as $key2 => $nomVideoNAS2) {

			//On compare les fichiers sans tenir compte de leur extension (video.mp4 = video.mxf)
			//(pathinfo pour ne pas tenir compte de l'extension)
            if (pathinfo($nomVideoNAS1, PATHINFO_FILENAME) == pathinfo($nomVideoNAS2, PATHINFO_FILENAME)) {
				unset($nomsVideos_NAS1[$key1]);
                unset($nomsVideos_NAS2[$key2]);
                $videoManquanteDansNAS2 = false;
                break;
            }
        }

		if ($videoManquanteDansNAS2) {
            $listeVideosManquantes[] = [
                'video' => $nomVideoNAS1,
                'manqueDans' => 'NAS ARCH'
            ];
			unset($nomsVideos_NAS1[$key1]);
        }
    }

    // Ajouter les vidéos restantes dans NAS2 qui ne sont pas dans NAS1
    foreach ($nomsVideos_NAS2 as $nomVideoNAS2Restant) {
        $listeVideosManquantes[] = [
            'video' => $nomVideoNAS2Restant,
            'manqueDans' => 'NAS PAD'
        ];
    }

    return $listeVideosManquantes;
}

function afficherListeVideosManquantes($listeVideosManquantes) {
    echo "<h2>Tableau des vidéos manquantes :</h2>";

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    
	#RISQUE : Nom des NAS statique
    echo "<tr>";
		echo "<th>Nom Vidéo</th>";
		echo "<th> NAS </th>";
    echo "</tr>";

    // Parcours de la liste des vidéos manquantes
    foreach ($listeVideosManquantes as $video) {

		$videoName = $video['video'];
        $manqueDans = $video['manqueDans'];

		//Lignes pour chaque élément
			echo "<tr>";
			echo "<td>$videoName</td>";
			echo "<td>$manqueDans</td>";
			echo "</tr>";
    }

    echo "</table>";
}

?>