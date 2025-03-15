<?php

/**
 * \file fonctions.php
 * \version 1.1
 * \brief Fichier servant à la majorité des fonctions de transferts ffmpeg et autres
 * \author Julien Loridant
 */

/**
 * \fn afficherCollect($titre, $COLLECT_NAS)
 * \brief Fonction qui affiche à l'écran une collection passée en paramètre sous forme de tableau
 * \param titre - Le titre de la collection à afficher
 * \param COLLECT_NAS - Un tableau de métadonnées des vidéos présentes dans le NAS
 */
function afficherCollect($titre, $COLLECT_NAS) {
    echo "<h2>$titre</h2>";
    if (empty($COLLECT_NAS)) {
        echo "<p>Tableau vide</p>";
        return;
    }
    $first_item = reset($COLLECT_NAS); //Récupère le 1er élément, merci le chat j'avais une erreur
    // Vérification si le tableau est vide ou ne contient pas d'éléments valides
    if (!$first_item) {
        echo "<p>Aucun élément valide dans le tableau</p>";
        return;
    }
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>";
    // En-têtes des colonnes
    foreach ($first_item as $key => $value) {
        echo "<th>$key</th>";
    }
    echo "</tr>";
    //Lignes pour chaque élément
    foreach ($COLLECT_NAS as $item) {
        echo "<tr>";
        foreach ($item as $key => $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table><br><br>";
}

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
 * \fn fonctionReconciliation()
 * \brief Fonction qui permet de comparer le contenu des deux NAS pour trouver les vidéos qui ne sont présentes que dans un seul emplacement
 */
function fonctionReconciliation() {
	// Algorithme qui vérifie la présence des vidéos dans les 2 NAS.
	// Si une vidéo n'est pas présente dans les 2 NAS, une alerte est lancée

	// #RISQUE : Incomprehension sur les spec de la fonction de réconciliation
	// Il faudra pouvoir comparer un fichier et ses infos dans la base de données

	$listeVideosNAS_1 = [];
	$listeVideosNAS_2 = [];
	$listeVideosNAS_1 = recupererNomsVideosNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_RACINE_NAS_PAD, $listeVideosNAS_1);
	$listeVideosNAS_2 = recupererNomsVideosNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_RACINE_NAS_ARCH, $listeVideosNAS_2);

	$listeVideosManquantes = [];
	$listeVideosManquantes = trouverVideosManquantes(NAS_PAD, NAS_ARCH, $listeVideosNAS_1, $listeVideosNAS_2, $listeVideosManquantes);

	// #RIQUE : Affichage pas encore implémenté
	//Pour chaque vidéo manquante, afficher un message d'information

    ajouterLog(LOG_SUCCESS, "Fonction de réconciliation effectuée avec succès.");
}


/**
 * \fn trouverVideosManquantes($nomNAS_1, $nomNAS_2, $nomsVideosNAS_1, $nomsVideosNAS_2, $listeVideosManquantes)
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
function trouverVideosManquantes($nomNAS_1, $nomNAS_2, $nomsVideosNAS_1, $nomsVideosNAS_2, $listeVideosManquantes) {
    foreach ($nomsVideosNAS_1 as $key1 => $nomVideoNAS1) {
        $videoManquanteDansNAS2 = true;
        foreach ($nomsVideosNAS_2 as $key2 => $nomVideoNAS2) {

            if (verifierCorrespondanceNomsVideos($nomVideoNAS1, $nomVideoNAS2)) {
				unset($nomsVideosNAS_1[$key1]);
                unset($nomsVideosNAS_2[$key2]);
                $videoManquanteDansNAS2 = false;
                break;
            }
        }
		if ($videoManquanteDansNAS2) {
            $listeVideosManquantes[] = [
                MTD_TITRE => $nomVideoNAS1,
                EMPLACEMENT_MANQUANT => $nomNAS_2
            ];
			unset($nomsVideosNAS_1[$key1]);
        }
    }
    // Ajouter les vidéos restantes dans NAS2 qui ne sont pas dans NAS1
    foreach ($nomsVideosNAS_2 as $nomVideoNAS2Restant) {
        $listeVideosManquantes[] = [
            MTD_TITRE => $nomVideoNAS2Restant,
            EMPLACEMENT_MANQUANT => $nomNAS_1
        ];
    }
    return $listeVideosManquantes;
}

/**
 * \fn afficherVideosManquantes($listeVideosManquantes)
 * \brief Fonction qui permet d'afficher la liste des vidéos manquantes dans un des deux NAS.
 * \param listeVideosManquantes - la liste des vidéos manquantes dans un NAS
 */
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

/**
 * \fn scanDossierDecoupeVideo()
 * \brief Fonction qui permet à la page transferts.php de savoir quels videos sont en train de se faire découper
 */
