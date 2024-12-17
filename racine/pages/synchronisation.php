<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Algorithme de synchronisation</title>
</head>
<body>

<h1> Algorithme de synchronisation </h1>

<form method="post">
	<button type="submit" name="declencherSynchro">Synchro</button>
</form>
</body>
</html>

<?php

require '../fonctions/fonctions.php';

if (isset($_POST['declencherSynchro'])) {
	synchronisation();
}

function synchronisation(){

	// #RISQUE : Changement des répertoires des NAS
	$URI_NAS_PAD = "./NAS/NAS_PAD";
	$URI_NAS_ARCH = "./NAS/NAS_ARCH";
	$URI_NAS_MPEG = "./NAS/NAS_MPEG";
	$URI_ESPACE_LOCAL_PAD = './espaceLocal_PAD';
	$URI_ESPACE_LOCAL_ARCH = './espaceLocal_ARCH';
	$COLLECT_PAD = [];
	$COLLECT_ARCH = [];
	$COLLECT_MPEG = [];

    echo("<h2> Lancement de l'algorithme </h2>");

	
	//-----------------------   répertoire NAS_PAD      ------------------------
	$COLLECT_PAD = recupererCollectNAS($URI_NAS_PAD, $URI_ESPACE_LOCAL_PAD, $COLLECT_PAD);

	//-----------------------   répertoire NAS_ARCH      ------------------------
	$COLLECT_ARCH = recupererCollectNAS($URI_NAS_ARCH, $URI_ESPACE_LOCAL_ARCH, $COLLECT_ARCH);

	afficherCollect("COLLECT_PAD", $COLLECT_PAD);
	afficherCollect("COLLECT_ARCH", $COLLECT_ARCH);

	//Remplir $COLLECT_MPEG
	$COLLECT_MPEG = remplirCollect_MPEG($COLLECT_PAD, $COLLECT_ARCH, $COLLECT_MPEG);

	afficherCollect("COLLECT_MPEG", $COLLECT_MPEG);
	afficherCollect("COLLECT_PAD", $COLLECT_PAD);
	afficherCollect("COLLECT_ARCH", $COLLECT_ARCH);

	//Mettre à jour la base avec $COLLECT_MPEG
	insertionCollect_MPEG($COLLECT_MPEG);
}

/*
- Fonction qui récupère l'ensemble des métadonnées techniques des vidéos d'un NAS (collectPAD ou collectARCH)

- On télécharge les vidéos dans un $URI_ESPACE_LOCAL si celles-ci ne sont pas présentes dans la BD
- On remplit CollectNAS pour chaque vidéo
- On vide le répertoire local $URI_ESPACE_LOCAL
*/
function recupererCollectNAS($URI_NAS, $URI_ESPACE_LOCAL, $COLLECT_NAS){

	// Pour chaque fichier dans le répertoire NAS
	$fichiers_NAS = scandir($URI_NAS);

    foreach ($fichiers_NAS as $fichier) {
		if ($fichier !== '.' && $fichier !== '..') {

			//Si le fichier n'est pas présent en base
			if(!fichierEnBase($fichier)){

				//Copie du fichier dans le repertoire local ----Dans les faits, téléchargement via FTP
				copierFichier($fichier, $URI_NAS, $URI_ESPACE_LOCAL);

				//Récupérer les métadonnées techniques du $fichier et les ajouter dans collectNAS
				$listeMetadonneesVideos = recupererMetadonnees($fichier, $URI_ESPACE_LOCAL);

				$COLLECT_NAS[] = array_merge($listeMetadonneesVideos[0], ['URI' => $URI_NAS]);

				//Vider le fichier de l'espace local
				$cheminFichier = $URI_ESPACE_LOCAL . '/' . $fichier;
				unlink($cheminFichier);
			};
		}
    }
	return $COLLECT_NAS;
}

/*
Fonction qui copie un $fichier situé dans $URI_NAS et le colle dans $URI_ESPACE_LOCAL
*/
function copierFichier($fichier, $URI_NAS, $URI_ESPACE_LOCAL) {

	$fichier_source = $URI_NAS . '/' . $fichier;
	$fichier_destination = $URI_ESPACE_LOCAL . '/' . $fichier;

	if (file_exists($fichier_source) && !file_exists($fichier_destination)) {

		if (copy($fichier_source, $fichier_destination)) {
			echo "Fichier $fichier copié avec succès vers l'espace local. <br>";
		}
		else {
			echo "Erreur lors de la copie de $fichier. <br>";
		}
	} else {
		echo "Le fichier $fichier n'existe pas dans $URI_NAS ou existe deja dans $URI_ESPACE_LOCAL. <br> ";
	}
}

/*
Fonction qui remplit $COLLECT_MPEG avec les metadonnées de chaque vidéo présentes dans $COLLECT_PAD ET $COLLECT_ARCH

- Vide les vidéos de $COLLECT_PAD et $COLLECT_ARCH qui sont ajoutées dans $COLLECT_MPEG (passage les collections par référence)
- Traite les vidéos isolées 
*/
function remplirCollect_MPEG(&$COLLECT_PAD, &$COLLECT_ARCH, $COLLECT_MPEG){

	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {

			//Si les deux $ligneCollect correspondent exactement (hors URI) (pathinfo pour ne pas tenir compte de l'extension)
			if (verifierCorrespondanceMdtTechVideos($ligneCollect_PAD, $ligneCollect_ARCH)){

				//Remplir $COLLECT_MPEG
				$COLLECT_MPEG[] = [
					'TITRE' => $ligneCollect_PAD['TITRE'],
					'URI_NAS_PAD' => $ligneCollect_PAD['URI'],
					'URI_NAS_ARCH' => $ligneCollect_ARCH['URI'],
					'FORMAT' => $ligneCollect_PAD['FORMAT'],
					'RESOLUTION' => $ligneCollect_PAD['RESOLUTION'],
					'DUREE' => $ligneCollect_PAD['DUREE']
				];

				//Retirer $ligneCollect_ARCH et $ligneCollect_PAD de COLLECT_ARCH et $COLLECT_PAD
				unset($COLLECT_PAD[$key_PAD]);
                unset($COLLECT_ARCH[$key_ARCH]);

				//Ajouter un id unique à un média

				//Traiter la compression

				break;
			}
		}
	}

	//Traitement des fichiers isolés

	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		$COLLECT_MPEG[] = [
			'TITRE' => $ligneCollect_PAD['TITRE'],
			'URI_NAS_PAD' => $ligneCollect_PAD['URI'],
			'URI_NAS_ARCH' => null,
			'FORMAT' => $ligneCollect_PAD['FORMAT'],
			'RESOLUTION' => $ligneCollect_PAD['RESOLUTION'],
			'DUREE' => $ligneCollect_PAD['DUREE']
		];
		unset($COLLECT_PAD[$key_PAD]);
	}
	foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
		$COLLECT_MPEG[] = [
			'TITRE' => $ligneCollect_ARCH['TITRE'],
			'URI_NAS_PAD' => null,
			'URI_NAS_ARCH' => $ligneCollect_ARCH['URI'],
			'FORMAT' => $ligneCollect_ARCH['FORMAT'],
			'RESOLUTION' => $ligneCollect_ARCH['RESOLUTION'],
			'DUREE' => $ligneCollect_ARCH['DUREE']
		];

		unset($COLLECT_ARCH[$key_ARCH]);
	}


	return $COLLECT_MPEG;
}

function fichierEnBase($fichier){
	return false;
}

function insertionCollect_MPEG($COLLECT_MPEG){
	
	return;
}

?>