<?php

/**
 * \file ffmpeg.php
 * \version 1.1
 * \brief Fichier regroupant toutes les fonctions de transferts des métadonnées et vidéos
 * \author Axel Marrier
 */



 /**
 * \fn recupererMetadonneesViaVideoLocale($fichier, $URI_ESPACE_LOCAL)
 * \brief Fonction qui retourne la liste des métadonnées techniques d'une vidéo passée en paramètre
 * \param fichier - Nom de la vidéo
 * \param URI_ESPACE_LOCAL - Uri du fichier sur l'espace local
 * \return tableau généré par la fonction recupérerMetadonnees
 */
function recupererMetadonneesViaVideoLocale($fichier, $URI_ESPACE_LOCAL){
	$fichier_source = $URI_ESPACE_LOCAL . '/' . $fichier;
    $command = URI_FFMPEG." -i $fichier_source 2>&1";
    exec($command, $output);
    $meta = implode($output);
    return recupererMetadonnees($meta, $fichier);
}


 /**
 * \fn recupererMetadonneesVideoViaFTP($ftp_server, $ftp_user, $ftp_pass, $cheminFichier, $nomFichier)
 * \brief Fonction qui retourne la liste des métadonnées techniques d'une vidéo passée en paramètre
 * \param ftp_server - Serveur ftp sur lequel on veut se connecter
 * \param ftp_user - Utilisateur ftp qu'on utilise pour la connexion
 * \param ftp_pass - Mot de passe de l'utilisateur qu'on utilise
 * \param cheminFichier - Uri du fichier sur l'espace local
 * \param nomFichier - Nom du fichier recherché
 * \return tableau généré par la fonction recupérerMetadonnees
 */
function recupererMetadonneesVideoViaFTP($ftp_server, $ftp_user, $ftp_pass, $cheminFichier, $nomFichier) {
    $fileUrl = "ftp://$ftp_user:$ftp_pass@$ftp_server/$cheminFichier/$nomFichier";
    $command = URI_FFMPEG." -i \"$fileUrl\" 2>&1";
    exec($command, $output);
    $meta = implode($output);
    return recupererMetadonnees($meta, $nomFichier);
}


/**
 * Fonction de récupération des métadonnées d'un $meta (bloc de métadonnées) via REGEX
 * #RISQUE : Changement des REGEX selon les vidéos
 * \fn recupererMetadonnees($meta, $fichier)
 * \brief Fonction de récupération des métadonnées d'un $meta (bloc de métadonnées) via REGEX
 * \param meta - bloc de métadonnées 
 * \param fichier - Nom du fichier
 * \return liste - Liste des métadonnées techniques de la vidéo
 */

 // #RISQUE : Changment des REGEX selon les vidéos
function recupererMetadonnees($meta, $fichier){
    preg_match("/'[^']*\/(.*)'/",$meta,$nom);
    preg_match("/(\d+(.\d+)?)(?= fps)/", $meta, $fps);
    preg_match("/(\d{2,4}x\d{2,4})/", $meta, $resolution);
    preg_match("/(?<=Duration: )(\d{2}:\d{2}:\d{2}.\d{2})/", $meta, $duree);
    preg_match("/(?<=DAR )([0-9]+:[0-9]+)/", $meta, $format);
    // #RISQUE : Attention aux duree des vidéos qui varient selon l'extension-  J'ai arrondi mais solution partiellement viable
    $dureeFormatee = preg_replace('/\.\d+/', '', $duree[1]); //Arrondir pour ne pas tenir compte des centièmes
    $liste = [MTD_TITRE => $fichier,
                MTD_FPS => $fps[0],
                MTD_RESOLUTION => $resolution[0],
                MTD_DUREE => $dureeFormatee,
                MTD_FORMAT => $format[1]
                ];
    return $liste;
}

/**
 * \fn recupererTailleFichier($video, $cheminFichier)
 * \brief Fonction qui récupère la taille d'un fichier vidéo via ffmpeg
 * \param video - Nom du fichier vidéo
 * \param cheminFichier - Chemin complet du fichier vidéo
 * \return string - Taille du fichier en Mo
 */
function recupererTailleFichier($video, $cheminFichier){
    return "XX Mb"; #RISQUE pas encore implémenté : Changement par une fonction qui récupère la taille du fichier
}


/**
 * \fn traiterVideo($titre, $duree)
 * \brief Fonction qui permet de découper et de convertir une vidéo située dans un espace local en plusieurs fragments
 * \param titre - nom de la vidéo 
 * \param duree - Duree de la vidéo
 * \return liste - Liste des métadonnées techniques de la vidéo
 */
