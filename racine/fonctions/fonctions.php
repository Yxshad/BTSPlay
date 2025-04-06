<?php

/**
 * \file fonctions.php
 * \version 1.1
 * \brief Fichier servant à la majorité des fonctions de transferts ffmpeg et autres
 * \author Julien Loridant
 */


/**
 * \fn verifierCorrespondanceMdtTechVideos($donneesVideo1, $donneesVideo2)
 * \brief Fonction qui vérifie la correspondance de toutes les métadonnées techniques entre 2 vidéos passées en paramètre
 * Une vidéo est un tableau qui contient les métadonnées techniques d'une vidéo (titre, durée, ...)
 * (pathinfo pour ne pas tenir compte de l'extension)
 * \param donneesVideo1 - Données de la première vidéo
 * \param donneesVideo2 - Données de la deuxième vidéo
 * \return boolean
 */
function verifierCorrespondanceMdtTechVideos($donneesVideo1, $donneesVideo2){
    
    if (pathinfo($donneesVideo1[MTD_TITRE], PATHINFO_FILENAME) == pathinfo($donneesVideo2[MTD_TITRE], PATHINFO_FILENAME)
        && $donneesVideo1[MTD_FORMAT] == $donneesVideo2[MTD_FORMAT]
        && $donneesVideo1[MTD_FPS] == $donneesVideo2[MTD_FPS]
        && $donneesVideo1[MTD_RESOLUTION] == $donneesVideo2[MTD_RESOLUTION]
        && $donneesVideo1[MTD_DUREE] == $donneesVideo2[MTD_DUREE]
        && $donneesVideo1[MTD_URI] == $donneesVideo2[MTD_URI]) {
        return true;
    }
    else {
        return false;
    }
}

