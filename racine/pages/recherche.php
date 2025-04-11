<?php

    //PROMOTION A FAIRE !

    require_once "../fonctions/controleur.php";

    //recherche basique
    if (isset($_GET["motCle"])) {
        $medias = faireRecherche($_GET["motCle"], false);
    }
    
    //recherche avancée
    else{
        $prof = (isset($_GET["prof"])) ? $_GET["prof"] : null ;
        $description = (isset($_GET["description"])) ? $_GET["description"] : null ;
        $projet = (isset($_GET["projet"])) ? $_GET["projet"] : null ;
        $roles = (isset($_GET["roles"])) ? $_GET["roles"] : null ;
        $participants = (isset($_GET["participants"])) ? $_GET["participants"] : null ;

        print_r($roles);
        echo "<br/>";
        print_r($participants);
        echo "<br/>";

        $affectations = [];

        foreach ($roles as $index => $roleArr) {
            $roleString = (is_array($roleArr) && isset($roleArr[0])) ? $roleArr[0] : '';
            $nomString = (isset($participants[$index]) && is_array($participants[$index]) && isset($participants[$index][0]) && trim($participants[$index][0]) !== '') ? $participants[$index][0] : '';

            // Si aucun participant n'est défini, on attribue "n'importe"
            if ($nomString === '') {
                $nomString = 'Affectation libre';
            }

            // Si le rôle est vide
            if (trim($roleString) === '') {
                // Si un nom est défini, on l'affecte à un rôle libre
                $affectations[] = [$nomString => 'Affectation libre'];
                continue;
            }

            // Traitement des rôles et des noms
            $listeRoles = array_map('trim', explode(',', $roleString));

            $participantsArray = explode(', ', $nomString);
            foreach ($participantsArray as $participant) {
                // On parcourt chaque rôle et on l'affecte au participant (ici, "n’importe" s'il n'y a pas de nom)
                foreach ($listeRoles as $role) {
                    $affectations[] = [$participant => $role];
                }
            }
        }

        $medias = faireRechercheAvance($prof, $description, $projet, $affectations);

    }
    $listeProf = getAllProfesseursReferent();
    $listeProjet = getAllProjet();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/menuFiltres.css" rel="stylesheet">
    <link href="../ressources/Style/recherche.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>
    <link rel="stylesheet" href="../ressources/lib/Tagify/tagify.css">
    <script src="../ressources/lib/Tagify/tagify.js"></script>
    <title>Recherche</title>
</head>
<body>
    <?php require_once '../ressources/Templates/header.php'; ?>

    <div class="filtrage">
        <form action="#" method="get">
            <input placeholder="Rechercher dans la description" type="text" name="description">
            <div>
                <div class="selects">
                    <select name="prof" id="">
                        <option value="" disabled selected>Professeur référent</option>
                        <?php
                            foreach ($listeProf as $prof) {
                                echo "<option value='" . $prof["professeurReferent"] . "'>" . $prof["nom"] . " " . $prof["prenom"] . "</option>";
                            }
                        ?>
                    </select>
                    <select placeholder="Projet" name="projet" id="">
                        <option value="" disabled selected>Projet</option>
                        <?php
                            foreach ($listeProjet as $projet) {
                                echo "<option value='" . $projet["intitule"] . "'>" . $projet["intitule"] . "</option>";
                            }
                        ?>
                    </select>

                    </div>
                </div>
                <input type="submit" value="Rechercher" id="Valider">
            </div>
            <button type="button" id="add-role" class="form-button">Ajouter un rôle</button>
        </form>
    </div>
    <div class="resultsContainer">
        <?php
            foreach($medias as $media){ ?>
                <div class="result">
                    <a href="video.php?v=<?php echo $media["id"] ?>">
                        <div class="miniature">
                            <img src="<?php echo '/stockage/' . $media['URI_STOCKAGE_LOCAL'] . trouverNomMiniature($media["mtd_tech_titre"]); ?>" alt="">
                        </div>
                        <div class="info-video">
                            <p class="titre-video">
                                <?php echo $media["mtd_tech_titre"]; ?>
                            </p>
                            <p class="description">
                                <?php echo $media["description"]; ?>
                            </p>
                        </div>
                    </a>
                </div>
            <?php }
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let index = 0;
                document.getElementById("add-role").addEventListener("click", function() {
                    let container = document.querySelector(".filtrage form");
                    
                    let newRoleDiv = document.createElement("div.role-acteur");

                    newRoleDiv.innerHTML = `
                        <input type="text" id="role_${index}" name="roles[${index}][]">
                        <input type="text" id="participant_${index}" name="participants[${index}][]">
                    `;
                    
                    container.insertBefore(newRoleDiv, container.children[container.childElementCount -1]);

                    initTagify(`#role_${index}`);
                    initTagify(`#participant_${index}`);
                    index++;
                });
            });
        </script>
</body>
</html>