<?php
include '../ressources/constantes.php';
/**
 *  @NOM : methodes.php
 *  @DESCRIPTION : Fonctions php liées aux manipulations (insertions, suppressions...) sur la base de données
 *  @CREATION : 19/12/2024
 *  @DERNIERE MODIFICATION : 28/12/2024 - XXHXX
 *  @COLLABORATEURS : Elsa Lavergne
 */


/**
 *  @Nom : connexionBD
  * @Description : Permet de se connecter en base de données et de checker au passage s'il y a eu des erreurs de connexion
 */
function connexionBD()
{
    try
    {
        $mysqlClient = new PDO('mysql:host=localhost;dbname=mydatabase;charset=utf8', 'myuser', 'mypassword');
        return $mysqlClient;
    }
    catch (Exception $e)
    {
        die('Erreur : ' . $e->getMessage());
    }
}


 /**
  * @Nom : insertionDonneesTechniques*
  * @Description : crée la vidéo en base de données et insère les métadonnées techniques associées 
  * @$listeMetadonnees : liste des metadonnées techniques à insérer
  */
function insertionDonneesTechniques($listeMetadonnees)
{
    $connexion = connexionBD();
    $videoAAjouter = $connexion->prepare('INSERT INTO Media (URI_RACINE_NAS_PAD, 
    URI_RACINE_NAS_ARCH, 
    URI_RACINE_NAS_MPEG, 
    URI_DOSSIER_CHAPITRAGE, 
    mtd_tech_titre,
    mtd_tech_duree,
    mtd_tech_resolution,
    mtd_tech_fps,
    mtd_tech_format) Values (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    try{
        $videoAAjouter->execute([
            $URI_NAS_PAD, 
            $URI_NAS_ARCH, 
            $URI_NAS_MPEG,
            PATHINFO_FILENAME,
            $listeMetadonnees[MTD_TITRE],
            $listeMetadonnees[MTD_DUREE],
            $listeMetadonnees[MTD_RESOLUTION],
            $listeMetadonnees[MTD_FPS],
            $listeMetadonnees[MTD_FORMAT]]);
            $connexion->commit();
    }
    catch(Exception e)
    {
        $connexion->rollback();
    }
    
}


/**
* @Nom : getMetadonneesEdito
* @Description : sélectionne les métadonnées éditoriales insérées par l'utilisateur
 */

 function getMetadonneesEdito()
 {

 }

/**
* @Nom : insertionDonneesEditoriales
* @Description : insère les métadonnées éditoriales sur la vidéo concernée
* @$listeMetadonnees : liste des metadonnées editoriales à insérer
 */

 function insertionDonneesEditoriales($listeEdito)
 {

 }
?>