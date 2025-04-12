<?php
    require_once "../fonctions/controleur.php";

    //recherche basique
    if (isset($_GET["motCle"])) {
        $medias = faireRecherche($_GET["motCle"]);
    }
    
    //recherche avancée
    else{
        $prof = (isset($_GET["prof"])) ? $_GET["prof"] : null ;
        $description = (isset($_GET["description"])) ? $_GET["description"] : null ;
        $projet = (isset($_GET["projet"])) ? $_GET["projet"] : null ;
        $promotion = (isset($_GET["promotion"])) ? $_GET["promotion"] : null ;

        $roles = (isset($_GET["roles"])) ? $_GET["roles"] : null ;
        $participants = (isset($_GET["participants"])) ? $_GET["participants"] : null ;
        $affectations = controleurPreparerAffectations($roles, $participants);

        $medias = faireRechercheAvance($prof, $description, $projet, $promotion, $affectations);

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
            <input placeholder="Rechercher dans la description" type="text" name="description" class="description">
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
                    <input type="text" placeholder="promotion">
                </div>
                
                
            </div>
            
        </form>
        <button type="button" id="add-role" class="form-button">Ajouter un rôle</button>
        <input type="submit" value="Rechercher" id="Valider">
    </div>
    <a href="#" class="btn-afficher-filtres">
        <svg fill="#000" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"></path>
        </svg>
    </a>


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
                gererFiltres();
            });
        </script>
</body>
</html>