<?php
require_once "../fonctions/controleur.php";
if (isset($_GET["motCle"])) {
    $medias = faireRecherche($_GET["motCle"], false);
}else{
    $prof = (isset($_GET["prof"])) ? $_GET["prof"] : null ;
    $description = (isset($_GET["description"])) ? $_GET["description"] : null ;
    $projet = (isset($_GET["projet"])) ? $_GET["projet"] : null ;

    $medias = faireRechercheAvance($prof, $description, $projet);
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
                <input type="submit" value="Rechercher" id="Valider">
            </div>
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
    </div>
</body>
</html>