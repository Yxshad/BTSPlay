<?php
    // Inclure le fichier de constantes
    require_once "/var/www/html/ressources/constantes.php";
    require_once "/var/www/html/fonctions/fonctions.php";

    $backupDir = "/var/www/html/ressources/datas/dumpBD";
    $fileName = $backupDir . "/autosave_" . date("j-m-Y_H-i-s_") . SUFFIXE_FICHIER_DUMP_SAUVEGARDE;
    $commandSql = 'mysqldump --user='.BD_USER.' --password='.BD_PASSWORD.' --host=mysql --add-drop-table '.BD_NAME.' > '.$fileName;
    $operationSucces = exec($commandSql);
?>