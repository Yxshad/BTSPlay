<?php session_start();?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/logo_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
    
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

	
<?php require_once '../ressources/Templates/header.php';?>

<h1> Fonction de réconciliation </h1>

<!-- Formulaire pour choisir les NAS -->
<form method="post">
	<input type="hidden" value="declencherReconciliation">
    <button type="submit" name="declencherReconciliation">Réconciliation</button>
	<br> <br>
</form>

<?php require_once '../ressources/Templates/footer.php';?>



<?php
    require_once '../fonctions/fonctions.php';
    require_once '../fonctions/ftp.php';
    require_once '../ressources/constantes.php';
    require_once '../fonctions/ffmpeg.php';

if (isset($_POST['declencherReconciliation'])) {
    fonctionReconciliationAffichee();
}

function fonctionReconciliationAffichee() {
    // Algorithme qui vérifie la présence des vidéos dans les 2 NAS.
    // Si une vidéo n'est pas présente dans les 2 NAS, une alerte est lancée

    $listeVideos_NAS_1 = [];
    $listeVideos_NAS_2 = [];
    $listeVideos_NAS_1 = recupererNomsVideosNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_RACINE_NAS_PAD, $listeVideos_NAS_1);
    $listeVideos_NAS_2 = recupererNomsVideosNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_RACINE_NAS_ARCH, $listeVideos_NAS_2);

    echo "<h2>Vidéos présentes sur " .NAS_PAD.": </h2>";
    echo "<pre>" . print_r($listeVideos_NAS_1, true) . "</pre>";

    echo "<h2>Vidéos présentes sur " .NAS_ARCH.": </h2>";
    echo "<pre>" . print_r($listeVideos_NAS_2, true) . "</pre>";

    $listeVideosManquantes = [];
    $listeVideosManquantes = trouverVideosManquantes(NAS_PAD, NAS_ARCH, $listeVideos_NAS_1, $listeVideos_NAS_2, $listeVideosManquantes);

    afficherVideosManquantes($listeVideosManquantes);

    ajouterLog(LOG_SUCCESS, "Fonction de réconciliation effectuée avec succès.");
}
?>