function verifierCorrespondanceMdtTechVideosAvecBD($donneesVideo1, $donneesVideoBD){
    
    if (pathinfo($donneesVideo1[MTD_TITRE], PATHINFO_FILENAME) == pathinfo($donneesVideoBD['mtd_tech_titre'], PATHINFO_FILENAME)
        && $donneesVideo1[MTD_FORMAT] == $donneesVideoBD['mtd_tech_format']
        && $donneesVideo1[MTD_FPS] == $donneesVideoBD['mtd_tech_fps']
        && $donneesVideo1[MTD_RESOLUTION] == $donneesVideoBD['mtd_tech_resolution']
        && $donneesVideo1[MTD_DUREE] == $donneesVideoBD['mtd_tech_duree']
        && $donneesVideo1[MTD_URI] == $donneesVideoBD[MTD_URI]) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * \fn verifierCorrespondanceNomsVideos($cheminFichierComplet1, $cheminFichierComplet2)
 * Fonction qui vérifie la correspondance des noms des 2 vidéos passées en paramètre
 * On compare les noms des fichiers sans tenir compte de leur extension (video.mp4 = video.mxf)
 * (pathinfo pour ne pas tenir compte de l'extension)
 * On prend cependant compte du chemin du fichier
 * \param cheminFichierComplet1 - Chemin complet du fichier numéro 1
 * \param cheminFichierComplet2 - Chemin complet du fichier numéro 2
 */
function verifierCorrespondanceNomsVideos($cheminFichierComplet1, $cheminFichierComplet2) {

    $cheminFichier1 = pathinfo($cheminFichierComplet1, PATHINFO_DIRNAME);
    $cheminFichier2 = pathinfo($cheminFichierComplet2, PATHINFO_DIRNAME);

    $nomFichier1 = pathinfo($cheminFichierComplet1, PATHINFO_FILENAME);
    $nomFichier2 = pathinfo($cheminFichierComplet2, PATHINFO_FILENAME);

    if ($cheminFichier1 == $cheminFichier2 && $nomFichier1 == $nomFichier2) {
        return true;
    } else {
        return false;
    }
}


/**
 * \fn EtablirDiagnosticVideos($nomNAS_1, $nomNAS_2, $nomsVideosNAS_1, $nomsVideosNAS_2, $listeVideosManquantes)
 * \brief Fonction qui permet de rechercher les vidéos présentes dans un NAS mais pas dans l'autre
 * Prend en paramètre les noms des deux NAS, les listes des noms des vidéos des deux NAS et une liste vide de vidéos manquantes.
 * Retourne $listeVideosManquantes valorisée
 * \param nomNAS_1 - nom du premier NAS inspecté
 * \param nomNAS_2 - nom du second NAS inspecté
 * \param nomsVideosNAS_1 - nom de la vidéo comparée dans le NAS numéro 1
 * \param nomsVideosNAS_2 - nom de la vidéo comparée dans le NAS numéro 2
 * \param listeVideosManquantes - Liste des vidéos manquantes dans les NAS
 * \return listeVideosManquantes - Liste des vidéos manquantes dans les NAS
 */
function EtablirDiagnosticVideos($NAS_PAD, $NAS_ARCH, $nomsCompletVideosNAS_PAD, $nomsCompletVideosNAS_ARCH, $listeVideosBD, $listeVideosDiagnostiquees) {

    //Parcours des vidéos du NAS PAD
    foreach ($nomsCompletVideosNAS_PAD as $key1 => $nomCompletVideoNAS_PAD) {

        //Récupérer les MtdTech de la vidéo via FTP
        $listeMetadonneesVideosNAS_PAD = recupererMetadonneesAvecFormatCheminComplet($nomCompletVideoNAS_PAD, $NAS_PAD);
        
        // 1- Rechercher la vidéo dans nomsCompletVideosNAS_ARCH
        $uneVideoSimilaireTrouvee = false;
        foreach ($nomsCompletVideosNAS_ARCH as $key2 => $nomCompletVideoNAS_ARCH) {

            //Si une vidéo est similaire dans le NAS ARCH (nomComplet est le même)
            if (verifierCorrespondanceNomsVideos($nomCompletVideoNAS_PAD, $nomCompletVideoNAS_ARCH)){
                $uneVideoSimilaireTrouvee = true;

                //Récupérer les MtdTech de la vidéo via FTP
                $listeMetadonneesVideosNAS_ARCH = recupererMetadonneesAvecFormatCheminComplet($nomCompletVideoNAS_ARCH, $NAS_ARCH);

                //Comparer les métadonnées des vidéos
                if (!verifierCorrespondanceMdtTechVideos($listeMetadonneesVideosNAS_PAD, $listeMetadonneesVideosNAS_ARCH)) {
                    ajouterOuMettreAJourDiagnostic($listeVideosDiagnostiquees, $nomCompletVideoNAS_PAD,
                    "La vidéo est différente d'un NAS à l'autre. Veuillez unifier les vidéos.");
                }

                //Supprimer la vidéo de cheminCompletVideosNAS_ARCH car trouvée dans le NAS PAD
                unset($nomsCompletVideosNAS_ARCH[$key2]);
            }

        }
        //Si aucune vidéo n'est trouvée, on informe
        if(!$uneVideoSimilaireTrouvee){
            ajouterOuMettreAJourDiagnostic($listeVideosDiagnostiquees, $nomCompletVideoNAS_PAD,
            'Vidéo manquante du ' . $NAS_ARCH . '.');
        }

        // 2- Rechercher la vidéo dans listeVideosBD

        //Trouver une vidéo
        $infosVideoBD = TrouverVideoAvecURI_NASComplet($listeVideosBD, $nomCompletVideoNAS_PAD, $NAS_PAD);

        //Si la vidéo n'est pas présente en base (pas encore transférée), on informe
        if($infosVideoBD == NULL){
            ajouterOuMettreAJourDiagnostic($listeVideosDiagnostiquees, $nomCompletVideoNAS_PAD,
            "La vidéo n'a pas encore été transférée.");
        }
        else{
            //Sinon, Si les métadonnées de la BD sont à jour
            if(!verifierCorrespondanceMdtTechVideosAvecBD($listeMetadonneesVideosNAS_PAD, $infosVideoBD)){

                ajouterOuMettreAJourDiagnostic($listeVideosDiagnostiquees, $nomCompletVideoNAS_PAD,
                "La vidéo a été changée et la base de données n'est pas à jour. Mise à jour...");

                //Insertion des nouvelles métadonnées dans la base de données
                $listeMetadonneesVideosNAS_PAD = array_merge($listeMetadonneesVideosNAS_PAD, ['id' => $infosVideoBD['id']]);
                mettreAJourMtdTech($listeMetadonneesVideosNAS_PAD);
            }
        }
        unset($nomsCompletVideosNAS_PAD[$key1]);

        $nomsCompletsVideosNAS_ARCH_Restantes = $nomsCompletVideosNAS_ARCH;
    }

    //Parcours des vidéos du NAS ARCH restantes
    foreach ($nomsCompletsVideosNAS_ARCH_Restantes as $key1 => $nomCompletVideoNAS_ARCH_Restante) {

        //Récupérer les MtdTech de la vidéo via FTP
        $listeMetadonneesVideoNAS_ARCH = recupererMetadonneesAvecFormatCheminComplet($nomCompletVideoNAS_ARCH_Restante, $NAS_ARCH);

        //Il y a dans tous les cas une absence du NAS PAD
        ajouterOuMettreAJourDiagnostic($listeVideosDiagnostiquees, $nomCompletVideoNAS_ARCH_Restante,
        'Vidéo manquante du ' . $NAS_PAD . '.');

        //Rechercher la vidéo dans listeVideosBD

        //Trouver une vidéo
        $infosVideoBD = TrouverVideoAvecURI_NASComplet($listeVideosBD, $nomCompletVideoNAS_ARCH_Restante, $NAS_ARCH);

        //Si la vidéo n'est pas présente en base (pas encore transférée), on informe
        if($infosVideoBD == NULL){
            ajouterOuMettreAJourDiagnostic($listeVideosDiagnostiquees, $nomCompletVideoNAS_ARCH_Restante,
            "La vidéo n'a pas encore été transférée.");
        }
        else{
            //Sinon, Si les métadonnées de la BD sont à jour
            if(!verifierCorrespondanceMdtTechVideosAvecBD($listeMetadonneesVideoNAS_ARCH, $infosVideoBD)){

                ajouterOuMettreAJourDiagnostic($listeVideosDiagnostiquees, $nomCompletVideoNAS_ARCH_Restante,
                "La vidéo a été changée et la base de données n'est pas à jour. Mise à jour...");

                //Insertion des nouvelles métadonnées dans la base de données
                $nomCompletVideoNAS_ARCH_Restante = array_merge($nomCompletVideoNAS_ARCH_Restante, ['id' => $infosVideoBD['id']]);
                mettreAJourMtdTech($nomCompletVideoNAS_ARCH_Restante);
            }
        }
        unset($nomsCompletsVideosNAS_ARCH_Restantes[$key1]);
    }

    return $listeVideosDiagnostiquees;
}

function recupererMetadonneesAvecFormatCheminComplet($cheminCompletVideoNAS, $nom_NAS){
    $nomFichier = basename($cheminCompletVideoNAS);
    $cheminFichier = dirname($cheminCompletVideoNAS) . '/';
    if($nom_NAS == NAS_PAD){
        $listeMetadonneesVideosNAS = recupererMetadonneesVideoViaFTP(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, $cheminFichier, $nomFichier);
    }
    else{
        $listeMetadonneesVideosNAS = recupererMetadonneesVideoViaFTP(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, $cheminFichier, $nomFichier);
    }
    $listeMetadonneesVideosNAS = array_merge($listeMetadonneesVideosNAS, [MTD_URI => $cheminFichier]);
    return $listeMetadonneesVideosNAS;
}

function ajouterOuMettreAJourDiagnostic(&$listeVideosManquantes, $cheminCompletVideoNASPAD, $diagnostic) {
    foreach ($listeVideosManquantes as &$video) {
        if ($video[MTD_TITRE] === $cheminCompletVideoNASPAD) {
            $video[DIAGNOSTIC] .= " <br> - " . $diagnostic;
            return;
        }
    }
    $listeVideosManquantes[] = [
        MTD_TITRE => $cheminCompletVideoNASPAD,
        DIAGNOSTIC => " - " . $diagnostic
    ];
}

function TrouverVideoAvecURI_NASComplet($videos, $cheminCompletVideo, $nom_NAS) {
    $nomFichier = basename($cheminCompletVideo);
    $nomFichier = forcerExtensionMp4($nomFichier);
    $cheminFichier = dirname($cheminCompletVideo) . '/';
    if($nom_NAS == NAS_PAD){
        foreach ($videos as $video) {
            if ($video['URI_NAS_PAD'].$video['mtd_tech_titre'] == $cheminFichier.$nomFichier) {
                $video = array_merge($video, [MTD_URI => $cheminFichier]);
                return $video;
            }
        }
    }
    else{
        foreach ($videos as $video) {
            if ($video['URI_NAS_ARCH'].$video['mtd_tech_titre'] == $cheminCompletVideo) {
                $video = array_merge($video, [MTD_URI => $cheminFichier]);
                return $video;
            }
        }
    }
    return null;
}

/**
 * \fn afficherVideosPresentesDansBD($listeVideos)
 * \brief Fonction qui permet d'afficher la liste des vidéos manquantes dans un des deux NAS.
 * \param listeVideosManquantes - la liste des vidéos manquantes dans un NAS
 */
function afficherVideosPresentesDansBD($listeVideos) {
    echo "<h2>Vidéos présentes dans la base de données :</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
	echo "<tr>";
		echo "<th>".MTD_TITRE."</th>";
		echo "<th>".MTD_URI_NAS_PAD."</th>";
        echo "<th>".MTD_URI_NAS_ARCH."</th>";
    echo "</tr>";
    // Parcours de la liste des vidéos manquantes
    foreach ($listeVideos as $video) {
		$nomVideo = $video['mtd_tech_titre'];
        $cheminNAS_PAD = $video['URI_NAS_PAD'];
        $cheminNAS_ARCH = $video['URI_NAS_ARCH'];
		//Lignes pour chaque élément
		echo "<tr>";
		echo "<td>$nomVideo</td>";
        echo "<td>$cheminNAS_PAD</td>";
		echo "<td>$cheminNAS_ARCH</td>";
		echo "</tr>";
    }
    echo "</table>";
}

/**
 * \fn afficherDiagnostiqueVideos($listeDiagnosticVideos)
 * \brief Fonction qui permet d'afficher la liste des vidéos manquantes dans un des deux NAS.
 * \param listeVideosManquantes - la liste des vidéos manquantes dans un NAS
 */
function afficherDiagnostiqueVideos($listeDiagnosticVideos) {
    echo "<h2>Tableau des vidéos manquantes :</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
	echo "<tr>";
		echo "<th>".MTD_TITRE."</th>";
		echo "<th>".DIAGNOSTIC."</th>";
    echo "</tr>";
    // Parcours de la liste des vidéos manquantes
    foreach ($listeDiagnosticVideos as $video) {
		$nomVideo = $video[MTD_TITRE];
        $emplacementManquant = $video[DIAGNOSTIC];
		//Lignes pour chaque élément
		echo "<tr>";
		echo "<td>$nomVideo</td>";
		echo "<td>$emplacementManquant</td>";
		echo "</tr>";
    }
    echo "</table>";
}


/**
 * \fn ajouterLog($typeLog, $message)
 * \brief Fonction qui permet d'ajouter un log dans le fichier de log
 * \param typeLog - Le type de log qu'on veut retourner
 * \param message - Le message qu'on veut mettre dans le log
 */
function ajouterLog($typeLog, $message, $nomFichierLog = NOM_FICHIER_LOG_GENERAL){
    $repertoireLog = URI_FICHIER_GENERES;
    $fichierLog = $repertoireLog . $nomFichierLog;

    // Vérifier si le fichier log.log existe, sinon le créer
    if (!file_exists($fichierLog)) {
        file_put_contents($fichierLog, "");
    }
    $horodatage = date('Y-m-d H:i:s');
    $log = "[$horodatage] $typeLog : $message" . PHP_EOL;
    $handleFichier = fopen($fichierLog, 'a');
    fwrite($handleFichier, $log);
    fclose($handleFichier);
}

/**
 * \fn trouverNomMiniature($nomFichierVideo)
 * \brief Fonction qui permet de trouver le nom de la miniature d'une vidéo
 * \param nomFichierVideo - nom de la vidéo dont on cherche la miniature
 * \return nomFichierSansExtension - le nom de la miniature
 */
function trouverNomMiniature($nomFichierVideo) {
	$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichierVideo);
    return $nomFichierSansExtension . SUFFIXE_MINIATURE_VIDEO;
}


/**
 * \fn trouverNomVideo($nomFichierMiniature)
 * \brief Fonction qui permet de trouver le nom d'une vidéo à partir d'une miniature
 * \param nomFichierMiniature - Nom de la vidéo dont on cherche la miniature
 * \return nomFichierSansExtension le nom de la vidéo
 */
function trouverNomVideo($nomFichierMiniature) {
    $nomFichierSansExtension = str_replace(SUFFIXE_MINIATURE_VIDEO, '', $nomFichierMiniature);
    return $nomFichierSansExtension . SUFFIXE_VIDEO;
}


/**
 * \fn creerDossier(&$cheminDossier, $creationIncrementale)
 * \brief Fonction qui permet de créer un dossier local sans erreur
 * \param cheminDossier - l'URI du dossier à créer
 * \param creationIncrementale - booléen qui indique si on créé de manière incrémentale
 */
function creerDossier(&$cheminDossier, $creationIncrementale, $modeErreur=true){
	
	// Vérifie si le dossier existe, sinon le crée
	if (!is_dir($cheminDossier)) {
		if (!(mkdir($cheminDossier, 0777, true))) {
			if($modeErreur){
                ajouterLog(LOG_FAIL, "Échec lors de la création du dossier $cheminDossier.");
                exit();
            }
		}
	}
	//Si le dossier n'existe pas, on regarde si on créé de manière incrémentale
	else {
        if ($creationIncrementale) {
            $i = 1;
            $nouveauChemin = $cheminDossier . '(' . $i . ')';
            while (is_dir($nouveauChemin)) {
                $i++;
                $nouveauChemin = $cheminDossier . '(' . $i . ')';
            }
            if (!(mkdir($nouveauChemin, 0777, true))) {
                ajouterLog(LOG_FAIL, "Échec lors de la création du dossier $nouveauChemin.");
                if($modeErreur){
                    exit();
                }
            }
			//Pour le passage par référence
			$cheminDossier = $nouveauChemin;
        }
    }
}

/**
 * \fn verifierFichierPresentEnBase($cheminFichier, $nomFichier)
 * \brief Fonction qui véfifie la présence l'un fichier dans la base de données (dans URI_STOCKAGE_LOCAL)
 * \param cheminFichier - le chemin du fichier
 * \param nomFichier - Nom du fichier
 * \return booléen
 */
function verifierFichierPresentEnBase($cheminFichier, $nomFichier){
	$cheminFichierStockageLocal = trouverCheminEspaceLocalVideo($cheminFichier, $nomFichier);
	
	// Forcer l'extension à .mp4 (si des vidéos sont présentes en .mxf)
	$nomFichier = forcerExtensionMp4($nomFichier);

	$videoPresente = verifierPresenceVideoStockageLocal($cheminFichierStockageLocal, $nomFichier);
	return $videoPresente;
}

/**
 * \fn trouverCheminEspaceLocalVideo($cheminFichier, $nomFichier)
 * \brief Fonction qui permet de récupérer le chemin d'un fichier dans le stockage local à partir du chemin dans un autre NAS
 * \param cheminFichier - chemin d'un fichier situé dans le NAS PAD ou ARCH
 * \param nomFichier - Nom du fichier
 * \return cheminFichierStockageLocal - Le chemin du fichier dans le stockage local
 */
function trouverCheminEspaceLocalVideo($cheminFichier, $nomFichier){
	$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichier);
	$cheminFichierStockageLocal = $cheminFichier . PREFIXE_DOSSIER_VIDEO . $nomFichierSansExtension . '/';
	return $cheminFichierStockageLocal;
}

