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
    <div class="resultsContainer">
        <div class="filtrage">
            <form action="#" method="get">
                <select name="prof" id="">
                    <option value="" disabled selected>Professeur référent</option>
                    <?php
                        foreach ($listeProf as $prof) {
                            echo "<option value='" . $prof["professeurReferent"] . "'>" . $prof["nom"] . " " . $prof["prenom"] . "</option>";
                        }
                    ?>
                </select>

                <input placeholder="Rechercher dans la description" type="text" name="description">

                <select placeholder="Projet" name="projet" id="">
                    <option value="" disabled selected>Projet</option>
                    <?php
                        foreach ($listeProjet as $projet) {
                            echo "<option value='" . $projet["intitule"] . "'>" . $projet["intitule"] . "</option>";
                        }
                    ?>
                </select>

                <input type="submit" value="Rechercher" id="Valider">
            </form>
        </div>
        <?php
            foreach($medias as $media){ ?>
                <div class="result">
                    <a href="video.php?v=<?php echo $media["id"] ?>">
                        <?php echo $media["mtd_tech_titre"]; ?>
                    </a>          
                </div>
            <?php }
        ?>
    </div>
    <div class="container-home-button">
        <a href="/" class="btn-home">Retour à l'accueil</a>
    </div>
</body>
</html>