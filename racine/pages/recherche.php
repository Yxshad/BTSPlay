<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Informations de connexion à la base de données
$host = 'mysql';
$dbname = 'mydatabase';
$username = 'myuser';
$password = 'mypassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Erreur de connexion à la base de données : ' . $e->getMessage()]));
}

// Endpoint pour récupérer les professeurs référents
if (isset($_GET['action']) && $_GET['action'] == 'getProfesseursReferents') {
    try {
        $sql = "SELECT DISTINCT professeurReferent FROM media WHERE professeurReferent IS NOT NULL ORDER BY professeurReferent ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $professeurs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        header('Content-Type: application/json');
        echo json_encode($professeurs);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la récupération des professeurs référents : ' . $e->getMessage()]);
        exit;
    }
}

// Fonction pour effectuer la recherche
function performSearch($pdo, $keyword, $descriptionKeyword, $titreKeyword, $professeurId) {
    $sql = "SELECT id, mtd_tech_titre AS titre, projet AS nom, description, professeurReferent
            FROM media
            WHERE 1=1";

    $params = [];

    if (!empty($keyword)) {
        $keywords = explode(' ', $keyword);
        foreach ($keywords as $index => $kw) {
            if (!empty(trim($kw))) {
                $sql .= " AND (mtd_tech_titre LIKE :keyword$index OR projet LIKE :keyword$index OR description LIKE :keyword$index)";
                $params[":keyword$index"] = "%" . trim($kw) . "%";
            }
        }
    }

    if (!empty($descriptionKeyword)) {
        $descKeywords = explode(' ', $descriptionKeyword);
        foreach ($descKeywords as $index => $kw) {
            if (!empty(trim($kw))) {
                $sql .= " AND description LIKE :desc$index";
                $params[":desc$index"] = "%" . trim($kw) . "%";
            }
        }
    }

    if (!empty($titreKeyword)) {
        $titreKeywords = explode(' ', $titreKeyword);
        foreach ($titreKeywords as $index => $kw) {
            if (!empty(trim($kw))) {
                $sql .= " AND mtd_tech_titre LIKE :titre$index";
                $params[":titre$index"] = "%" . trim($kw) . "%";
            }
        }
    }

    if (!empty($professeurId)) {
        $sql .= " AND professeurReferent = :professeurId";
        $params[":professeurId"] = $professeurId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Vérifier si c'est une requête AJAX
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

// Récupérer les paramètres de recherche
$keyword = isset($_GET['motCle']) ? trim($_GET['motCle']) : '';
$descriptionKeyword = isset($_GET['descriptionKeyword']) ? trim($_GET['descriptionKeyword']) : '';
$titreKeyword = isset($_GET['TitreKeyword']) ? trim($_GET['TitreKeyword']) : '';
$professeurId = isset($_GET['professeurReferent']) ? trim($_GET['professeurReferent']) : '';

// Effectuer la recherche
try {
    $results = performSearch($pdo, $keyword, $descriptionKeyword, $titreKeyword, $professeurId);
    $compteur = count($results);

    if ($isAjax) {
        // Retourner les résultats en JSON pour les requêtes AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'count' => $compteur,
            'results' => $results,
            'keyword' => $keyword
        ]);
        exit;
    }
} catch (PDOException $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la recherche : ' . $e->getMessage()]);
        exit;
    } else {
        die(json_encode(['error' => 'Erreur lors de la recherche : ' . $e->getMessage()]));
    }
}
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
    <title>Recherche</title>
</head>
<body>
    <?php require_once '../ressources/Templates/header.php'; ?>

    <div class="search-controls">
        <div id="advancedSearchContainer">
            <div id="advancedSearchMenu" class="advanced-search-menu">
                <div class="advanced-search-form">
                    <div class="form-group">
                        <label for="descriptionKeyword">Mot-clé dans la description :</label>
                        <input type="text" id="descriptionKeyword" name="descriptionKeyword" class="search-input">
                    </div>
                    <div class="form-group">
                        <label for="TitreKeyword">Mot-clé dans le titre :</label>
                        <input type="text" id="TitreKeyword" name="TitreKeyword" class="search-input">
                    </div>
                    <div class="form-group">
                        <label for="professeurReferent">Professeur référent :</label>
                        <select id="professeurReferent" name="professeurReferent" class="search-input">
                            <option value="">Tous les professeurs</option>
                            <!-- Options seront ajoutées dynamiquement -->
                        </select>
                    </div>
                    <button id="applyAdvancedSearch" class="btn-apply">Appliquer</button>
                </div>
            </div>
            <button id="toggleAdvancedSearch" class="btn-advanced-search">
                <img id="img_toggle" class="img_toggle" src="../ressources/Images/fleches.png" />
            </button>
        </div>
    </div>

    <div id="resultsContainer">
        <?php if (isset($results)): ?>
            <h2>Résultats de recherche</h2>
            <?php if ($compteur > 0): ?>
                <div class="results-list">
                    <?php foreach ($results as $media): ?>
                        <div class="result">
                            <h3><?= htmlspecialchars($media['titre'] ?? '') ?></h3>
                            <?php if ($media['nom']): ?>
                                <p><strong>Nom :</strong> <?= htmlspecialchars($media['nom']) ?></p>
                            <?php endif; ?>
                            <?php if ($media['description']): ?>
                                <p><strong>Description :</strong> <?= htmlspecialchars($media['description']) ?></p>
                            <?php endif; ?>
                            <?php if ($media['professeurReferent']): ?>
                                <p><strong>Professeur référent :</strong> <?= htmlspecialchars($media['professeurReferent']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p>Nombre de résultats : <?= $compteur ?></p>
            <?php else: ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <a href="/" class="btn-home">Retour à l'accueil</a>

    <script src="../ressources/Script/recherche.js"></script>
</body>
</html>