function listerFichiersRecursif($chemin) {
    $fichiers = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($chemin, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $fichier) {
        if (
            $fichier->isFile() &&
            $fichier->getFilename() !== '.gitkeep' &&
            $fichier->getFilename() !== 'file_list.txt'
        ) {
            $cheminComplet = $fichier->getPathname();

            // Créer un chemin relatif pour avoir une clé standard
            $cheminRelatif = str_replace($chemin . DIRECTORY_SEPARATOR, '', $cheminComplet);
            
            //Retirer "_parts" pour les chemins des vidéos en cours de conversion
            $cheminNormalise =  preg_replace('#/(.+?)_parts/#', '/$1/', $cheminRelatif);

            $fichiers[$cheminNormalise] = $cheminComplet;
        }
    }
    return $fichiers;
}

/**
 * \fn scanDossierDecoupeVideo()
 * \brief Fonction qui permet d'afficher les vidéos en cours de transfert
 */
function scanDossierDecoupeVideo() {
    $videosDownload = listerFichiersRecursif(URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION);
    $videosConversion = listerFichiersRecursif(URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION);
    $videosUpload = listerFichiersRecursif(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD);

    // Fusion avec priorité : Upload > Conversion > Download
    $videosFusionnees = [];

    foreach ($videosDownload as $cheminNormalise => $cheminComplet) {
        $cheminNormalise = str_replace(URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION, '', $cheminNormalise);
        $videosFusionnees[$cheminNormalise] = [
            'chemin' => $cheminComplet,
            'status' => "En cours de téléchargement"
        ];
    }
    foreach ($videosConversion as $cheminNormalise => $cheminComplet) {
        $cheminNormalise = str_replace(URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION, '', $cheminNormalise);
        $videosFusionnees[$cheminNormalise] = [
            'chemin' => $cheminComplet,
            'status' => "En cours de conversion"
        ];
    }
    foreach ($videosUpload as $cheminNormalise => $cheminComplet) {
        $cheminNormalise = str_replace(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD, '', $cheminNormalise);
        $videosFusionnees[$cheminNormalise] = [
            'chemin' => $cheminComplet,
            'status' => "En cours d'upload"
        ];
    }

    ajouterLog(LOG_FAIL, print_r($videosFusionnees, true));

    $resultat = [];
    foreach ($videosFusionnees as $cheminNormalise => $infos) {
        $resultat[] = [
            'nomVideo' => basename($cheminNormalise),
            'cheminComplet' => $infos['chemin'],
            'poidsVideo' => recupererTailleFichier($infos['chemin'], null),
            'status' => $infos['status']
        ];
    }
    echo json_encode($resultat);
}

