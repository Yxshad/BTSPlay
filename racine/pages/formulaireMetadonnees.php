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

<div class="container">
    <h1>Formulaire des métadonnées</h1>

    <div class="colonnes">
        <div class="colonne-1">
            <div class="img">
                <img src="<?php echo $cheminMiniatureComplet; ?>" alt="Miniature de la vidéo" class="imageMiniature">
            </div>
            <h2 class="titre"><?php echo $nomFichier; ?></h2>
            <h2 class="titre"><?php echo $titreVideo; ?></h2>
            <p><strong>Durée :</strong> <?php echo $mtdTech['mtd_tech_duree']; ?></p>
            <p><strong>Images par seconde :</strong> <?php echo $mtdTech['mtd_tech_fps']; ?></p>
            <p><strong>Résolution :</strong> <?php echo $mtdTech['mtd_tech_resolution']; ?></p>
            <p><strong>Format :</strong> <?php echo $mtdTech['mtd_tech_format']; ?></p>
        </div>

        <div class="colonne-2">
            <h2>Équipe</h2>
            <form method="post" action="#">
                <input type="hidden" name="action" value="ModifierMetadonnees">
                <input type="hidden" name="idVideo" value="<?php echo $idVideo; ?>">

                <div class="champ">

                    <label for="profReferent" class="form-label">Professeur référent</label>

                    <select id="profReferent" name="profReferent">
                        <option value="<?php echo $mtdEdito["professeur"]; ?>">
                            Professeur actuel : <?php echo $mtdEdito["professeur"]; ?>
                        </option>
                        <?php foreach ($listeProfesseurs as $prof) { ?>
                            <option value="<?php echo $prof; ?>"><?php echo $prof; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="champ">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" value="<?php echo $description; ?>" 
                        pattern="^(?! ).*(?<! )$" title="Ne commencez ni ne terminez par un espace">
                </div>

                <div class="champ">
                    <label for="promotion">Promotion</label>
                    <input type="text" id="promotion" name="promotion" value="<?php echo $promotion; ?>" 
                        pattern="^(?! ).*(?<! )$" title="Ne commencez ni ne terminez par un espace">
                </div>

                <div class="champ">
                    <label for="projet">Projet</label>
                    <input type="text" id="projet" name="projet" value="<?php echo $mtdEdito["projet"]; ?>" 
                        pattern="^(?! ).*(?<! )$" title="Ne commencez ni ne terminez par un espace">
                </div>

                <div id="roles-container">
                <?php 
                    if($mtdRoles!=null){
                        foreach ($mtdRoles as $role => $values) { 
                            $formattedId = strtolower(str_replace(' ', '_', $role));
                            echo '<div class="champ role"> ';
                            echo '<label for="' . htmlspecialchars($formattedId) . '">' . htmlspecialchars($role) . '</label> <div class="inputs">';
                            echo '<input class="role-input" type="text" id="'. htmlspecialchars($formattedId) .'" name="roles['. htmlspecialchars($role) .']" value="' . htmlspecialchars($values) . '">';
                            echo '</div></div>';
                        }
                    }
                ?>
                </div>

                <button type="button" id="add-role" class="btn">Ajouter un rôle</button>
                <button type="submit" class="btn">Confirmer</button>
            </form>
        </div>
    </div>

    <div class="btns">
        <a href="video.php?v=<?php echo $idVideo; ?>" class="btn">Retour</a>
    </div>
</div>

<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    initFormMetadonnees();
    envoiMetadonnees();
</script>
</body>
</html>