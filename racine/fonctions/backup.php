<?php
require_once "../ressources/constantes.php";
require_once "ftp.php";
require_once "ffmpeg.php";
require_once "modele.php";
require_once "fonctions.php";

$commandSql = 'mysqldump --user='.BD_USER.' --password='.BD_PASSWORD.' --host=mysql '.BD_NAME.' > '. URI_DUMP_SAUVEGARDE .date("j-m-Y_H-i-s_").SUFFIXE_FICHIER_DUMP_SAUVEGARDE;
$operationSucces = exec($commandSql);
ajouterLog(LOG_INFORM, "Création d'une sauvegarde manuelle de la base le ". date("j-m-Y_H-i-s").".", NOM_FICHIER_LOG_SAUVEGARDE);

?>