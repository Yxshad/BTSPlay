<?php
require_once '../fonctions/controleur.php';

$annee = isset($_POST['annee']) ? intval($_POST['annee']) : null;
$niveau = isset($_POST['niveau']) ? intval($_POST['niveau']) : null;
$prof = isset($_POST['prof']) ? $_POST['prof'] : null;

$tabVideos = controleurRecupererTitreIdVideoFiltre($annee, $niveau, $prof); // Nouvelle fonction filtrée


<div class="container">
    <?php for ($i=0; $i < 5; $i++) { ?>
        <a href="video.php" class="video">
            <div class="miniature"></div>
            <div class="description">
                <h2 class="titre">Video <?php echo $i; ?></h2>
                <p class="description"> Métadonnées : Lorem Ipsum</p>
            </div>
        </a>
    <?php } ?>
</div>

<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        affichageFiltres();
    });
</script>

