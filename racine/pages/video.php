<?php 
    session_start();
    require_once '../fonctions/controleur.php';
    $infosVideo = controleurRecupererInfosVideo();

    $idVideo = $infosVideo["idVideo"];
    $nomFichier = $infosVideo["nomFichier"];
    $cheminMiniatureComplet = $infosVideo["cheminMiniatureComplet"];
    $cheminVideoComplet = $infosVideo["cheminVideoComplet"];
    $titreVideo = $infosVideo["titreVideo"];
    $mtdTech = $infosVideo["mtdTech"];
    $mtdEdito = $infosVideo["mtdEdito"];
    $promotion = $infosVideo["promotion"];
    $URIS = $infosVideo["URIS"];

    $cheminCompletNAS_PAD = null;
    if(!empty($URIS['URI_NAS_PAD'])){
        $cheminCompletNAS_PAD = $URIS['URI_NAS_PAD'].$nomFichier;
    }

    chargerPopup();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>
    
    <!-- <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" /> -->

    <!-- #RISQUE : Liens CDN utilisés dans la lib plyr.js -->
    <script src="../ressources/lib/Plyr/plyr.js"></script>
    <link rel="stylesheet" href="../ressources/lib/Plyr/plyr.css" />

<?php require_once '../ressources/Templates/header.php';?>

<div class="container">
    <div class="lecteurVideo">
    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminMiniatureComplet; ?>>
        <source src="<?php echo $cheminVideoComplet; ?>" type="video/mp4"/>
    </video>
</div>
    <h1 class="titre"><?php echo $nomFichier; ?></h1>
    <h2 ><?php echo $titreVideo; ?></h2>
    <div class="colonnes">
        <div class="colonne-1">
            <p class="description"><?php echo $mtdTech["Description"]; ?></p>
            <p class="mtd">
                <strong>URI du NAS PAD : </strong><?php echo $URIS['URI_NAS_PAD']; ?>
            </p>
            <p class="mtd">
                <strong>URI du NAS ARCH : </strong><?php echo $URIS['URI_NAS_ARCH']; ?>
            </p>
            <p class="mtd">
                <strong>Durée : </strong><?php echo $mtdTech["mtd_tech_duree"]; ?>
            </p>
            <p class="mtd">
                <strong>Image par secondes : </strong><?php echo $mtdTech["mtd_tech_fps"]; ?> fps
            </p>
            <p class="mtd">
                <strong>Résolution : </strong><?php echo $mtdTech["mtd_tech_resolution"]; ?>
            </p>
            <p class="mtd">
                <strong>Format : </strong><?php echo $mtdTech["mtd_tech_format"]; ?>
            </p>
            <p class="mtd">
                <strong>Projet : </strong><?php echo $mtdEdito["projet"]; ?>
            </p>
            <p class="mtd">
                <strong>Promotion : </strong><?php echo $promotion; ?>
            </p>
            <p class="mtd">
                <strong>Professeur : </strong><?php echo $mtdEdito["professeur"]; ?>
            </p>
            <p class="mtd">
                <strong>Réalisateur : </strong><?php echo $mtdEdito["realisateur"]; ?>
            </p>
            <p class="mtd">
                <strong>Cadreur : </strong><?php echo $mtdEdito["cadreur"]; ?>
            </p>
            <p class="mtd">
                <strong>Responsable Son : </strong><?php echo $mtdEdito["responsableSon"]; ?>
            </p>
            
        </div>
        <div class="colonne-2">
            <!-- #RISQUE : Télécharger le fichier distant du NAS ARCH, il faudra créer un dossier local 'videosATelecharger' -->
            <a href="<?php echo $cheminVideoComplet; ?>" download="<?php echo $mtdTech["mtd_tech_titre"]; ?>" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/download.webp" alt="">
                </div>
                <p>Télécharger</p>
            </a>
            <?php if(controleurVerifierAcces(ACCES_DIFFUSION)){ ?>
                <?php if(!empty($cheminCompletNAS_PAD)){ ?>
                <div class="btnVideo">
                    <button onclick="  changerTitrePopup('Diffusion'); 
                                    changerTextePopup('Voulez-vous vraiment diffuser la vidéo <?php echo $nomFichier; ?>');
                                    changerTexteBtn('Confirmer', 'btn1');
                                    changerTexteBtn('Annuler', 'btn2');
                                    attribuerFonctionBtn('lancerDiffusion','<?php echo $cheminCompletNAS_PAD; ?>', 'btn1');
                                    attribuerFonctionBtn('','', 'btn2');
                                    afficherBtn('btn2');
                                    cacherBtn('btn3');
                                    cacherBtn('btn4');
                                    afficherPopup();">
                        <div class="logo-btnvideo">
                            <img src="../ressources/Images/antenne.png" alt="">
                        </div>
                        <p>Diffuser</p>
                    </button>
                </div>
                <?php }
            }
            if(controleurVerifierAcces(ACCES_MODIFICATION)){ ?>
                <a href="formulaireMetadonnees.php?v=<?php echo $idVideo; ?>" class="btnVideo">
                    <div class="logo-btnvideo">
                        <img src="../ressources/Images/modif.png" alt="">
                    </div>
                    <p>Modifier</p>
                </a>
            <?php }
            if(controleurVerifierAcces(ACCES_SUPPRESSION)){ ?>             
                <div class="btnVideo">
                    <button class="boutonSubmit" onclick="  changerTitrePopup('Suppression'); 
                                                            changerTextePopup('Voulez-vous vraiment supprimer la vidéo <?php echo $nomFichier; ?>');
                                                            changerTexteBtn('Base de données', 'btn1');
                                                            changerTexteBtn('NAS PAD', 'btn2');
                                                            changerTexteBtn('NAS Archive', 'btn3');
                                                            changerTexteBtn('Annuler', 'btn4');
                                                            attribuerFonctionBtn('supprimerVideo','<?php echo $idVideo; ?>, local', 'btn1');
                                                            attribuerFonctionBtn('supprimerVideo','<?php echo $idVideo; ?>, PAD', 'btn2');
                                                            attribuerFonctionBtn('supprimerVideo','<?php echo $idVideo; ?>, ARCH', 'btn3');
                                                            attribuerFonctionBtn('', '', 'btn4');
                                                            afficherBtn('btn2');
                                                            afficherBtn('btn3');
                                                            afficherBtn('btn4');
                                                            afficherPopup();">
                        <div class="logo-btnvideo">
                            <img src="../ressources/Images/trash.png" alt="">
                        </div>
                        <p>Supprimer</p>
                    </button>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        initLectureVideo();
    });
</script>
