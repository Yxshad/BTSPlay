<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Fonction de transfert</title>
</head>
<body>
	<h1> Fonction de transfert </h1>
	<form method="post">
		<button type="submit" name="declencherTransfert">Déclencher la fonction de transfert</button>
	</form>
</body>
</html>

<?php

include_once '../fonctions/fonctions.php';
include_once '../fonctions/ftp.php';
require_once '../ressources/constantes.php';
include_once '../fonctions/ffmpeg.php';

if (isset($_POST['declencherTransfert'])) {
	transfertAffiche();
}

function transfertAffiche(){

	$COLLECT_PAD = [];
	$COLLECT_ARCH = [];
	$COLLECT_MPEG = [];

    echo("<h2> Lancement de l'algorithme </h2>");

	//-----------------------   répertoire NAS_PAD      ------------------------
	$COLLECT_PAD = recupererCollectNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_VIDEOS_A_ANALYSER, $COLLECT_PAD, URI_RACINE_NAS_PAD);

	//-----------------------   répertoire NAS_ARCH      ------------------------
	$COLLECT_ARCH = recupererCollectNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_VIDEOS_A_ANALYSER, $COLLECT_ARCH, URI_RACINE_NAS_ARCH);

	afficherCollect("COLLECT_PAD", $COLLECT_PAD);
	afficherCollect("COLLECT_ARCH", $COLLECT_ARCH);

	//Remplir $COLLECT_MPEG
	$COLLECT_MPEG = remplirCollect_MPEG($COLLECT_PAD, $COLLECT_ARCH, $COLLECT_MPEG);

	afficherCollect("COLLECT_MPEG", $COLLECT_MPEG);
	afficherCollect("COLLECT_PAD", $COLLECT_PAD);
	afficherCollect("COLLECT_ARCH", $COLLECT_ARCH);

	//Alimenter le NAS MPEG
	$COLLECT_MPEG = alimenterNAS_MPEG($COLLECT_MPEG);

	//Mettre à jour la base avec $COLLECT_MPEG
	insertionCollect_MPEG($COLLECT_MPEG);

	$COLLECT_MPEG_après_alimentation = [];
	$COLLECT_MPEG_après_alimentation = recupererCollectNAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG, URI_VIDEOS_A_ANALYSER, $COLLECT_MPEG_après_alimentation, URI_RACINE_NAS_MPEG);
	afficherCollect("NAS MPEG après remplissage", $COLLECT_MPEG_après_alimentation);
}

/**
* - Fonction qui récupère l'ensemble des métadonnées techniques des vidéos d'un NAS (collectPAD ou collectARCH)
* On télécharge les vidéos dans un $URI_VIDEOS_A_ANALYSER si celles-ci ne sont pas présentes dans la BD
* - On remplit CollectNAS pour chaque vidéo
* - On vide le répertoire local $URI_VIDEOS_A_ANALYSER
*/
function recupererCollectNAS($ftp_server, $ftp_user, $ftp_pass, $URI_VIDEOS_A_ANALYSER, $COLLECT_NAS, $URI_NAS_RACINE){
	
	$conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);

	// Lister les fichiers sur le serveur FTP
	$fichiers_NAS = listerFichiersCompletFTP($conn_id, $URI_NAS_RACINE);

	foreach ($fichiers_NAS as $fichier) {
        $nom_fichier = basename($fichier);

		if ($nom_fichier !== '.' && $nom_fichier !== '..') {

			// Si le fichier n'est pas présent en base
			if (!fichierEnBase($nom_fichier)) {

				//Chemin distant
				$cheminFichier = dirname($fichier) . '/';

				//RECUPERATION VIA TELECHARGEMENT FTP --ABANDONNE CAR BESOIN DE TELECHARGER LA VIDEO
				/*$fichierDesination = $URI_VIDEOS_A_ANALYSER . '/' . $nom_fichier;
				telechargerFichier($conn_id, $fichierDesination, $fichier);
				$listeMetadonneesVideos = recupererMetadonneesViaVideoLocale($nom_fichier, $URI_VIDEOS_A_ANALYSER);
				unlink($fichierDesination);*/

				//RECUPERATION VIA LECTURE FTP
				$listeMetadonneesVideos = recupererMetadonneesVideoViaFTP($ftp_server, $ftp_user, $ftp_pass, $cheminFichier, $nom_fichier);

				$COLLECT_NAS[] = array_merge($listeMetadonneesVideos, [MTD_URI => $cheminFichier]);
			}
		}
    }
	ftp_close($conn_id);
	return $COLLECT_NAS;
}

