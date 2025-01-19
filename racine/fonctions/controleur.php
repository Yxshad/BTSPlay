<?php
require_once "../ressources/constantes.php";
require_once "ftp.php";
require_once "ffmpeg.php";
require_once "modele.php";
require_once "fonctions.php";


if (isset($_POST["action"])) {

    if ($_POST["action"] == "scanDossierDecoupeVideo") {
        header('Content-Type: application/json');
        scanDossierDecoupeVideo(); 
        exit();
    }
    if ($_POST["action"] == "lancerConversion") {
        fonctionTransfert();
    }
}
?>