/**
 * \fn recupererTitreVideo($nomFichier)
 * \brief Fonction qui retourne le titre de la vidéo
 * \param nomFichier - le nom d'un fichier
 * \return nomFichierSansExtension - le titre du fichier sans l'année, le projet et l'extension
 */
function recupererTitreVideo($nomFichier){
	$titreVideo = [];
    if (preg_match("/^[^_]*_[^_]*_(.*)(?=\.)/", $nomFichier, $titreVideo)) {
        if (isset($titreVideo[1]) && !empty($titreVideo[1])) {
            return $titreVideo[1];
        }
    }
	else{
		//Si le fichier a un nom particulier, on retourne son nom sans extension
		$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichier);
	}
    return $nomFichierSansExtension;
}

/**
 * \fn recupererExtensionFichier($nomFichier)
 * \brief Fonction récupérant l'extension du fichier mis en paramètre
 * \param nomFichier - Nom du fichier
 * \return string - extension du fichier
 */
function recupererExtensionFichier($nomFichier){
	return substr(pathinfo($nomFichier, PATHINFO_EXTENSION), -3);
}

/**
 * \fn recupererNomFichierSansExtension($nomFichier)
 * \brief Fonction récupérant le nom du fichier mis en paramètre sans l'extension de fichier
 * \param nomFichier - Nom du fichier
 * \return string - nom du fichier sans extension
 */