function traiterVideo($titre, $duree) {
    $total = formaterDuree($duree);

    $chemin_dossier_decoupe = URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION . $titre . '_parts';
    creerDossier($chemin_dossier_decoupe, false);

    $chemin_dossier_conversion = URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION . $titre . '_parts';
    creerDossier($chemin_dossier_conversion, false);

    // Vérifier si la durée totale est inférieure à 100 secondes
    if ($total < 100) {
        // Si la vidéo fait moins de 100 secondes, on la place directement dans URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION
        creerDossier($chemin_dossier_decoupe, false);
        $output_path = $chemin_dossier_decoupe . '/' . $titre;
        rename(URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $titre, $output_path);
        convertirVideo($output_path, $chemin_dossier_conversion, $titre, 0, true);
        unlink($chemin_dossier_decoupe . "/" . $titre);
        rmdir($chemin_dossier_decoupe);
        rmdir($chemin_dossier_conversion);
    } else {
        $nombreParties = 100;
        $dureePartie = $total / $nombreParties;
        $PIDsEnfants = [];
        $partiesParProcessus = ceil($nombreParties / NB_MAX_SOUS_PROCESSUS_TRANSFERT);
        for ($i = 0; $i < NB_MAX_SOUS_PROCESSUS_TRANSFERT; $i++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                ajouterLog(LOG_CRITICAL, "Erreur critique sur le multiprocessing.");
                die('Duplication impossible');
            } elseif ($pid) {
                // Processus parent : on enregistre le PID du fils
                $PIDsEnfants[] = $pid;
            } else {
                // **PROCESSUS ENFANT**
                $debut = $i * $partiesParProcessus;
                $fin = min(($i + 1) * $partiesParProcessus, $nombreParties);

                for ($j = $debut; $j < $fin; $j++) {
                    $start_time = $j * $dureePartie;
                    $start_time_formatted = gmdate("H:i:s", intval($start_time)) . sprintf(".%03d", ($start_time - floor($start_time)) * 1000);
                    $current_part_duration = ($j == $nombreParties - 1) ? max(($total - $start_time), 0.01) : $dureePartie;

                    if (substr($titre, -1) == "4" ) {
                        $output_path = $chemin_dossier_decoupe . '/' . $titre . '_part_' . sprintf('%03d', $j + 1) . '.mp4';
                    } else {
                        $output_path = $chemin_dossier_decoupe . '/' . $titre . '_part_' . sprintf('%03d', $j + 1) . '.mxf';
                    }

                    $command = URI_FFMPEG." -i \"" . URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $titre . "\"" .
                            " -ss " . $start_time_formatted .
                            " -t " . $current_part_duration .
                            " -c copy \"" . $output_path . "\" -y";
                    exec($command, $output, $return_var);

                    if ($return_var == 1) {
                        ajouterLog(LOG_CRITICAL, "Erreur lors du découpage de la partie".($j + 1)."de la vidéo $titre.");
                    }

                    convertirVideo($output_path, $chemin_dossier_conversion, $titre, $j + 1, false);
                }
                exit(0);
            }
        }
        // **PROCESSUS PARENT**
        // Attendre que tous les processus fils se terminent
        while (count($PIDsEnfants) > 0) {
            $pidTermine = pcntl_waitpid(-1, $status);
            if ($pidTermine > 0) {
                $PIDsEnfants = array_diff($PIDsEnfants, [$pidTermine]);
            }
        }
        // **Nettoyage des fichiers temporaires**
        $files = scandir($chemin_dossier_decoupe);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                unlink($chemin_dossier_decoupe . "/" . $file);
            }
        }
        // Supprimer le fichier original + dossier des morceaux de vidéos à convertir
        unlink(URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $titre);
        rmdir($chemin_dossier_decoupe);
    }
}

/**
 * \fn convertirVideo($chemin_fichier_origine, $chemin_dossier_conversion, $titre, $i)
 * \brief Fonction qui permet de convertir une vidéo passée en paramètre
 * \param titre - nom de la vidéo 
 * \param duree - Duree de la vidéo
 * \return liste - Liste des métadonnées techniques de la vidéo
 */
