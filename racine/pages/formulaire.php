<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/formulaire.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php
    require_once '../ressources/Templates/header.php';
    require_once '../fonctions/fonctions.php';
    require_once '../fonctions/ftp.php';
    require_once '../ressources/constantes.php';
    require_once '../fonctions/modele.php';

    // Récupération de l'URI NAS de la vidéo
    if (isset($_GET['v'])) {
        $id = $_GET['v'];
    }
    $video = fetchAll("SELECT * FROM Media WHERE id=$id;");
    $video = $video[0];
    $titre = substr($video["mtd_tech_titre"], 0, -4);

    // Charge la miniature
    $miniature = $titre . "_miniature.png";
    $cheminMiniature = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $miniature;
?>

<div class="content">
    <h1 class="text-center mb-4">Formulaire des métadonnées</h1>

    <div class="row mb-4">
        <div class="col-md-6 text-center">
            <div class="mb-3">
                <img src="<?php echo $cheminMiniature; ?>" alt="Miniature de la vidéo" class="imageMiniature">
            </div>
            <h2><?php echo $titre; ?></h2>
            <p><strong>Durée :</strong> <?php echo $video['mtd_tech_duree']; ?></p>
            <p><strong>Images par secondes :</strong> <?php echo $video['mtd_tech_fps']; ?></p>
            <p><strong>Résolution :</strong> <?php echo $video['mtd_tech_resolution']; ?></p>
            <p><strong>Format :</strong> <?php echo $video['mtd_tech_format']; ?></p>
        </div>

        <div class="col-md-6">
            <h2>Équipe</h2>
            <form>
                <div class="mb-3">
                    <label for="realisateur" class="form-label">Réalisateur</label>
                    <input type="text" class="form-control" id="realisateur">
                </div>
                <div class="mb-3">
                    <label for="cadreur" class="form-label">Cadreur</label>
                    <input type="text" class="form-control" id="cadreur">
                </div>
                <div class="mb-3">
                    <label for="acteur1" class="form-label">Acteur</label>
                    <input type="text" class="form-control" id="acteur1">
                </div>
                <div class="mb-3">
                    <label for="acteur2" class="form-label">Acteur 2</label>
                    <input type="text" class="form-control" id="acteur2">
                </div>
                <div class="mb-3">
                    <label for="promotion" class="form-label">Promotion</label>
                    <input type="text" class="form-control" id="promotion">
                </div>
                <div class="mb-3">
                    <label for="projet" class="form-label">Projet</label>
                    <input type="text" class="form-control" id="projet">
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-center gap-3">
        <a href="video.php?v=<?php echo $id;?>" class="btn btn-secondary">Annuler</a>
        <a href="#" class="btn btn-primary">Confirmer</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
