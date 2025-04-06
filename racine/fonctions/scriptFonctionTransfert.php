<?php

require_once "../ressources/constantes.php";
require_once "./ftp.php";
require_once "./ffmpeg.php";
require_once "./modele.php";
require_once "./fonctions.php";

fonctionTransfert();

/**
 * \fn fonctionTransfert()
 * \brief Fonction principale qui execute le transfert des fichiers des NAS ARCH et PAD vers le stockage local
 * Alimente aussi la base de données avec les métadonnées techniques des vidéos transférées 
 */
function fonctionTransfert(){
    
	ajouterLog(LOG_INFORM, "Lancement de la fonction de transfert.");
	$COLLECT_PAD = [];
	$COLLECT_ARCH = [];
	$COLLECT_STOCK_LOCAL = [];
	//-----------------------   répertoire NAS_PAD      ------------------------
	$COLLECT_PAD = recupererCollectNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, $COLLECT_PAD, URI_RACINE_NAS_PAD);
	ajouterLog(LOG_INFORM, "Récupération des vidéos du NAS PAD. " . count($COLLECT_PAD) . " fichiers trouvés.");
	//-----------------------   répertoire NAS_ARCH      ------------------------
	$COLLECT_ARCH = recupererCollectNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, $COLLECT_ARCH, URI_RACINE_NAS_ARCH);
	ajouterLog(LOG_INFORM, "Récupération des vidéos du NAS ARCH. " . count($COLLECT_ARCH) . " fichiers trouvés.");
	//Remplir $COLLECT_STOCK_LOCAL
	$COLLECT_STOCK_LOCAL = remplirCOLLECT_STOCK_LOCAL($COLLECT_PAD, $COLLECT_ARCH, $COLLECT_STOCK_LOCAL);
	//Alimenter le Stockage local
	ajouterLog(LOG_INFORM, "Alimentation du stockage local avec " . count($COLLECT_STOCK_LOCAL) . " fichiers." );
	alimenterStockageLocal($COLLECT_STOCK_LOCAL);
    ajouterLog(LOG_SUCCESS, "Fonction de transfert effectuée avec succès.");
}


/**
 * \fn recupererCollectNAS($ftp_server, $ftp_user, $ftp_pass, $COLLECT_NAS, $URI_NAS_RACINE)
 * \brief Fonction qui récupère l'ensemble des métadonnées techniques des vidéos d'un NAS (collectPAD ou collectARCH)
 * - On remplit CollectNAS pour chaque vidéo
 * \param ftp_server - Le nom du serveur ftp auquel on veut accéder
 * \param ftp_user - Nom de l'utilisateur qui se connecte sur le serveur ftp
 * \param ftp_pass - Mot de passe de l'utilisateur se connectant sur le serveur ftp
 * \param COLLECT_NAS - Toutes les vidéos collectées sur les NAS
 * \param URI_NAS_RACINE - URI de la vidéo sur le NAS racine
 */
function recupererCollectNAS($ftp_server, $ftp_user, $ftp_pass, $COLLECT_NAS, $URI_NAS_RACINE){
	$conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);

	// Lister les fichiers sur le serveur FTP
	$fichiersNAS = listerFichiersCompletFTP($conn_id, $URI_NAS_RACINE);
	foreach ($fichiersNAS as $cheminFichierComplet) {

        $nomFichier = basename($cheminFichierComplet);
		$cheminFichier = dirname($cheminFichierComplet) . '/';

        //Si le fichier est une vidéo avec l'extension mxf ou mp4
		if($cheminFichier != "./" && $nomFichier !== '.'
        && $nomFichier !== '..' && isVideo($nomFichier)){
            //Vérifier que la vidéo ne contient pas certains caractères spéciaux
            if(verifierNomVideoAbsenceCaracteresSpeciaux($nomFichier)){
                // Si le fichier n'est pas présent en base
                if (!verifierFichierPresentEnBase($cheminFichier, $nomFichier)) {
                    //RECUPERATION VIA LECTURE FTP
                    $listeMetadonneesVideos = recupererMetadonneesVideoViaFTP($ftp_server, $ftp_user, $ftp_pass, $cheminFichier, $nomFichier);
                    $COLLECT_NAS[] = array_merge($listeMetadonneesVideos, [MTD_URI => $cheminFichier]);
                }
            }
            else{
                ajouterLog(LOG_FAIL, "La vidéo " . $cheminFichierComplet . "contient des caractères spéciaux. Son transfert est ignoré.");
            }
		}
    }
	ftp_close($conn_id);
	return $COLLECT_NAS;
}