function recupererNomFichierSansExtension($nomFichier){
	return pathinfo($nomFichier, PATHINFO_FILENAME);
}

/**
 * \fn forcerExtensionMp4($nomFichier)
 * \brief Fonction qui force le fichier donné à obtenir l'extension mp4
 * \param nomFichier - Nom du fichier
 * \return string - nom du fichier avec l'extension mp4
 */
function forcerExtensionMp4($nomFichier){
	$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichier);
	return $nomFichierSansExtension . '.mp4';
}

/**
 * \fn forcerExtensionMXF($nomFichier)
 * \brief Fonction qui force le fichier donné à obtenir l'extension mxf
 * \param nomFichier - Nom du fichier
 * \return string - nom du fichier avec l'extension mxf
 */
function forcerExtensionMXF($nomFichier){
	$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichier);
	return $nomFichierSansExtension . '.mxf';
}

/**
 * \fn verifierNomVideoAbsenceCaracteresSpeciaux($nomFichier)
 * \brief Fonction qui vérifier qu'un nom du fichier ne comporte pas de caractères spéciaux
 * \param nomFichier - Nom du fichier
 * \return booleen - vrai si le nom du fichier est valide
 */
function verifierNomVideoAbsenceCaracteresSpeciaux($nomFichier){
    return !preg_match('/[%\s()\'"]/', $nomFichier);
}

