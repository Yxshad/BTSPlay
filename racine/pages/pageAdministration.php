<?php 
session_start();
require_once '../fonctions/controleur.php';
controleurVerifierAccesPage(ACCES_ADMINISTRATION);

$listeProfesseurs = controleurRecupererAutorisationsProfesseurs();
$tabDernieresVideos = controleurRecupererDernieresVideosTransfereesSansMetadonnees();
// Appel des logs 
$logFile = URI_FICHIER_GENERES . NOM_FICHIER_LOG_GENERAL; // Chemin du fichier log
$maxLines = NB_LIGNES_LOGS; // Nombre maximum de lignes à afficher dans les logs
$logsGeneraux = controleurAfficherLogs($logFile, $maxLines);

//Pour les logs des sauvegardes de la BD
$logFile = URI_FICHIER_GENERES . NOM_FICHIER_LOG_SAUVEGARDE;
$maxLines = NB_LIGNES_LOGS;
$logsSauvegardesBDD = controleurAfficherLogs($logFile, $maxLines);

if(AFFICHAGE_LOGS_PLUS_RECENTS_PREMIERS=='on'){
    $logsGeneraux = array_reverse($logsGeneraux);
    $logsSauvegardesBDD = array_reverse($logsSauvegardesBDD);
}
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
        <div class="colonnes">
            <div class="colonne-1">
                <h1>Paramètre des sauvegardes</h1>
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

            <div class="log-container colonne-2">
                <?php foreach ($logsSauvegardesBDD as $line): ?>
                    <div class="log-line"><?php echo htmlspecialchars($line); ?></div>
                <?php endforeach; ?>
            </div>
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
                            <button class="btn" id="btnConversion" onclick="lancerConversion()">Lancer conversion</button>
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
        
        <?php
        // Afficher un message de succès si les constantes ont été mises à jour
        if (isset($successMessage)) {
            echo "<p style='color:green;'>$successMessage</p>";
        }
        ?>
        
        <form method="post" action="#">
            <input type="hidden" name="action" value="mettreAJourParametres">
            <h3>URIs</h3>
            <label for="uri_racine_nas_pad">URI Racine NAS PAD:</label>
            <input type="text" id="uri_racine_nas_pad" name="uri_racine_nas_pad" value="<?php echo URI_RACINE_NAS_PAD; ?>"><br><br>
            
            <label for="uri_racine_nas_arch">URI Racine NAS ARCH:</label>
            <input type="text" id="uri_racine_nas_arch" name="uri_racine_nas_arch" value="<?php echo URI_RACINE_NAS_ARCH; ?>"><br><br>
            
            <label for="uri_racine_stockage_local">URI Racine Stockage Local:</label>
            <input type="text" id="uri_racine_stockage_local" name="uri_racine_stockage_local" value="<?php echo URI_RACINE_STOCKAGE_LOCAL; ?>"><br><br>
            
            <label for="uri_racine_nas_diff">URI Racine NAS DIFF:</label>
            <input type="text" id="uri_racine_nas_diff" name="uri_racine_nas_diff" value="<?php echo URI_RACINE_NAS_DIFF; ?>"><br><br>
            
            <h3>Connexions FTP</h3>
            <label for="nas_pad">NAS PAD:</label>
            <input type="text" id="nas_pad" name="nas_pad" value="<?php echo NAS_PAD; ?>"><br><br>
            
            <label for="login_nas_pad">Login NAS PAD:</label>
            <input type="text" id="login_nas_pad" name="login_nas_pad" value="<?php echo LOGIN_NAS_PAD; ?>"><br><br>
            
            <label for="password_nas_pad">Password NAS PAD:</label>
            <input type="password" id="password_nas_pad" name="password_nas_pad" value="<?php echo PASSWORD_NAS_PAD; ?>"><br><br>
            
            <label for="nas_arch">NAS ARCH:</label>
            <input type="text" id="nas_arch" name="nas_arch" value="<?php echo NAS_ARCH; ?>"><br><br>
            
            <label for="login_nas_arch">Login NAS ARCH:</label>
            <input type="text" id="login_nas_arch" name="login_nas_arch" value="<?php echo LOGIN_NAS_ARCH; ?>"><br><br>
            
            <label for="password_nas_arch">Password NAS ARCH:</label>
            <input type="password" id="password_nas_arch" name="password_nas_arch" value="<?php echo PASSWORD_NAS_ARCH; ?>"><br><br>
            
            <label for="nas_diff">NAS DIFF:</label>
            <input type="text" id="nas_diff" name="nas_diff" value="<?php echo NAS_DIFF; ?>"><br><br>
            
            <label for="login_nas_diff">Login NAS DIFF:</label>
            <input type="text" id="login_nas_diff" name="login_nas_diff" value="<?php echo LOGIN_NAS_DIFF; ?>"><br><br>
            
            <label for="password_nas_diff">Password NAS DIFF:</label>
            <input type="password" id="password_nas_diff" name="password_nas_diff" value="<?php echo PASSWORD_NAS_DIFF; ?>"><br><br>
            
            <h3>Base de données</h3>
            <label for="bd_host">BD Host:</label>
            <input type="text" id="bd_host" name="bd_host" value="<?php echo BD_HOST; ?>"><br><br>
            
            <label for="bd_port">BD Port:</label>
            <input type="text" id="bd_port" name="bd_port" value="<?php echo BD_PORT; ?>"><br><br>
            
            <label for="bd_name">BD Name:</label>
            <input type="text" id="bd_name" name="bd_name" value="<?php echo BD_NAME; ?>"><br><br>
            
            <label for="bd_user">BD User:</label>
            <input type="text" id="bd_user" name="bd_user" value="<?php echo BD_USER; ?>"><br><br>
            
            <label for="bd_password">BD Password:</label>
            <input type="password" id="bd_password" name="bd_password" value="<?php echo BD_PASSWORD; ?>"><br><br>
            
            <h3>Fichiers générés</h3>
            <label for="uri_fichier_generes">URI Fichiers Générés:</label>
            <input type="text" id="uri_fichier_generes" name="uri_fichier_generes" value="<?php echo URI_FICHIER_GENERES; ?>"><br><br>
            
            <label for="uri_dump_sauvegarde">URI Dump Sauvegarde:</label>
            <input type="text" id="uri_dump_sauvegarde" name="uri_dump_sauvegarde" value="<?php echo URI_DUMP_SAUVEGARDE; ?>"><br><br>
            
            <label for="nom_fichier_log_general">Nom Fichier Log Général:</label>
            <input type="text" id="nom_fichier_log_general" name="nom_fichier_log_general" value="<?php echo NOM_FICHIER_LOG_GENERAL; ?>"><br><br>
            
            <label for="nom_fichier_log_sauvegarde">Nom Fichier Log Sauvegarde:</label>
            <input type="text" id="nom_fichier_log_sauvegarde" name="nom_fichier_log_sauvegarde" value="<?php echo NOM_FICHIER_LOG_SAUVEGARDE; ?>"><br><br>
            
            <label for="suffixe_fichier_dump_sauvegarde">Suffixe Fichier Dump Sauvegarde:</label>
            <input type="text" id="suffixe_fichier_dump_sauvegarde" name="suffixe_fichier_dump_sauvegarde" value="<?php echo SUFFIXE_FICHIER_DUMP_SAUVEGARDE; ?>"><br><br>
            
            <h3>Pages</h3>
            <label for="nb_videos_par_swiper">Nombre de vidéos par Swiper:</label>
            <input type="number" id="nb_videos_par_swiper" name="nb_videos_par_swiper" value="<?php echo NB_VIDEOS_PAR_SWIPER; ?>"><br><br>
            
            <h3>Historique du transfert</h3>
            <label for="nb_videos_historique_transfert">Nombre de vidéos dans l'historique:</label>
            <input type="number" id="nb_videos_historique_transfert" name="nb_videos_historique_transfert" value="<?php echo NB_VIDEOS_HISTORIQUE_TRANSFERT; ?>"><br><br>
            
            <h3>Logs</h3>
            <label for="nb_lignes_logs">Nombre de lignes de logs:</label>
            <input type="number" id="nb_lignes_logs" name="nb_lignes_logs" value="<?php echo NB_LIGNES_LOGS; ?>"><br><br>
            
            <h3>Multithreading</h3>
            <label for="nb_max_processus_transfert">Nombre maximum de processus de transfert:</label>
            <input type="number" id="nb_max_processus_transfert" name="nb_max_processus_transfert" value="<?php echo NB_MAX_PROCESSUS_TRANSFERT; ?>"><br><br>
            
            <h3>Affichage des logs</h3>
            <label for="affichage_logs_plus_recents_premiers">Afficher les logs les plus récents en premier:</label>
            <input type="checkbox" id="affichage_logs_plus_recents_premiers" name="affichage_logs_plus_recents_premiers" <?php echo AFFICHAGE_LOGS_PLUS_RECENTS_PREMIERS=='on' ? 'checked' : ''; ?>><br><br>
            
            <input type="submit" value="Mettre à jour">
        </form>
    </div>

    <div class="tab-content" id="logs">
        <h2>Consulter les logs</h2>
        <div class="log-container">
            <?php foreach ($logsGeneraux as $line): ?>
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
                        <?php 
                        if ($professeur["role"] == ROLE_ADMINISTRATEUR) {
                            $desactivation = "disabled";
                            $class = "class='gris'";
                        } else {
                            $desactivation = "";
                            $class = "";
                        }
                        ?>

                        <th <?php echo $class; ?>><?php echo($professeur['nom'] . " " . $professeur['prenom']); ?></th>

                        <td>
                            <input <?php echo $desactivation; ?> type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="modifier" <?php echo $professeur["modifier"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input <?php echo $desactivation; ?> type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="diffuser" <?php echo $professeur["diffuser"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input <?php echo $desactivation; ?> type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="supprimer" <?php echo $professeur["supprimer"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input <?php echo $desactivation; ?> type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="administrer" <?php echo $professeur["administrer"] == 1 ? "checked" : "" ;?>/>
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
