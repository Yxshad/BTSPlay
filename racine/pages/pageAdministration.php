<?php 
session_start();
require_once '../fonctions/controleur.php';

// Appel des logs 
$logFile = '../ressources/historique.log'; // Chemin du fichier log
$maxLines = 100; // Nombre maximum de lignes à afficher
$logs = getLastLines($logFile, $maxLines);

// Vérification de la soumission du formulaire AVANT toute sortie HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === "declencherReconciliation") {
    ob_start(); // Démarrer la capture de sortie pour éviter les erreurs de header
    fonctionReconciliationAffichee();
    ob_end_clean(); // Nettoyer la sortie tamponnée

    // Redirection AVANT d'envoyer du contenu
    header("Location: ?tab=reconciliation");
    exit();
}
//fonction de réconciliation 
function fonctionReconciliationAffichee() {
    $listeVideos_NAS_1 = recupererNomsVideosNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_RACINE_NAS_PAD, []);
    $listeVideos_NAS_2 = recupererNomsVideosNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_RACINE_NAS_ARCH, []);

    ob_start(); // Capture la sortie pour éviter les erreurs de header
    echo "<h2>Vidéos présentes sur " . NAS_PAD . ":</h2>";
    echo "<pre>" . print_r($listeVideos_NAS_1, true) . "</pre>";

    echo "<h2>Vidéos présentes sur " . NAS_ARCH . ":</h2>";
    echo "<pre>" . print_r($listeVideos_NAS_2, true) . "</pre>";

    $listeVideosManquantes = trouverVideosManquantes(NAS_PAD, NAS_ARCH, $listeVideos_NAS_1, $listeVideos_NAS_2, []);
    afficherVideosManquantes($listeVideosManquantes);

    ajouterLog(LOG_SUCCESS, "Fonction de réconciliation effectuée avec succès.");
    $_SESSION['reconciliation_result'] = ob_get_clean(); // Stocker la sortie pour l'afficher après redirection
}
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
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');
            
            function setActiveTab(tabId) {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                const activeTab = document.querySelector(`.tab[data-tab="${tabId}"]`);
                const activeContent = document.getElementById(tabId);

                if (activeTab && activeContent) {
                    activeTab.classList.add('active');
                    activeContent.classList.add('active');
                }
            }

            // Vérifie s'il y a un paramètre "tab" dans l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || "database"; // "database" par défaut

            setActiveTab(activeTab);

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabId = tab.dataset.tab;
                    setActiveTab(tabId);

                    // Met à jour l'URL sans recharger la page
                    const newUrl = `${window.location.pathname}?tab=${tabId}`;
                    window.history.pushState({ path: newUrl }, '', newUrl);
                });
            });
        });
        document.addEventListener("DOMContentLoaded", function () {
            scanDossierDecoupeVideo();
            setInterval( scanDossierDecoupeVideo , 5000);
        });
    </script>
</body>
</html>
