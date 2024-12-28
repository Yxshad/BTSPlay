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
  *
  *
  */
function insertionDonneesTechniques($listeMetadonnees)
{
    $connexion = connexionBD();
    $videoAAjouter = $connexion->prepare('INSERT INTO Media (URI_NAS_PAD, URI_NAS_ARCH, URI_NAS_MPEG, URI_DOSSIER_CHAPITRAGE, ) Values ()');
}


/**
 * $liste = [MTD_TITRE => $fichier,
 *             MTD_FPS => $fps[0],
*              MTD_RESOLUTION => $resolution[0],
 *             MTD_DUREE => $dureeFormatee,
 *             MTD_FORMAT => $format[1]
 *             ];
 */
?>