function convertirVideo($chemin_fichier_origine, $chemin_dossier_conversion, $titre, $i, $partieUnique){
    if($partieUnique){
        $chemin_fichier_destination = $chemin_dossier_conversion . '/' . forcerExtensionMp4($titre);
    }
    else{
        $chemin_fichier_destination = $chemin_dossier_conversion. '/' . $titre . '_part_' . sprintf('%03d', $i) . '.mp4';
    }
    $command = URI_FFMPEG." -i \"$chemin_fichier_origine\" " .
                "-c:v libx264 -preset ultrafast -crf 35 " .  // CRF élevé pour réduire la qualité vidéo
                "-c:a aac -b:a 64k -ac 2 -threads 1 " .             // Bitrate audio réduit à 64 kbps, limité à 2 threads
                "-movflags +faststart " .                   // Optimisation pour le streaming
                "-vf format=yuv420p " .
                "\"$chemin_fichier_destination\"";
    //exec($command, $output, $return_var);
    exec($command . " 2>&1", $output, $return_var);
    if ($return_var == 1) {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la conversion de la partie ". $i ." de la vidéo " .
        $chemin_fichier_origine . " : " . implode("\n", $output));
        //exit();
    }
}

/**
 * \fn fusionnerVideo($video)
 * \brief Fonction qui permet de fusionner tous les morceaux d'une vidéo en un seul fichier
 * \param video - nom de la video 
 */
function fusionnerVideo($video){
    // Chemin pour accéder aux dossiers des vidéos
    $chemin_dossier_origine = URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION . $video . '_parts';
    $chemin_dossier_destination = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD;

    // On récupère toutes les morceaux de vidéos à convertir
    $files = scandir($chemin_dossier_origine);
    // On trie les fichier avec l'ordre naturel (ex:  vid_1, vid_10, vid_2 -> vid_1, vid_2, vid_10)
    natsort($files);
    // On met le nom de chaques vidéos dans un fichier txt pour ffmpeg
    $fileListContent = "";
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $fileListContent .= "file '" . $file . "'\n";
        }
    }
    // On donne le fichier txt à ffmpeg pour qu'il fusionne toutes les vidéos suivant l'ordre naturel, LE TXT N'EST PAS OPIONNEL
    $fileListPath = $chemin_dossier_origine . '/file_list.txt';
    file_put_contents($fileListPath, $fileListContent);
    $outputFile = $chemin_dossier_destination . "/" . $video;
    $command = URI_FFMPEG." -v verbose -f concat -safe 0 -i " . $fileListPath .
               " -c copy " . substr($outputFile, 0, -3) . "mp4";
    exec($command, $output, $returnVar);


    // On supprime le dossier qui contient les morceaux convertis
    $files = scandir($chemin_dossier_origine);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            unlink($chemin_dossier_origine . "/" . $file);
        }
    }
    rmdir($chemin_dossier_origine);
}

/**
 * \fn genererMiniature($video, $duree)
 * \brief Fonction qui créé une miniature dans un espace local.
 * \param video - nom de la video 
 * \param duree - duree de la vidéo
 * \return miniature - Miniature de la vidéo
 */
function genererMiniature($video, $duree){

    $total = formaterDuree($duree);

    $timecode = floor($total / 2);

    //Remplacer l'extension .mp4 par .png
    $cheminFichier = dirname($video) . '/';
    $nomFichier = basename($video);
    $nomFichier = recupererNomFichierSansExtension($nomFichier);
    $videoSansExtension = $cheminFichier.$nomFichier;
    $miniature = $videoSansExtension . SUFFIXE_MINIATURE_VIDEO;

    $command = URI_FFMPEG . " -i " . $video . 
               " -ss " . $timecode . 
               " -vframes 1 " . 
               " -vf scale=320:-1 " . // Réduction de la résolution
               " -pix_fmt rgb8 " . // Limite à 256 couleurs
               " -compression_level 9 " . // Compression maximale du PNG
               $miniature;
        
    exec($command, $output, $returnVar);
    ajouterLog(LOG_SUCCESS, "Miniature de la vidéo $video générée avec succès.");
    $miniature = basename($miniature);
    return $miniature;
}

/**
 * \fn formaterDuree($duree)
 * \brief Fonction qui permet de convertir une durée totale en secondes
 * \param duree - duree de la vidéo
 * \return total - Durée totale en secondes
 */
function formaterDuree($duree){
    $heures = (int)substr($duree, 0, 2);
    $minutes = (int)substr($duree, 3, 2);
    $secondes = (int)substr($duree, 6, 2);
    $milisecondes = (int)substr($duree, 9, 2);

    // Convertir la durée totale en secondes
    $total = $heures * 3600 + $minutes * 60 + $secondes + $milisecondes / 1000;
    return $total;
}
?>