function scanDossierDecoupeVideo() {
    $listeVideoDownload = array_diff(scandir(URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION), ['.', '..','.gitkeep']);
    $listeVideoDecoupage = array_diff(scandir(URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION), ['.', '..','.gitkeep']);
    $listeVideoConversion = array_diff(scandir(URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION), ['.', '..','.gitkeep']);
    $listeVideoUpload = array_diff(scandir(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD), ['.', '..','.gitkeep']);
	
    $listeVideoDownload = array_map(function($e) { return substr($e, 0, -4); }, $listeVideoDownload);
    $listeVideoDecoupage = array_map(function($e) { return substr($e, 0, -10); }, $listeVideoDecoupage);
    $listeVideoConversion = array_map(function($e) { return substr($e, 0, -10); }, $listeVideoConversion);
    $listeVideoUpload = array_map(function($e) { return substr($e, 0, -4); }, $listeVideoUpload);

    $listeVideo = array_unique(array_merge($listeVideoDownload, $listeVideoDecoupage, $listeVideoConversion, $listeVideoUpload));

    $result = [];
    foreach ($listeVideo as $video) {
        if (in_array($video, $listeVideoUpload)) {
            $status = "En cours d'upload";
        } elseif (in_array($video, $listeVideoConversion)) {
            $status = "En cours de conversion";
        } elseif (in_array($video, $listeVideoDecoupage)) {
            $status = "En cours de découpe";
        } else {
            $status = "En cours de téléchargement";
        }
        $result[] = [
            'nomVideo' => $video,
            'poidsVideo' => recupererTailleFichier($video, null),
            'status' => $status
        ];
    }
    echo json_encode($result);
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


function forcerExtensionMXF($nomFichier){
	$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichier);
	return $nomFichierSansExtension . '.mxf';
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
	$realisateur, 
	$promotion, 
	$projet, 
    $description,
	$cadreur, 
	$responsableSon){

	if (!$profReferent == "") {
		assignerProfReferent($idVid, $profReferent);
	}
	if (!$promotion == "") {
		assignerPromotion($idVid, $promotion);
	}
	if (!$projet == "") {
		assignerProjet($idVid, $projet);
	}
    assignerDescription($idVid, $description);
    assignerRealisateur($idVid, $realisateur);
    assignerCadreur($idVid, $cadreur);
    assignerResponsable($idVid, $responsableSon);
	
	ajouterLog(LOG_SUCCESS, "Modification des métadonnées éditoriales de la vidéo n° $idVid.");
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
    
    $mtdEdito = [
        "projet" => $projet,
        "professeur" => $nomPrenom,
        "realisateur" => $etudiants['Realisateur'],
        "cadreur" => $etudiants['Cadreur'],
        "responsableSon" => $etudiants['Son']
    ];
    
    return $mtdEdito;
}

/**
 * \fn controleurSupprimerVideo($idVideo)
 * \brief Renvoit vrai si le fichier donné est une vidéo 
 * \param directory - Racine de l'endroit qu'on veut scanner
 */
function isVideo($file) {
    $videoExtensions = ['mp4', 'mxf'];
    $extension = pathinfo($file, PATHINFO_EXTENSION);
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
	$operationSucces = exec($commandSql);
	ajouterLog(LOG_INFORM, "Création d'une sauvegarde manuelle de la base le ". date("j-m-Y_H-i-s").".", NOM_FICHIER_LOG_SAUVEGARDE);
}

/*
 * \fn mettreAJourParametres()
 * \brief Récupère les données du formulaire de la page de paramètres et met à jour les constantes
 */
<<<<<<< Updated upstream
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
            'NAS_ARCH' => $_POST['nas_arch'],
            'LOGIN_NAS_ARCH' => $_POST['login_nas_arch'],
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
=======
function changeWhenToSaveDB($minute, $heure, $jour, $mois, $annee) {
    $crontabFile = "/etc/crontab";
    $backupScript = "/var/www/html/fonctions/backup.php";
    $logFile = "/var/log/backup.log";

    // // 🔥 1️⃣ Supprimer l'ancienne tâche backup.php SANS casser le fichier
    // exec("sudo grep -v 'php $backupScript' $crontabFile > /tmp/crontab_tmp && sudo mv /tmp/crontab_tmp $crontabFile", $output, $return_var);
    
    // // Vérification de l'exécution de la suppression
    // if ($return_var !== 0) {
    //     echo "Erreur lors du nettoyage de la crontab. Code d'erreur : $return_var\n";
    //     return;
    // }

    // 🔥 2️⃣ Ajouter la nouvelle tâche backup.php
    $newLine = "$minute $heure $jour $mois * www-data /usr/local/bin/php $backupScript >> $logFile 2>&1";
    exec("echo '$newLine' | sudo tee -a $crontabFile > /dev/null", $output, $return_var);

    // Vérification de l'ajout de la nouvelle tâche
    if ($return_var !== 0) {
        echo "Erreur lors de l'ajout de la nouvelle tâche. Code d'erreur : $return_var\n";
        return;
    }

    // 🔥 3️⃣ Vérifier et corriger les permissions du fichier crontab
    exec("sudo chown root:root $crontabFile && sudo chmod 644 $crontabFile", $output, $return_var);

    if ($return_var !== 0) {
        echo "Erreur lors de la correction des permissions. Code d'erreur : $return_var\n";
        return;
    }

    // 🔥 5️⃣ Redémarrer cron proprement
    exec("sudo service cron restart", $output, $return_var);

    if ($return_var === 0) {
        echo "Crontab mise à jour et cron redémarré avec succès !\n";
    } else {
        echo "Erreur lors du redémarrage de cron. Code d'erreur : $return_var\n";
        return;
    }

    // 🔥 6️⃣ Vérifier si cron tourne
    exec("sudo service cron status", $cronStatus, $return_var);

    if ($return_var === 0) {
        echo "État de cron :\n" . implode("\n", $cronStatus) . "\n";
    } else {
        echo "Erreur lors de la vérification de l'état de cron. Code d'erreur : $return_var\n";
    }
}


>>>>>>> Stashed changes
?>