/**
 * \fn miseAJourMetadonneesVideo($idVid, $profReferent, $realisateur, $promotion, $projet, $cadreur, $responsableSon)
 * \brief Fonction qui permet de modifier les métadonnées éditoriales d'une vidéo
 * \param idVid - l'id de la vidéo
 * \param profReferent - ID du professeur référent
 * \param realisateur - le nom d'un réalisateur
 * \param promotion - l'année de la promotion
 * \param projet - le nom du projet
 * \param cadreur - le nom du cadreur
 * \param responsableSon - le nom du responsable son
 */
function miseAJourMetadonneesVideo(
    $idVid,
    $profReferent,
    $promotion,
    $projet,
    $description,
    $roles
) {
    assignerProfReferent($idVid, $profReferent);
    assignerPromotion($idVid, $promotion);
    assignerProjet($idVid, $projet);
    assignerDescription($idVid, $description);
    // Mise à jour des rôles dynamiques
    foreach ($roles as $role => $listePersonnesCsv) {
        // Séparation des noms en tableau, suppression des espaces et des entrées vides
        $tabPersonnes = array_filter(array_map('trim', explode(',', $listePersonnesCsv)));

        $idRoleActuel = getRole($role);
        if($idRoleActuel){
            deleteFromRoles($idVid, $idRoleActuel['id']);
        }

        foreach ($tabPersonnes as $personne) {
            assignerRole($idVid, $role, $personne);
        }
    }

    ajouterLog(LOG_SUCCESS, "Modification des métadonnées éditoriales de la vidéo n° $idVid.");
}

/**
 * \fn getMetadonneesEditorialesVideo($video)
 * \brief Récupère toutes les metadonneesEditoriales de la vidéo à partir de son id
 * \param video - id de la vidéo
 * \return mtdEdito - Tableau de métadonnées éditoriales qui doivent être insérées
 */
function assemblerRolesEtParticipantsDeVideo($video) {
    $req = getRolesEtParticipantsDeVideo($video);
    if($req){
        foreach ($req as $item) {
            $role = $item['libelle'];
            $nom = $item['nomComplet'];
            
            if (!isset($result[$role])) {
                $result[$role] = [];
            }
            
            $result[$role][] = $nom;
        }
        
        // Convertit les tableaux de noms en chaînes séparées par des virgules
        foreach ($result as $role => &$names) {
            $names = implode(', ', $names);
        }
        return $result;
    }
}

/**
 * \fn getMetadonneesEditorialesVideo($video)
 * \brief Récupère toutes les metadonneesEditoriales de la vidéo à partir de son id
 * \param video - id de la vidéo
 * \return mtdEdito - Tableau de métadonnées éditoriales qui doivent être insérées
 */
function getMetadonneesEditorialesVideo($video) {
    $projet = getProjetIntitule($video["projet"]);
    $nomPrenom = getProfNomPrenom($video["professeurReferent"]);
    $nomPrenom = implode(" ", $nomPrenom);
    $etudiants = getParticipants($video["id"]);
    $roles = getAllRoles();

    $mtdEdito = [
        "projet" => $projet,
        "professeur" => $nomPrenom,
        "realisateur" => $etudiants['Realisateur'],
        "cadreur" => $etudiants['Cadreur'],
        "responsableSon" => $etudiants['Son'],
        "roles" => $roles
    ];
    
    return $mtdEdito;
}

/**
 * \fn isVideo($file)
 * \brief Renvoit vrai si le fichier donné est une vidéo 
 * \param file - nom du fichier dont on regarde l'extension
 */
function isVideo($file) {
    $videoExtensions = ['mp4', 'mxf'];
    $extension = recupererExtensionFichier($file);
    return in_array(strtolower($extension), $videoExtensions);
}

