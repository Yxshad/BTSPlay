<?php 
	session_start(); 
	require_once '../fonctions/controleur.php';
    controleurVerifierAcces(AUTORISATION_ADMIN);
    $tabDernieresVideos = controleurRecupererDernieresVideosTransfereesSansMetadonnees();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/transfert.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

<?php require_once '../ressources/Templates/header.php'; ?>

<div class="container">
    <div class="colonnes">
        <div class="colonne-1">
            <h1>Transferts</h1>
            <div class="transferts">
                <div class="lignes">
                    <!-- Résultat ajax -->
                </div>
                <div class="commande">
                    <p>Commande de conversion</p>
                    <input type="text" placeholder="ffmpeg -i $video 2>&1">
                    <a class="btn" onclick="lancerConversion()">Lancer conversion</a>
                </div>
                
            </div>
        </div>
        <div class="symbole">
            >
        </div>
        <div class="colonne-2">
            <h2>Vidéos en attente de métadonnées</h2>
            <div class="dates">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Fichier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tabDernieresVideos as $video) {
                        $id = $video['id'];
                        $date_creation = $video['date_creation'];
                        $mtd_tech_titre = $video['mtd_tech_titre'];
                        ?>
                        <tr>
                            <td><a href="video.php?v=<?php echo $id; ?>"><?php echo $date_creation; ?></a></td>
                            <td><a href="video.php?v=<?php echo $id; ?>"><?php echo $mtd_tech_titre; ?></a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
                
<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        appelScanVideo();
    });
</script>