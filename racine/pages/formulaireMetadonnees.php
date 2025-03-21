<?php 
    session_start();
    require_once '../fonctions/controleur.php';
    controleurVerifierAccesPage(ACCES_MODIFICATION);
    $infosVideo = controleurRecupererInfosVideo();
    $idVideo = $infosVideo["idVideo"];
    $nomFichier = $infosVideo["nomFichier"];
    $cheminMiniatureComplet = $infosVideo["cheminMiniatureComplet"];
    $titreVideo = $infosVideo["titreVideo"];
    $description = $infosVideo["description"];
    $mtdTech = $infosVideo["mtdTech"];
    $mtdEdito = $infosVideo["mtdEdito"];
    $mtdRoles = $infosVideo["mtdRoles"];
    $promotion = $infosVideo["promotion"];
    $listeProfesseurs = controleurRecupererListeProfesseurs();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/formulaire.css" rel="stylesheet">
    <link rel="stylesheet" href="../ressources/lib/Tagify/tagify.css">
    <script src="../ressources/lib/Tagify/tagify.js"></script>
    <script src="../ressources/Script/script.js"></script>
    <?php require_once '../ressources/Templates/header.php'; ?>
</head>
<body>

<div class="form-container">
    <!-- Formulaire englobant les colonnes et les boutons -->
    <form method="post" action="#" class="metadata-form" id="metadataForm">
        <!-- Conteneur pour les deux colonnes -->
        <div class="form-columns">
            <!-- Colonne de gauche -->
            <div class="form-column-left">
                <div class="thumbnail-container">
                    <img src="<?php echo $cheminMiniatureComplet; ?>" alt="Miniature de la vidéo" class="thumbnail-image">
                </div>
                <h2 class="video-filename"><?php echo $nomFichier; ?></h2>
                <h2 class="video-title"><?php echo $titreVideo; ?></h2>

                <div class="low-column-left">
                    <table class="video-info-table">
                        <tr>
                            <th>Durée</th>
                            <td><?php echo $mtdTech['mtd_tech_duree']; ?></td>
                        </tr>
                        <tr>
                            <th>Images par seconde</th>
                            <td><?php echo $mtdTech['mtd_tech_fps']; ?></td>
                        </tr>
                        <tr>
                            <th>Résolution</th>
                            <td><?php echo $mtdTech['mtd_tech_resolution']; ?></td>
                        </tr>
                        <tr>
                            <th>Format</th>
                            <td><?php echo $mtdTech['mtd_tech_format']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Colonne de droite -->
            <div class="form-column-right">
                <h2 class="team-title">Équipe</h2>
                <input type="hidden" name="action" value="ModifierMetadonnees">
                <input type="hidden" name="idVideo" value="<?php echo $idVideo; ?>">

                <div class="form-field">
                    <label for="profReferent" class="form-label">Professeur référent</label>
                    <select id="profReferent" name="profReferent" class="form-select">
                        <option value="<?php echo $mtdEdito["professeur"]; ?>">
                             <?php echo $mtdEdito["professeur"]; ?>
                        </option>
                        <?php foreach ($listeProfesseurs as $prof) { ?>
                            <option value="<?php echo $prof; ?>"><?php echo $prof; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-field">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" maxlength="800" pattern="^(?! ).*(?<! )$" title="Ne commencez ni ne terminez par un espace"
                      class="form-input"><?php echo $description; ?></textarea>
                </div>

                <div class="form-field">
                    <label for="promotion" class="form-label">Promotion</label>
                    <input type="text" id="promotion" maxlength="50" name="promotion" pattern="^(?! ).*(?<! )$" title="Ne commencez ni ne terminez par un espace"
                  value="<?php echo $promotion; ?>" class="form-input">
                </div>

                <div class="form-field">
                    <label for="projet" class="form-label">Projet</label>
                    <input type="text" id="projet" maxlength="50" name="projet" pattern="^(?! ).*(?<! )$" title="Ne commencez ni ne terminez par un espace"
                  value="<?php echo $mtdEdito["projet"]; ?>" class="form-input">
                </div>

                <div id="roles-container">
                <?php 
                    if($mtdRoles!=null){
                        foreach ($mtdRoles as $role => $values) { 
                            $formattedId = strtolower(str_replace(' ', '_', $role));
                            echo '<div class="form-field role-field"> ';
                            echo '<label for="' . htmlspecialchars($formattedId) . '" class="form-label">' . htmlspecialchars($role) . '</label> <div class="role-inputs">';
                            echo '<input type="text" id="'. htmlspecialchars($formattedId) .'" maxlength="50" name="roles['. htmlspecialchars($role) .']" value="' . htmlspecialchars($values) . '" class="role-input">';
                            echo '</div></div>';
                        }
                    }
                ?>
                </div>
            </div>
        </div>

        <!-- Conteneur pour les boutons -->
        <div class="form-buttons-container">
            <a href="video.php?v=<?php echo $idVideo; ?>" class="form-button">Retour</a>

            <div class="bouton-droit">
                <button type="button" id="add-role" class="form-button">Ajouter un rôle</button>
                <button type="submit" class="form-button">Confirmer</button>
            </div>
        </div>
    </form>
</div>


<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    initFormMetadonnees();
</script>
</body>
</html>