/**
 * \fn afficherDossier($path, $item)
 * \brief Affiche une div avec la classe dossier, et l'arborescence en data-path
 * \param path - Chemin de l'objet
 * \param item - Nom de l'objet à afficher en tant que dossier
 */
function afficherDossier($path, $item){ ?>
    <div data-path ="<?php echo $path; ?>" class="dossier">
        <?php echo $item; ?>
    </div>
<?php }

/**
 * \fn afficherVideo($path, $item, $item)
 * \brief Affiche une div avec la classe vidéo, et l'arborescence en data-path
 * \param path - Chemin de l'objet
 * \param item - Nom de l'objet à afficher en tant que vidéo
 * \param id - Identifiant de la vidéo à mettre dans le lien
 */
function afficherVideo($path, $item, $id){ ?>
	
	<?php if ($id) { ?>
		<div data-path ="<?php echo $path; ?>" class="video" >
			<a href="video.php?v=<?php echo $id; ?>">
				<?php echo $item; ?>
			</a>
		</div>
	<?php } else { ?>
		<div data-path ="<?php echo $path; ?>" class="video inaccessible" >
			<a href="erreur.php?code=415">
				<?php echo $item; ?>
			</a>
		</div>
	<?php } ?>

<?php }

/**
 * \fn afficherFichier($path, $item)
 * \brief Affiche une div avec la classe fichier, et l'arborescence en data-path
 * \param path - Chemin de l'objet
 * \param item - Nom de l'objet à afficher en tant que ficher
 */
function afficherFichier($path, $item){ ?>
    <div data-path ="<?php echo $path; ?>" class="fichier inaccessible">
        <?php echo $item; ?>
    </div>
<?php }

/*
 * \fn createDatabaseSave()
 * \brief Permet de lancer une sauvegarde de la base de données
 */
function createDatabaseSave(){
    $commandSql = 'mysqldump --user='.BD_USER.' --password='.BD_PASSWORD.' --host=mysql '.BD_NAME.' > '. URI_DUMP_SAUVEGARDE .date("j-m-Y_H-i-s_").SUFFIXE_FICHIER_DUMP_SAUVEGARDE;
	$operationSucces = exec($commandSql, $output, $exitCode);
	ajouterLog(LOG_INFORM, "Création d'une sauvegarde manuelle de la base le ". date("j-m-Y_H-i-s").".", NOM_FICHIER_LOG_SAUVEGARDE);
    return $exitCode;
}

/*
 * \fn mettreAJourParametres()
 * \brief Récupère les données du formulaire de la page de paramètres et met à jour les constantes
 */
function mettreAJourParametres(){
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(empty($_POST['affichage_logs_plus_recents_premiers'])){
            $_POST['affichage_logs_plus_recents_premiers']='off';
        }
        // Récupérer les données du formulaire
        $formData = [
            'URI_RACINE_NAS_PAD' => $_POST['uri_racine_nas_pad'],
            'URI_RACINE_NAS_ARCH' => $_POST['uri_racine_nas_arch'],
            'URI_RACINE_STOCKAGE_LOCAL' => $_POST['uri_racine_stockage_local'],
            'URI_RACINE_NAS_DIFF' => $_POST['uri_racine_nas_diff'],
            'NAS_PAD' => $_POST['nas_pad'],
            'LOGIN_NAS_PAD' => $_POST['login_nas_pad'],
            'PASSWORD_NAS_PAD' => $_POST['password_nas_pad'],
            'LOGIN_NAS_PAD_SUP' => $_POST['login_nas_pad_sup'],
            'PASSWORD_NAS_PAD_SUP' => $_POST['password_nas_pad_sup'],
            'NAS_ARCH' => $_POST['nas_arch'],
            'LOGIN_NAS_ARCH' => $_POST['login_nas_arch'],
            'PASSWORD_NAS_ARCH_SUP' => $_POST['password_nas_arch_sup'],
            'LOGIN_NAS_ARCH_SUP' => $_POST['login_nas_arch_sup'],
            'PASSWORD_NAS_ARCH' => $_POST['password_nas_arch'],
            'NAS_DIFF' => $_POST['nas_diff'],
            'LOGIN_NAS_DIFF' => $_POST['login_nas_diff'],
            'PASSWORD_NAS_DIFF' => $_POST['password_nas_diff'],
            'BD_HOST' => $_POST['bd_host'],
            'BD_PORT' => $_POST['bd_port'],
            'BD_NAME' => $_POST['bd_name'],
            'BD_USER' => $_POST['bd_user'],
            'BD_PASSWORD' => $_POST['bd_password'],
            'URI_FICHIER_GENERES' => $_POST['uri_fichier_generes'],
            'URI_DUMP_SAUVEGARDE' => $_POST['uri_dump_sauvegarde'],
            'URI_CONSTANTES_SAUVEGARDE' => $_POST['uri_constantes_sauvegarde'],
            'NOM_FICHIER_LOG_GENERAL' => $_POST['nom_fichier_log_general'],
            'NOM_FICHIER_LOG_SAUVEGARDE' => $_POST['nom_fichier_log_sauvegarde'],
            'SUFFIXE_FICHIER_DUMP_SAUVEGARDE' => $_POST['suffixe_fichier_dump_sauvegarde'],
            'SUFFIXE_FICHIER_CONSTANTES_SAUVEGARDE' => $_POST['suffixe_fichier_constantes_sauvegarde'],
            'NB_VIDEOS_PAR_SWIPER' => $_POST['nb_videos_par_swiper'],
            'NB_VIDEOS_HISTORIQUE_TRANSFERT' => $_POST['nb_videos_historique_transfert'],
            'NB_LIGNES_LOGS' => $_POST['nb_lignes_logs'],
            'NB_MAX_PROCESSUS_TRANSFERT' => $_POST['nb_max_processus_transfert'],
            'NB_MAX_SOUS_PROCESSUS_TRANSFERT' => $_POST['nb_max_sous_processus_transfert'],
            'AFFICHAGE_LOGS_PLUS_RECENTS_PREMIERS' => $_POST['affichage_logs_plus_recents_premiers'],
        ];
    
        // Appeler la fonction pour mettre à jour les constantes
        mettreAJourConstantes($formData);
    }
}

