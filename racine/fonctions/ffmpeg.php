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
                MTD_DUREE_REELLE => $duree[1],
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
function traiterVideo($cheminDossierAttenteConversion, $cheminDossierCoursConversion, $nomFichier, $duree){

    $total = formaterDuree($duree);
    $nomFichierSortie = forcerExtensionMp4($nomFichier); //Vidéo de sortie (compressée) forcée à l'extension mp4

    // Vérifier si la durée totale est inférieure à 100 secondes
    if ($total < 100) {
        // Si la vidéo fait moins de 100 secondes, on la place directement dans URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION
        //$output_path = $chemin_dossier_conversion . '/' . forcerExtensionMp4($titre);

        $command = URI_FFMPEG." -i " . $cheminDossierAttenteConversion . $nomFichier .
                " -c:v libx264 -preset ultrafast -crf 35 " .  // CRF élevé pour réduire la qualité vidéo
                "-c:a aac -b:a 64k -ac 2 -threads " . NB_MAX_SOUS_PROCESSUS_TRANSFERT .            // Bitrate audio réduit à 64 kbps, limité à 2 threads
                " -movflags +faststart " .                   // Optimisation pour le streaming
                "-vf format=yuv420p " .
                $cheminDossierCoursConversion . $nomFichierSortie;

        //exec($command, $output, $return_var);
        exec($command . " 2>&1", $output, $return_var);
        if ($return_var == 1) {
            ajouterLog(LOG_CRITICAL, "Erreur lors de la conversion de la partie unique de la vidéo " .
            $cheminDossierAttenteConversion . $nomFichier . " : " . implode("\n", $output));
            //exit();
        }
        else{
            // on ne supprime la vidéo de base que quand la vidéo a bien été compressée
            unlink($cheminDossierAttenteConversion . $nomFichier);
        }
    }
    else {
        //Pour une vidéo longue, supérieure à 100 secondes
        $segmentDuration = $total / 100;

        $extension = (substr($titre, -4) === ".mp4") ? ".mp4" : ".mxf";

        // 3. Générer les points de coupure
        $cutPoints = '';
        for ($i = 0; $i < 100; $i++) {
            $cutPoints .= ($i * $segmentDuration) . ',';
        }
        $cutPoints = rtrim($cutPoints, ',');

        // 4. Découper la vidéo en segments
        $decoupeCommand = URI_FFMPEG . " -i " . $cheminDossierAttenteConversion . $nomFichier .
                      " -threads " . NB_MAX_SOUS_PROCESSUS_TRANSFERT .
                      " -f segment" .
                      " -segment_times $cutPoints" .
                      " -reset_timestamps 1" .
                      " -segment_format mp4" .
                      " -movflags +faststart" .
                      " -c:v libx264 -pix_fmt yuv420p -crf 24 -preset ultrafast" .
                      " -vf yadif" .
                      " -c:a aac -b:a 128k -ac 2 " .
                      " -map 0:v:0 -map 0:a:0" .
                      $cheminDossierCoursConversion . $nomFichierSortie;
    
        // Exécuter la commande de découpage
        exec($decoupeCommand, $output, $return_var);
        if ($return_var == 1) {
            ajouterLog(LOG_CRITICAL, "Erreur lors de la conversion de la partie ". $i ." de la vidéo " .
            $chemin_fichier_origine . " : " . implode("\n", $output));
            //exit();
        }else{
            // on ne supprime la vidéo de base que quand la vidéo a bien été compressée
            unlink($cheminDossierAttenteConversion . $nomFichier);
        }  
    }
}

/**
 * \fn fusionnerVideo($video)
 * \brief Fonction qui permet de fusionner tous les morceaux d'une vidéo en un seul fichier
 * \param video - nom de la video
 */
function fusionnerVideo($cheminDossierCoursConversion, $cheminDossierAttenteUpload, $nomFichier){

    $nomFichierSortie = forcerExtensionMp4($nomFichier);

    // On récupère toutes les morceaux de vidéos à convertir
    $files = scandir($cheminDossierCoursConversion);
    natsort($files);
    // On met le nom de chaques vidéos dans un fichier txt pour ffmpeg
    $fileListContent = "";
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $fileListContent .= "file '" . $file . "'\n";
        }
    }
    // On donne le fichier txt à ffmpeg pour qu'il fusionne toutes les vidéos suivant l'ordre naturel, LE TXT N'EST PAS OPIONNEL
    $fileListPath = $cheminDossierCoursConversion . '/file_list.txt';
    file_put_contents($fileListPath, $fileListContent);
    $outputFile = $cheminDossierAttenteUpload . "/" . $nomFichierSortie;
    $command = URI_FFMPEG." -v verbose -f concat -safe 0 -i " . $fileListPath .
           " -c:v libx264 -preset ultrafast -crf 35 -c:a aac -b:a 64k -async 1 -fflags +genpts " .
           $outputFile;
    exec($command, $output, $returnVar);
    if ($return_var != 1) {
        // On supprime le dossier qui contient les morceaux convertis
        $files = scandir($cheminDossierCoursConversion);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                unlink($cheminDossierCoursConversion . "/" . $file);
            }
        }
        rmdir($cheminDossierCoursConversion);
        return 1;
    }else{
        return 0;
    }
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
    $centisecondes = (int)substr($duree, 9, 2);

    // Convertir la durée totale en secondes
    $total = $heures * 3600 + $minutes * 60 + $secondes + ($centisecondes / 10);
    return $total;
}
?>