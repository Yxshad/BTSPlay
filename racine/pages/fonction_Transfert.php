<?php 
	session_start(); 
	require_once '../fonctions/controleur.php';
    controleurVerifierAcces(AUTORISATION_ADMIN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="../ressources/Images/logo_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
	<script src="../ressources/Script/script.js"></script>
	<title>Fonction de transfert</title>

<?php require_once '../ressources/Templates/header.php';?>

	<h1> Fonction de transfert </h1>
	<form method="post">
		<button type="submit" name="declencherTransfert">Déclencher la fonction de transfert</button>
	</form>

<?php require_once '../ressources/Templates/footer.php';?>	



<?php

if (isset($_POST['declencherTransfert'])) {
	fonctionTransfertAffiche();
}

function fonctionTransfertAffiche(){
	ajouterLog(LOG_INFORM, "Lancement de la fonction de transfert.");
	$COLLECT_PAD = [];
	$COLLECT_ARCH = [];
	$COLLECT_MPEG = [];
	//-----------------------   répertoire NAS_PAD      ------------------------
	$COLLECT_PAD = recupererCollectNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_VIDEOS_A_ANALYSER, $COLLECT_PAD, URI_RACINE_NAS_PAD);
	ajouterLog(LOG_INFORM, "Récupération des vidéos du NAS PAD. " . count($COLLECT_PAD) . " fichiers trouvés.");
	//-----------------------   répertoire NAS_ARCH      ------------------------
	afficherCollect("COLLECT_PAD", $COLLECT_PAD);
	afficherCollect("COLLECT_ARCH", $COLLECT_ARCH);
	$COLLECT_ARCH = recupererCollectNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_VIDEOS_A_ANALYSER, $COLLECT_ARCH, URI_RACINE_NAS_ARCH);
	ajouterLog(LOG_INFORM, "Récupération des vidéos du NAS ARCH. " . count($COLLECT_ARCH) . " fichiers trouvés.");
	//Remplir $COLLECT_MPEG
	$COLLECT_MPEG = remplirCollect_MPEG($COLLECT_PAD, $COLLECT_ARCH, $COLLECT_MPEG);
	afficherCollect("COLLECT_MPEG", $COLLECT_MPEG);
	afficherCollect("COLLECT_PAD", $COLLECT_PAD);
	afficherCollect("COLLECT_ARCH", $COLLECT_ARCH);
	//Alimenter le Stockage local
	ajouterLog(LOG_INFORM, "Alimentation du stockage local avec " . count($COLLECT_MPEG) . " fichiers." );
	$COLLECT_MPEG = alimenterStockageLocal($COLLECT_MPEG);
	//Mettre à jour la base avec $COLLECT_MPEG
	ajouterLog(LOG_INFORM, "Insertion des informations dans la base de données.");
	insertionCollect_MPEG($COLLECT_MPEG);
	$COLLECT_MPEG_après_alimentation = [];
	ajouterLog(LOG_SUCCESS, "Fonction de transfert effectuée avec succès.");
}

?>