<?php

require_once "../ressources/constantes.php";
require_once "../fonctions/ftp.php";
require_once "../fonctions/ffmpeg.php";
require_once "../fonctions/modele.php";
require_once "../fonctions/fonctions.php";
/*
echo "<h1>TEST MULTITHREADING </h1>";

if(!(function_exists("pcntl_fork"))){
    echo "<h2>pcntl_fork n'existe pas </h2>";
}

if(!(function_exists("pcntl_waitpid"))){
    echo "<h2>pcntl_waitpid n'existe pas </h2>";
}

$pid = pcntl_fork();
if ($pid == -1) {
    echo "ERREUR INCONNUE SUR LE FORK";
    die('Duplication impossible');
} elseif ($pid > 0) { // Processus parent
    ajouterLog(LOG_INFORM, getmypid() . " : Je suis le parent, mon fils a le PID $pid");
} else { // Processus enfant
    ajouterLog(LOG_INFORM, "Je suis l'enfant, mon PID est " . getmypid());
    sleep(1); // Simulation de travail
    exit(0);
}

ajouterLog(LOG_INFORM, getmypid() . " : J'attends mon fils $pid");

// Attendre que le processus enfant se termine
$pidTermine = pcntl_waitpid($pid, $status);

if ($pidTermine > 0) { // Un processus enfant s'est terminé
    if (pcntl_wifexited($status)) {
        ajouterLog(LOG_SUCCESS, getmypid() . " : le fils $pidTermine est MORT HAHHAHAH");
    } else {
        ajouterLog(LOG_WARN, getmypid() . " : le fils $pidTermine ne s'est pas terminé proprement.");
    }
}

exit(0);


*/
ajouterLog(LOG_INFORM, "OUVERTURE");
    if (isset($argv[1])) {
        $COLLECT_STOCK_LOCAL = json_decode($argv[1], true);
    }
    
    ajouterLog(LOG_INFORM, print_r($COLLECT_STOCK_LOCAL, true));

    $tailleDuTableau = count($COLLECT_STOCK_LOCAL);
    $elementsParProcessus = ceil($tailleDuTableau / NB_MAX_PROCESSUS_TRANSFERT);

    $PIDsEnfants = [];

    for ($i = 0; $i < NB_MAX_PROCESSUS_TRANSFERT; $i++) {
        //usleep(500000);
        $pid = pcntl_fork();

        if ($pid == -1) {
            ajouterLog(LOG_CRITICAL, "Erreur critique sur le multithreading.");
            die('Duplication impossible');
        } elseif ($pid) {
            // Processus parent : on enregistre le PID du fils
            ajouterLog(LOG_INFORM, "Processus parent - Fils lancé avec PID : $pid");
            $PIDsEnfants[] = $pid;
        } else {
            // **PROCESSUS ENFANT**
            $debut = $i * $elementsParProcessus;
            $fin = min(($i + 1) * $elementsParProcessus, $tailleDuTableau);

            for ($j = $debut; $j < $fin; $j++) {
                usleep(50000);
                $video = $COLLECT_STOCK_LOCAL[$j];
                ajouterLog(LOG_INFORM, "Le fils PID " . getmypid() . " travaille sur la vidéo : " . $video[MTD_TITRE]);

                // **Téléchargement**
                $cheminFichierDestination = URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . $video[MTD_TITRE];

                if (!empty($video[MTD_URI_NAS_ARCH])) {
                    $conn_id = connexionFTP_NAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH);
                    $cheminFichierSource = $video[MTD_URI_NAS_ARCH] . $video[MTD_TITRE];
                } elseif (!empty($video[MTD_URI_NAS_PAD])) {
                    $conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD);
                    $cheminFichierSource = $video[MTD_URI_NAS_PAD] . $video[MTD_TITRE];
                } else {
                    ajouterLog(LOG_FAIL, "Erreur, la vidéo " . $video[MTD_TITRE] . " n'est présente dans aucun NAS.");
                    exit(1);
                }

                decouperVideo($video[MTD_TITRE], $video[MTD_DUREE]);
            convertirVideo($video[MTD_TITRE]);
            fusionnerVideo($video[MTD_TITRE]);

            // Forcer l'extension à .mp4
            $video[MTD_TITRE] = forcerExtensionMp4($video[MTD_TITRE]);

            $cheminCompletFichierSource = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $video[MTD_TITRE];
            $cheminFichierDestination = URI_RACINE_STOCKAGE_LOCAL . $URI_NAS;

            //Créer le dossier dans le NAS si celui-ci n'existe pas déjà.
            $nomFichierSansExtension = recupererNomFichierSansExtension($video[MTD_TITRE]);
            $dossierVideo = $cheminFichierDestination . PREFIXE_DOSSIER_VIDEO . $nomFichierSansExtension . '/';
            creerDossier($cheminFichierDestination, false);
            creerDossier($dossierVideo, false);

            // #RISQUE : S'assurer de l'export des fichiers par le booléen renvoyé par exporterFichierVersNAS()

            //Export de la vidéo dans le stockage local
            $cheminCompletDestination = $dossierVideo . $video[MTD_TITRE];
            $cheminCompletOrigine = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $video[MTD_TITRE];
            copy($cheminCompletOrigine, $cheminCompletDestination);

            //Générer la miniature de la vidéo
            $miniature = genererMiniature($cheminCompletFichierSource, $video[MTD_DUREE]);

            $cheminCompletDestination = $dossierVideo . $miniature;
            $cheminCompletOrigine = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $miniature;
            copy($cheminCompletOrigine, $cheminCompletDestination);

            //Supprimer la vidéo de l'espace local et sa miniature
            unlink($cheminCompletFichierSource);
            unlink(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD.$miniature);

            //Ajouter l'URI du stockage local à $video dans COLLECT_STOCK_LOCAL
            //On retire la racine du stockage local
            if (strpos($dossierVideo, URI_RACINE_STOCKAGE_LOCAL) == 0) {
                $dossierVideo = substr($dossierVideo, strlen(URI_RACINE_STOCKAGE_LOCAL));
            }
            $video[MTD_URI_STOCKAGE_LOCAL] = $dossierVideo;

                ajouterLog(LOG_INFORM, "Le fils PID " . getmypid() . " a terminé la vidéo : " . $video[MTD_TITRE]);
            }

            ajouterLog(LOG_INFORM, "Le fils PID " . getmypid() . " termine.");
            //usleep(50000);
            exit(0);
        }
    }

    // **Attente de la fin de tous les processus enfants**
    ajouterLog(LOG_CRITICAL, "PARTIE PERE");
    ajouterLog(LOG_CRITICAL, print_r($PIDsEnfants, true));
    //usleep(5000000);
    while (count($PIDsEnfants) > 0) {
        ajouterLog(LOG_CRITICAL, count($PIDsEnfants));
        $pidTermine = pcntl_waitpid(-1, $status);
        ajouterLog(LOG_CRITICAL, $pidTermine);
        if ($pidTermine > 0) {
            // Supprime le PID terminé du tableau
            $PIDsEnfants = array_diff($PIDsEnfants, [$pidTermine]);
            ajouterLog(LOG_INFORM, "Père : Processus fils PID $pidTermine terminé.");
        }
        //usleep(50000); // Petite pause pour éviter une surcharge CPU
        ajouterLog(LOG_CRITICAL, print_r($PIDsEnfants, true));
    }
    ajouterLog(LOG_INFORM, "Tous les processus fils ont terminé.");
    //return $COLLECT_STOCK_LOCAL;
    ajouterLog(LOG_CRITICAL, print_r($COLLECT_STOCK_LOCAL, true));
    echo json_encode($COLLECT_STOCK_LOCAL);

?>