<?php 
session_start();
require_once '../fonctions/controleur.php';



// Appel des logs 
$logFile = '../ressources/historique.log'; // Chemin du fichier log
$maxLines = 100; // Nombre maximum de lignes à afficher
$logs = getLastLines($logFile, $maxLines);

?>

<!DOCTYPE html>
<html lang="fr">
<head>

    <title>Administration du Site</title>
    <link href="../ressources/Style/pageAdministration.css" rel="stylesheet">
<?php require_once '../ressources/Templates/header.php'; ?>


<body>
    <div><h1>Administration du Site</h1></div>
    <div class="tabs">
        <div class="tab active" data-tab="database">Base de données</div>
        <div class="tab" data-tab="reconciliation">Réconciliation</div>
        <div class="tab" data-tab="transfer">Fonction de transfert</div>
        <div class="tab" data-tab="settings">Paramétrage du site</div>
        <div class="tab" data-tab="logs">Consulter les logs</div>
        <div class="tab" data-tab="users">Gérer les utilisateurs</div>
    </div>
    
    <div class="tab-content active" id="database">
        <h2>Base de données</h2>
        <p>Interface de gestion des données...</p>
    </div>
    <div class="tab-content" id="reconciliation">
        <h2>Réconciliation</h2>
        <p>Gestion des opérations de réconciliation...</p>
    </div>
    <div class="tab-content" id="transfer">
        <h2>Fonction de transfert</h2>
        <p>Outils pour le transfert des données...</p>
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
        const tabs = document.querySelectorAll('.tab');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>
