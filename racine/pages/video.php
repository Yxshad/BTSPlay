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

    <div class="contenu">
        <div class="container_principal">
            <div class="container_video">
                <div class="lecteurVideo">
                    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminMiniatureComplet; ?>>
                        <source src="<?php echo $cheminVideoComplet; ?>" type="video/mp4"/>
                    </video>
                </div>
            </div>
            <div class="info_video">

                <div class ="titre_nom">
                    <h1 class="titre"><?php echo $nomFichier; ?></h1>
                    <h2 ><?php echo $titreVideo; ?></h2>
                </div>

                <div class="container-button">
                    <!-- Bouton Télécharger -->
                    <button title="Télécharger vidéo" class="btnVideo" onclick="window.location.href='<?php echo $cheminVideoComplet; ?>';">
                        <div class="logo-btnvideo">
                            <img src="../ressources/Images/télécharger_image_blanc.png" alt="">
                        </div>
                    </button>

                    <?php if(controleurVerifierAcces(ACCES_DIFFUSION)){ ?>
                        <?php if(!empty($cheminCompletNAS_PAD)){ ?>
                            <button class="btnVideo" onclick="afficherPopUp('Diffusion', 'Voulez-vous vraiment diffuser la vidéo <?php echo htmlspecialchars($nomFichier); ?> ?', {libelle : 'Oui!', arguments : [['action','diffuserVideo'], ['URI_COMPLET_NAS_PAD', '<?php echo htmlspecialchars($cheminCompletNAS_PAD); ?>']]}, {libelle : 'Non!', arguments : []})"></button>
                        <?php }
                    }
                    if(controleurVerifierAcces(ACCES_MODIFICATION)){ ?>
                        <button id="boutonModif" title="Modifier vidéo" class="btnVideo" onclick="window.location.href='formulaireMetadonnees.php?v=<?php echo $idVideo; ?>';">
                            <div class="logo-btnvideo">
                                <img src="../ressources/Images/modifier_video_blanc.png" alt="">
                            </div>
                        </button>
                    <?php }
                    if(controleurVerifierAcces(ACCES_SUPPRESSION)){ ?>             
                        <button title="Supprimer vidéo" class="btnVideo" id="btnSuppr" onclick="afficherPopUp('Suppression', 'Voulez-vous vraiment Supprimer la vidéo <?php echo htmlspecialchars($nomFichier); ?> ?', {libelle : 'Oui!', arguments : [['action','supprimerVideo'], ['idVideo', '<?php echo htmlspecialchars($idVideo); ?>'], ['URI_STOCKAGE_LOCAL', '<?php echo $cheminVideoComplet; ?>']]}, {libelle : 'Non!', arguments : []})">
                            <p>
                                supprimer
                            </p>
                        </button>
                    <?php } ?>
                </div>

            </div>
        </div>

        <div class="metadata_detaillee">
            <p class="description"><strong>Description : </strong><?php echo $mtdTech["Description"]; ?></p>
            <div class="metadata">
                <div class="colonne">
                    <p class="mtd"><strong>URI du NAS PAD : </strong><?php echo $URIS['URI_NAS_PAD']; ?></p>
                    <p class="mtd"><strong>URI du NAS ARCH : </strong><?php echo $URIS['URI_NAS_ARCH']; ?></p>
                    <p class="mtd"><strong>Durée : </strong><?php echo $mtdTech["mtd_tech_duree"]; ?></p>
                    <p class="mtd"><strong>Image par seconde : </strong><?php echo $mtdTech["mtd_tech_fps"]; ?> fps</p>
                    <p class="mtd"><strong>Résolution : </strong><?php echo $mtdTech["mtd_tech_resolution"]; ?></p>
                    <p class="mtd"><strong>Format : </strong><?php echo $mtdTech["mtd_tech_format"]; ?></p>
                </div>
                <div class="colonne">
                    <p class="mtd"><strong>Projet : </strong><?php echo $mtdEdito["projet"]; ?></p>
                    <p class="mtd"><strong>Promotion : </strong><?php echo $promotion; ?></p>
                    <p class="mtd"><strong>Professeur : </strong><?php echo $mtdEdito["professeur"]; ?></p>
                    <p class="mtd"><strong>Réalisateur : </strong><?php echo $mtdEdito["realisateur"]; ?></p>
                    <p class="mtd"><strong>Cadreur : </strong><?php echo $mtdEdito["cadreur"]; ?></p>
                    <p class="mtd"><strong>Responsable Son : </strong><?php echo $mtdEdito["responsableSon"]; ?></p>
                </div>
            </div>
        </div>
    </div>


<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        initLectureVideo();
    });
</script>
