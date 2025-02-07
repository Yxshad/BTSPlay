<?php 
session_start();
require_once '../fonctions/controleur.php';

// Appel des logs 
$logFile = '../ressources/historique.log'; // Chemin du fichier log
$maxLines = NBR_LIGNES_LOGS; // Nombre maximum de lignes à afficher
$logs = controleurAfficherLogs($logFile, $maxLines);

// Vérification de la soumission du formulaire AVANT toute sortie HTML

//fonction de réconciliation 

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Administration du Site</title>
    <link href="../ressources/Style/pageAdministration.css" rel="stylesheet">
    <link href="../ressources/Style/transfert.css" rel="stylesheet">
    <?php require_once '../ressources/Templates/header.php'; ?>
</head>
<body>
    <div><h1>Administration du Site</h1></div>
    <div class="tabs">
        <div class="tab" data-tab="database">Base de données</div>
        <div class="tab" data-tab="reconciliation">Réconciliation</div>
        <div class="tab" data-tab="transfer">Fonction de transfert</div>
        <div class="tab" data-tab="settings">Paramétrage du site</div>
        <div class="tab" data-tab="logs">Consulter les logs</div>
        <div class="tab" data-tab="users">Gérer les utilisateurs</div>
    </div>
    
    <div class="tab-content" id="database">
        <h2>BDD</h2>
        <p>WORK IN PROGRESS</p>
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
                        <div class="nomColonne">
                            <p>Date</p>
                            <p>Nom</p>
                        </div>
                        <?php for ($i=0; $i < 6; $i++) { ?>
                            <div class="ligne">
                                <div>
                                    <p>04/05/2025 17:42</p>
                                    <p>vidéo.mp4</p>
                                </div>
                            </div>
                        <?php } ?>
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
        <pre><?php echo implode("\n", $logs); ?></pre>
    </div>

    <div class="tab-content" id="users">
        <h2>Gérer les utilisateurs</h2>
        <p>Configuration des comptes utilisateurs...</p>
    </div>
    
</body>
</html>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        gestionOngletsAdministration();
        appelScanVideo();
    });
</script>