/**
 * \fn remplirCOLLECT_STOCK_LOCAL(&$COLLECT_PAD, &$COLLECT_ARCH, $COLLECT_STOCK_LOCAL)
 * \brief Fonction qui remplit $COLLECT_STOCK_LOCAL avec les metadonnées de chaque vidéo présentes dans $COLLECT_PAD ET $COLLECT_ARCH
 * - Vide les vidéos de $COLLECT_PAD et $COLLECT_ARCH qui sont ajoutées dans $COLLECT_STOCK_LOCAL (passage les collections par référence)
 * Traite les vidéos isolées
 * \param COLLECT_PAD - Vidéos collectées sur le NAS PAD
 * \param COLLECT_ARCH - Vidéos collectées sur le NAS Archivage
 * \param COLLECT_STOCK_LOCAL - Tableau qui sera rempli de données à l'issue de la fonction
 * \return COLLECT_STOCK_LOCAL - Tableau contenant toutes les vidéos qui doivent être stockées sur le serveur local
*/
function remplirCOLLECT_STOCK_LOCAL(&$COLLECT_PAD, &$COLLECT_ARCH, $COLLECT_STOCK_LOCAL){

	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
			//Si les deux $ligneCollect correspondent exactement (hors URI) (pathinfo pour ne pas tenir compte de l'extension)
			if (verifierCorrespondanceMdtTechVideos($ligneCollect_PAD, $ligneCollect_ARCH)){
                
				//Remplir $COLLECT_STOCK_LOCAL
				$COLLECT_STOCK_LOCAL[] = [
					MTD_TITRE => $ligneCollect_ARCH[MTD_TITRE],
					MTD_URI_NAS_PAD => $ligneCollect_PAD[MTD_URI],
					MTD_URI_NAS_ARCH => $ligneCollect_ARCH[MTD_URI],
					MTD_FORMAT => $ligneCollect_PAD[MTD_FORMAT],
					MTD_FPS => $ligneCollect_PAD[MTD_FPS],
					MTD_RESOLUTION => $ligneCollect_PAD[MTD_RESOLUTION],
					MTD_DUREE => $ligneCollect_PAD[MTD_DUREE],
                    MTD_DUREE_REELLE => $ligneCollect_PAD[MTD_DUREE_REELLE]
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
		$COLLECT_STOCK_LOCAL[] = [
			MTD_TITRE => $ligneCollect_PAD[MTD_TITRE],
			MTD_URI_NAS_PAD => $ligneCollect_PAD[MTD_URI],
			MTD_URI_NAS_ARCH => null,
			MTD_FORMAT => $ligneCollect_PAD[MTD_FORMAT],
			MTD_FPS => $ligneCollect_PAD[MTD_FPS],
			MTD_RESOLUTION => $ligneCollect_PAD[MTD_RESOLUTION],
			MTD_DUREE => $ligneCollect_PAD[MTD_DUREE],
            MTD_DUREE_REELLE => $ligneCollect_PAD[MTD_DUREE_REELLE]
		];
		unset($COLLECT_PAD[$key_PAD]);
	}
	foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
		$COLLECT_STOCK_LOCAL[] = [
			MTD_TITRE => $ligneCollect_ARCH[MTD_TITRE],
			MTD_URI_NAS_PAD => null,
			MTD_URI_NAS_ARCH => $ligneCollect_ARCH[MTD_URI],
			MTD_FORMAT => $ligneCollect_ARCH[MTD_FORMAT],
			MTD_FPS => $ligneCollect_ARCH[MTD_FPS],
			MTD_RESOLUTION => $ligneCollect_ARCH[MTD_RESOLUTION],
			MTD_DUREE => $ligneCollect_ARCH[MTD_DUREE],
            MTD_DUREE_REELLE => $ligneCollect_ARCH[MTD_DUREE_REELLE]
		];
		unset($COLLECT_ARCH[$key_ARCH]);
	}
	return $COLLECT_STOCK_LOCAL;
}

/**
 * \fn alimenterStockageLocal($COLLECT_STOCK_LOCAL)
 * \brief Alimente le stockage en local des différentes vidéos récupérées dans les autres NAS
 * Les logs sont mis en commentaire en cas d'erreur, ils simplifieront le débogage
 * \param COLLECT_STOCK_LOCAL - Collection des vidéos à stocker sur le serveur local
 * \return COLLECT_STOCK_LOCAL - Liste des vidéos qui ont été implémentées dans le stockage local
 */