/**
* Fonction qui remplit $COLLECT_MPEG avec les metadonnées de chaque vidéo présentes dans $COLLECT_PAD ET $COLLECT_ARCH
* - Vide les vidéos de $COLLECT_PAD et $COLLECT_ARCH qui sont ajoutées dans $COLLECT_MPEG (passage les collections par référence)
* Traite les vidéos isolées 
*/
function remplirCollect_MPEG(&$COLLECT_PAD, &$COLLECT_ARCH, $COLLECT_MPEG){

	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
			//Si les deux $ligneCollect correspondent exactement (hors URI) (pathinfo pour ne pas tenir compte de l'extension)
			if (verifierCorrespondanceMdtTechVideos($ligneCollect_PAD, $ligneCollect_ARCH)){

				//Remplir $COLLECT_MPEG
				$COLLECT_MPEG[] = [
					MTD_TITRE => $ligneCollect_ARCH[MTD_TITRE],
					MTD_URI_NAS_PAD => $ligneCollect_PAD[MTD_URI],
					MTD_URI_NAS_ARCH => $ligneCollect_ARCH[MTD_URI],
					MTD_FORMAT => $ligneCollect_PAD[MTD_FORMAT],
					MTD_FPS => $ligneCollect_PAD[MTD_FPS],
					MTD_RESOLUTION => $ligneCollect_PAD[MTD_RESOLUTION],
					MTD_DUREE => $ligneCollect_PAD[MTD_DUREE]
				];

				//Retirer $ligneCollect_ARCH et $ligneCollect_PAD de COLLECT_ARCH et $COLLECT_PAD
				unset($COLLECT_PAD[$key_PAD]);
                unset($COLLECT_ARCH[$key_ARCH]);

				break;
			}
		}
	}

	//Traitement des fichiers isolés

	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		$COLLECT_MPEG[] = [
			MTD_TITRE => $ligneCollect_PAD[MTD_TITRE],
			MTD_URI_NAS_PAD => $ligneCollect_PAD[MTD_URI],
			MTD_URI_NAS_ARCH => null,
			MTD_FORMAT => $ligneCollect_PAD[MTD_FORMAT],
			MTD_FPS => $ligneCollect_PAD[MTD_FPS],
			MTD_RESOLUTION => $ligneCollect_PAD[MTD_RESOLUTION],
			MTD_DUREE => $ligneCollect_PAD[MTD_DUREE]
		];
		unset($COLLECT_PAD[$key_PAD]);
	}
	foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
		$COLLECT_MPEG[] = [
			MTD_TITRE => $ligneCollect_ARCH[MTD_TITRE],
			MTD_URI_NAS_PAD => null,
			MTD_URI_NAS_ARCH => $ligneCollect_ARCH[MTD_URI],
			MTD_FORMAT => $ligneCollect_ARCH[MTD_FORMAT],
			MTD_FPS => $ligneCollect_ARCH[MTD_FPS],
			MTD_RESOLUTION => $ligneCollect_ARCH[MTD_RESOLUTION],
			MTD_DUREE => $ligneCollect_ARCH[MTD_DUREE]
		];
		unset($COLLECT_ARCH[$key_ARCH]);
	}
	return $COLLECT_MPEG;
}

function alimenterNAS_MPEG($COLLECT_MPEG){

	foreach($COLLECT_MPEG as $video){
		//Téléchargement du fichier dans le répertoire local
		$fichierDesination = URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $video[MTD_TITRE];

		//Savoir dans quel NAS chercher la vidéo. Si on a le choix, on prend le NAS ARCH
		if($video[MTD_URI_NAS_ARCH] != null){
			$conn_id = connexionFTP_NAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH);
			$fichierSource = $video[MTD_URI_NAS_ARCH] . '/' . $video[MTD_TITRE];
			telechargerFichier($conn_id, $fichierDesination, $fichierSource);
			ftp_close($conn_id);
			$URI_NAS = $video[MTD_URI_NAS_ARCH];
		}
		elseif($video[MTD_URI_NAS_PAD] != null){
			$conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD);
			$fichierSource = $video[MTD_URI_NAS_PAD] . '/' . $video[MTD_TITRE];
			telechargerFichier($conn_id, $fichierDesination, $fichierSource);
			ftp_close($conn_id);
			$URI_NAS = $video[MTD_URI_NAS_PAD];
		}
		else{
			echo("Erreur système, Données incorrectes. Contacter votre administrateur");
		}
		
		decouperVideo($video[MTD_TITRE], $video[MTD_DUREE]);
		convertirVideo($video[MTD_TITRE]);
		fusionnerVideo($video[MTD_TITRE]);
		genererMiniature($video[MTD_TITRE], $video[MTD_DUREE]);

		// Forcer l'extension à .mp4
		$nomFichierSansExtension = pathinfo($video[MTD_TITRE], PATHINFO_FILENAME);
		$video[MTD_TITRE] = $nomFichierSansExtension . '.mp4'; // Forcer l'extension à .mp4

		$fichierSource = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $video[MTD_TITRE];
		$fichierDestination = URI_RACINE_NAS_MPEG .$URI_NAS. $video[MTD_TITRE];

		//Export de la vidéo dans le NAS MPEG
		exporterVideoVersNAS($fichierSource, $fichierDestination, NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);

		//Supprimer la vidéo de l'espace local
		unlink(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $video[MTD_TITRE]);
	}
}


function fichierEnBase($fichier){
	return false;
}

function insertionCollect_MPEG($COLLECT_MPEG){
	return;
}

?>