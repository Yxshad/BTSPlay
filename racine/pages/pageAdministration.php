<?php 
session_start();
require_once '../fonctions/controleur.php';
controleurVerifierAccesPage(ACCES_ADMINISTRATION);

$listeProfesseurs = controleurRecupererAutorisationsProfesseurs();
$tabDernieresVideos = controleurRecupererDernieresVideosTransfereesSansMetadonnees();
// Appel des logs 
$logFile = '../ressources/historique.log'; // Chemin du fichier log
$maxLines = NB_LIGNES_LOGS; // Nombre maximum de lignes à afficher dans les logs
$logs = controleurAfficherLogs($logFile, $maxLines);
$logs = array_reverse($logs); // Pour afficher les logs les plus récentes aux plus vieilles
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/pageAdministration.css" rel="stylesheet">
    <link href="../ressources/Style/transfert.css" rel="stylesheet">
    <link href="../ressources/Style/sauvegarde.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link href="../ressources/lib/Swiper/swiper-bundle.min.css" rel="stylesheet">
    <script src="../ressources/lib/Swiper/swiper-bundle.min.js"></script>

<?php require_once '../ressources/Templates/header.php'; ?>

<body>
    <div><h1>Administration du Site</h1></div>
    <div class="tabs">
        <div class="tab" data-tab="database">Base de données</div>
        <div class="tab" data-tab="reconciliation">Réconciliation</div>
        <div class="tab" data-tab="transfer">Fonction de transfert</div>
        <div class="tab" data-tab="settings">Paramétrage du site</div>
        <div class="tab" data-tab="logs">Consulter les logs</div>
        <?php //On cache la page des autorisation si on est pas admin
            if($_SESSION["role"] == ROLE_ADMINISTRATEUR){ ?>
                <div class="tab" data-tab="users">Gérer les utilisateurs</div>
        <?php } ?>
    </div>
    
    <div class="tab-content" id="database">
        <h2>Sauvegarde de la base de données</h2>
        <div class="colonne-1">
            <div class="intervalSauvegarde">
                <p>Sauvegarder toutes les </p>
                <input type="number" name="" id="">
            </div>
            <div class="options">
                <input type="radio" name="drone" id=""> Jours
            </div>
            <div class="options">
                <input type="radio" name="drone" id=""> Mois
            </div>
            <div class="options">
                <input type="radio" name="drone" id=""> Années
            </div>

            <div class="dateSauvegarde">
                <p>à partir du : </p>
                <input type="date" name="" id="">
            </div>

            <a href="#" class="btn parametre">Enregistrer les paramètres</a>
            <a onClick="createDatabaseSave()" class="btn manuelle">Réaliser une sauvegarde manuelle</a>

        </div>
    </div>

    <div class="tab-content" id="reconciliation">
        <h2>Fonction de réconciliation</h2>
        <form method="post">
            <input type="hidden" name="action" value="declencherReconciliation">
            <button type="submit">Réconciliation</button>
        </form>
        <?php
        // Affichage du résultat de la réconciliation après redirection
        if (isset($_SESSION['reconciliation_result'])) {
            echo $_SESSION['reconciliation_result'];
            unset($_SESSION['reconciliation_result']); // Nettoyer après affichage
        }
        ?>
    </div>

    <div class="tab-content" id="transfer">
        <h2>Fonction de transfert</h2>
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
    </div>

    <div class="tab-content" id="settings">
        <h2>Paramétrage du site</h2>
        <p>Configuration et personnalisation...</p>
    </div>

    <div class="tab-content" id="logs">
        <h2>Consulter les logs</h2>
        <div class="log-container">
            <?php foreach ($logs as $line): ?>
                <div class="log-line"><?php echo htmlspecialchars($line); ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php //On cache le contenu de la page si on est pas admin
    if($_SESSION["role"] == ROLE_ADMINISTRATEUR){ ?>
        <div class="tab-content" id="users">
            <h2>Gérer les utilisateurs</h2>
            <table>
                <tr>
                    <th></th>
                    <th>Modifier la vidéo</th>
                    <th>Diffuser la vidéo</th>
                    <th>Supprimer la vidéo</th>
                    <th>Administrer le site</th>
                </tr>
                <?php foreach($listeProfesseurs as $professeur){ ?>
                    <tr>
                        <th><?php echo($professeur['nom'] . " " . $professeur['prenom']); ?></th>
                        <td>
                            <input type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="modifier" <?php echo $professeur["modifier"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="diffuser" <?php echo $professeur["diffuser"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="supprimer" <?php echo $professeur["supprimer"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="administrer" <?php echo $professeur["administrer"] == 1 ? "checked" : "" ;?>/>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
    
</body>
</html>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        affichageLogsCouleurs();
        gestionOngletsAdministration();
        appelScanVideo();
        detectionCheckboxes(); 
    });
</script>
