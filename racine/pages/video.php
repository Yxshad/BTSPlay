<?php 
    session_start();
    require_once '../fonctions/controleur.php';
    $infosVideo = controleurRecupererInfosVideo();

    $idVideo = $infosVideo["idVideo"];
    $nomFichier = $infosVideo["nomFichier"];
    $cheminMiniature = $infosVideo["cheminMiniature"];
    $cheminDistantVideo = $infosVideo["cheminDistantVideo"];
    $titreVideo = $infosVideo["titreVideo"];
    $mtdTech = $infosVideo["mtdTech"];
    $mtdEdito = $infosVideo["mtdEdito"];
    $promotion = $infosVideo["promotion"];

    $cheminLocal = controleurTelechargerFichier($cheminDistantVideo, $nomFichier); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/logo_BTS_Play.png" type="image/png">
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
    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminMiniature; ?>>
        <source src="<?php echo $cheminLocal; ?>" type="video/mp4"/>
    </video>
</div>
    <h1 class="titre"><?php echo $nomFichier; ?></h1>
    <h2 ><?php echo $titreVideo; ?></h2>
    <div class="colonnes">
        <div class="colonne-1">
            <p class="description"><?php echo $mtdTech["Description"]; ?></p>
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
            <a href="<?php echo $cheminLocal; ?>" download="<?php echo $mtdTech["mtd_tech_titre"]; ?>" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/download.webp" alt="">
                </div>
                <p>Télécharger</p>
            </a>

            <div class="btnVideo">
                <form action="#" method="POST">
                    <input type="hidden" name="action" value="diffuserVideo">
                    <input type="hidden" name="cheminLocal" value="<?php echo $cheminLocal; ?>">
                    <button type="submit" class="boutonSubmit">
                        <div class="logo-btnvideo">
                            <img src="../ressources/Images/antenne.png" alt="">
                        </div>
                        <p>Diffuser</p>
                    </button>
                </form>
            </div>

            <a href="formulaireMetadonnees.php?v=<?php echo $idVideo; ?>" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/modif.png" alt="">
                </div>
                <p>Modifier</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/trash.png" alt="">
                </div>
                <p>Supprimer</p>
            </a>
        </div>
    </div>
</div>

<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        initLectureVideo();
    });
</script>