/*
 * \fn mettreAJourConstantes($data)
 * \brief Met à jour les constantes pour le paramétrage du site
 * \param data - Données mises à jour du formulaire de la page de paramètres
 */
function mettreAJourConstantes($data) {
    // Réaliser la sauvegarde du fichier
    $cheminFichier = '../ressources/constantes.php';
    $dossierSauvegarde = URI_CONSTANTES_SAUVEGARDE;

    // Créer une copie du fichier avec un horodatage
    $nomSauvegarde = date("j-m-Y_H-i-s_") . SUFFIXE_FICHIER_CONSTANTES_SAUVEGARDE;
    copy($cheminFichier, $dossierSauvegarde . $nomSauvegarde);

    // Lire le fichier constantes.php dans un tableau
    $lines = file('../ressources/constantes.php');

    // Parcourir chaque ligne du fichier
    foreach ($lines as &$line) {
        // Vérifier si la ligne contient une constante
        if (preg_match('/^\s*const\s+(\w+)\s*=\s*[\'"]?(.*?)[\'"]?\s*;/', $line, $matches)) {
            $constantName = $matches[1]; // Nom de la constante
            $currentValue = $matches[2]; // Valeur actuelle de la constante

            // Si la constante est dans les données du formulaire et que la valeur est différente
            if (isset($data[$constantName]) && $data[$constantName] !== $currentValue) {
                // Mettre à jour la ligne avec la nouvelle valeur
                $line = "const $constantName = '{$data[$constantName]}';\n";
            }
        }
    }

    // Réécrire le fichier avec les modifications
    file_put_contents('../ressources/constantes.php', implode('', $lines));
    ajouterLog(LOG_SUCCESS, "Mise à jour des paramétrages du site le ". date("j-m-Y_H-i-s").".");
}

function changeWhenToSaveDB($minute, $heure, $annee, $mois, $jour){
    try{
    $file = '/etc/crontab';
    exec("sudo chown www-data:www-data /etc/crontab");
    
    // Supprimer le zéro en tête si présent
    $minute = ltrim($minute, '0');
    if($minute==""){$minute = '0';}

    // Supprimer le zéro en tête si présent
    $heure = ltrim($heure, '0');
    if($heure==""){$heure = '0';}
    
    // Lire tout le fichier dans un tableau
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Modifier ou ajouter la ligne correspondant à scriptBackup.php
    $found = false;
    foreach ($lines as $index => $line) {
        if (strpos($line, "scriptBackup.php") !== false) {
            $lines[$index] = "$minute $heure * $mois $jour root php /var/www/html/fonctions/scriptBackup.php >> /var/log/backup.log";
            $found = true;
            break;
        }
    }
    
    // Ajouter la ligne si elle n'existe pas
    
    if (!$found) {
        $lines[] = "$minute $heure $jour $mois * root php /var/www/html/fonctions/backup.php >> /var/log/backup.log";
    }

    // Ajouter un saut de ligne final pour éviter les erreurs de format
    $updatedContent = implode("\n", $lines) . "\n";

    // Écrire le fichier mis à jour
    file_put_contents($file, $updatedContent);

    // Redémarrer le service cron et vérifier le statut
    exec("service cron restart", $output, $return_var);
    echo "Service cron restart status: " . $return_var . "\n";
    
    exec("sudo chown root:root /etc/crontab");

    ajouterLog(LOG_SUCCESS, "Création d'une sauvegarde automatique de la base le ". date("j-m-Y_H-i-s").".", NOM_FICHIER_LOG_SAUVEGARDE);

    echo "Dernière ligne modifiée avec succès !";
}
catch (Exception){
    echo "Erreur dans la programmation de la sauvegarde";
}
}

?>
