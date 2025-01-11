<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
    
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

<?php
    require_once '../ressources/Templates/header.php';
    require_once '../fonctions/fonctions.php';
    require_once '../fonctions/ftp.php';
    require_once '../ressources/constantes.php';
    require_once '../fonctions/ffmpeg.php';
    require_once '../fonctions/modele.php';

    //Récupération de l'URI NAS de la vidéo
    if (isset($_GET['v'])) {
        $id = $_GET['v'];
    }
    $video = fetchAll("SELECT * FROM Media WHERE id=$id;");
    $video = $video[0];
    $titre = substr($video["mtd_tech_titre"], 0, -4);
    
    //charge la minitature
    $miniature = $titre . "_miniature.png";
    $cheminMiniature = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $miniature;

    //prépare la video
    $cheminLocal = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $video["mtd_tech_titre"];
    $cheminDistant = URI_RACINE_NAS_MPEG . $video["URI_NAS_MPEG"] . $video["mtd_tech_titre"]; 
    $conn_id = connexionFTP_NAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
    telechargerFichier($conn_id, $cheminLocal, $cheminDistant);
    ftp_close($conn_id);

?>
<div class="container">
    <div class="lecteurVideo">
    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminMiniature; ?>>
        <source src="<?php echo $cheminLocal; ?>" type="video/mp4"/>
    </video>
</div>
    <h1 class="titre"><?php echo $titre; ?></h1>
    <div class="colonnes">
        <div class="colonne-1">
            <p class="description"><?php echo $video["Description"]; ?></p>
            <p class="meta"><?php echo $video["mtd_tech_fps"]; ?> fps, <?php echo $video["mtd_tech_resolution"]; ?>, <?php echo $video["mtd_tech_format"]; ?>, <?php echo $video["mtd_tech_duree"]; ?></p>
           
        </div>
        <div class="colonne-2">
            <a href="./bamboulo.mp4" download="bamboulo.mp4" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/download.webp" alt="">
                </div>
                <p>Télécharger</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/antenne.png" alt="">
                </div>
                <p>Diffuser</p>
            </a>
            <a href="formulaire.php?v=<?php echo $id;?>" class="btnVideo">
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

<footer>
<?php require '../ressources/Templates/footer.php';?>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        initLectureVideo();
    });
</script>