function alimenterStockageLocal($COLLECT_STOCK_LOCAL) {
    $tailleDuTableau = count($COLLECT_STOCK_LOCAL);
    $elementsParProcessus = ceil($tailleDuTableau / NB_MAX_PROCESSUS_TRANSFERT);

    $PIDsEnfants = [];

    for ($i = 0; $i < NB_MAX_PROCESSUS_TRANSFERT; $i++) {

        $pid = pcntl_fork();

        if ($pid == -1) {
            ajouterLog(LOG_CRITICAL, "Erreur critique sur le multithreading.");
            die('Duplication impossible');
        } elseif ($pid) {
            // Processus parent : on enregistre le PID du fils
            //ajouterLog(LOG_INFORM, "Processus parent - Fils lancé avec PID : $pid");
            $PIDsEnfants[] = $pid;
        } else {
            // **PROCESSUS ENFANT**
            $debut = $i * $elementsParProcessus;
            $fin = min(($i + 1) * $elementsParProcessus, $tailleDuTableau);

            for ($j = $debut; $j < $fin; $j++) {
                $video = $COLLECT_STOCK_LOCAL[$j];
                //ajouterLog(LOG_INFORM, "Le fils PID " . getmypid() . " travaille sur la vidéo : " . $video[MTD_TITRE]);

                if (!empty($video[MTD_URI_NAS_ARCH])) {
                    $conn_id = connexionFTP_NAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH);
                    $cheminFichierSourceDistant = $video[MTD_URI_NAS_ARCH] . $video[MTD_TITRE];
                } elseif (!empty($video[MTD_URI_NAS_PAD])) {
                    $conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD);
                    $cheminFichierSourceDistant = $video[MTD_URI_NAS_PAD] . $video[MTD_TITRE];
                } else {
                    ajouterLog(LOG_FAIL, "Erreur, la vidéo " . $video[MTD_TITRE] . " n'est présente dans aucun NAS.");
                    exit(0);
                }

                $nomFichierSansExtension = recupererNomFichierSansExtension($video[MTD_TITRE]);
                $nomFichier = $video[MTD_TITRE];

                //Création de tous les dossiers
                $cheminDossier = $video[MTD_URI_NAS_ARCH] ?? $video[MTD_URI_NAS_PAD];

                $cheminDossierAttenteConversion = URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . $cheminDossier . $nomFichierSansExtension . '/';
                $cheminDossierCoursConversion = URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION . $cheminDossier . $nomFichierSansExtension . '_parts/';
                $cheminDossierAttenteUpload = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $cheminDossier . $nomFichierSansExtension . '/';
                $cheminDossierStockageLocal = URI_RACINE_STOCKAGE_LOCAL . $cheminDossier . PREFIXE_DOSSIER_VIDEO . $nomFichierSansExtension . '/';

                $cheminfichierAttenteConversion = $cheminDossierAttenteConversion . $nomFichier;

                creerDossier($cheminDossierAttenteConversion, false);
                creerDossier($cheminDossierCoursConversion, false);
                creerDossier($cheminDossierAttenteUpload, false);
                creerDossier($cheminDossierStockageLocal, false);

                //Téléchargement du fichier distant
                telechargerFichier($conn_id, $cheminfichierAttenteConversion, $cheminFichierSourceDistant);
                ftp_close($conn_id);

                // Conversion et fusion
                traiterVideo($cheminDossierAttenteConversion, $cheminDossierCoursConversion, $nomFichier, $video[MTD_DUREE_REELLE]);
                fusionnerVideo($cheminDossierCoursConversion, $cheminDossierAttenteUpload, $nomFichier);

                //La vidéo a été compressée, on force son extension
                $nomFichier = forcerExtensionMp4($nomFichier);

                $cheminfichierAttenteUpload = $cheminDossierAttenteUpload . $nomFichier;
                $cheminfichierStockageLocal = $cheminDossierStockageLocal . $nomFichier;

                //On déplace la vidéo dans le stockage local
                rename($cheminfichierAttenteUpload, $cheminfichierStockageLocal);

                //On génère la miniature de la vidéo
                $miniature = genererMiniature($cheminfichierStockageLocal, $video[MTD_DUREE]);

                //On met l'URI du stockage local dans les métadonnées à insérer en base
                $cheminDossierStockageLocal = substr($cheminDossierStockageLocal, strlen(URI_RACINE_STOCKAGE_LOCAL));
                $video[MTD_URI_STOCKAGE_LOCAL] = $cheminDossierStockageLocal;
                $video[MTD_TITRE] = $nomFichier;

                //Insertion de la vidéo dans la base de données
                insertionDonneesTechniques($video);

                //Nétoyage  des dossiers. Si un fichier se trouve dans un dossier, celui-ci n'est pas supprimé.
                rmdir($cheminDossierAttenteConversion);
                rmdir($cheminDossierCoursConversion);
                rmdir($cheminDossierAttenteUpload);

                ajouterLog(LOG_INFORM, "La vidéo " . $nomFichier . " a été transférée avec succès.");
            }
            //ajouterLog(LOG_INFORM, "Le fils PID " . getmypid() . " termine.");
            exit(0);
        }
    }
    while (count($PIDsEnfants) > 0) {
        $pidTermine = pcntl_waitpid(-1, $status);
        if ($pidTermine > 0) {
            // Supprime le PID terminé du tableau
            $PIDsEnfants = array_diff($PIDsEnfants, [$pidTermine]);
        }
    }
    //Suppression des dossiers temporaires


    ajouterLog(LOG_INFORM, "Tous les processus fils ont terminé. Le processus de transfert des vidéos est terminé.");
}
?>