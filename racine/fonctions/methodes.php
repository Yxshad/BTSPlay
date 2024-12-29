<?php
/**
 *  @NOM : methodes.php
 *  @DESCRIPTION : Fonctions php liées aux manipulations (insertions, suppressions...) sur la base de données
 *  @CREATION : 19/12/2024
 *  @DERNIERE MODIFICATION : 29/12/2024 - XXHXX
 *  @COLLABORATEURS : Elsa Lavergne
 */


/**
 *  @Nom : connexion
  * @Description : Permet de se connecter sur le MAM
 */
function connexion($username, $password)
{
    $connexionSurBD = connexionBD();
    try
    {
    // #RISQUE : TABLE PAS ENCORE OFFICIELLE POUR LA CONNEXION - JE CALE COMME ÇA POUR L'INSTANT.......
        $videoAAjouter = $connexion->prepare('SELECT username from utilisateurs WHERE username = ? AND password = ?');
        $videoAAjouter->execute([$username, $password]);

        //Check si le mot de passe est correct post select
        if($videoAAjouter > 0)
        {
            //Jsp je fais juste la form générale là :insane:
            $connexion = null;
        }
        else{
            throw new Exception("Mauvais mot de passe ou nom d'utilisateur", 1);
            
        }
    }
    catch (Exception $e)
    {
        $connexion = null;
        die('Erreur : ' . $e->getMessage());
    }
}


/**
 *  @Nom : connexionBD
  * @Description : Permet de se connecter en base de données et de checker au passage s'il y a eu des erreurs de connexion
 */
function connexionBD()
{
    try
    {
        // #RISQUE : Changement de l'utilisateur BD
        $mysqlClient = new PDO('mysql:host=localhost;dbname=mydatabase;charset=utf8', 'myuser', 'mypassword');
        return $mysqlClient;
    }
    catch (Exception $e)
    {
        die('Erreur : ' . $e->getMessage());
    }
}


 /**
  * @Nom : insertionDonneesTechniques
  * @Description : crée la vidéo en base de données et insère les métadonnées techniques associées 
  * @$listeMetadonnees : liste des metadonnées techniques à insérer
  */
function insertionDonneesTechniques($listeMetadonnees)
{
    $connexion = connexionBD();                                                         // Connexion à la BD
    $videoAAjouter = $connexion->prepare('INSERT INTO Media (
    URI_RACINE_NAS_PAD, 
    URI_RACINE_NAS_ARCH, 
    URI_RACINE_NAS_MPEG,
    mtd_tech_titre,
    mtd_tech_duree,
    mtd_tech_resolution,
    mtd_tech_fps,
    mtd_tech_format) Values (?, ?, ?, ?, ?, ?, ?, ?)');                              //Construction de la requête
    try{
        $videoAAjouter->execute([
            $URI_NAS_PAD, 
            $URI_NAS_ARCH, 
            $URI_NAS_MPEG,
            $listeMetadonnees[MTD_TITRE],
            $listeMetadonnees[MTD_DUREE],
            $listeMetadonnees[MTD_RESOLUTION],
            $listeMetadonnees[MTD_FPS],
            $listeMetadonnees[MTD_FORMAT]]);                                            //Ajout des paramètres - #RISQUE : Variables éventuellement fausses pour les liens NAS ?
            $connexion->commit();
            $connexion = null;
    }
    catch(Exception e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
}


/**
* @Nom : getMetadonneesEdito
* @Description : sélectionne les métadonnées éditoriales insérées par l'utilisateur
 */

 function getMetadonneesEdito()
 {
        //A voir plus tard, dépendra de ce que j'ai sur le onClick de l'autre page
 }

/**
* @Nom : insertionDonneesEditoriales
* @Description : insère les métadonnées éditoriales sur la vidéo concernée
* @$listeMetadonnees : liste des metadonnées editoriales à insérer
* @$video : l'id de la video qu'on aimerait éditer ? (je sais pas si on part sur l'id hmmmmmmmmmmmmm)
 */

 function insertionDonneesEditoriales($video, $listeEdito)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $videoAAjouter = $connexion->prepare('UPDATE media 
    SET professeurReferent = ?,
    WHERE id = ? ');                             
    try{
        $videoAAjouter->execute([
            $video]);                                                               //Ajout des paramètres
        $connexion->commit();
        $connexion = null;
    }
    catch(Exception e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

/** #################################################
 *    FONCTIONS "GETTERS" DE RECHERCHE SUR LES TABLES
 *####################################################*/

 /**
* @Nom : getRealisateur
* @Description : renvoie la liste des réalisateurs d'une vidéo
* @video : id de la vidéo concernée
 */

 function getRealisateur($video)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $requeteReal = $connexion->prepare('SELECT nom, prenom 
    FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve
    WHERE idVideo = ? AND idRole = 2');                                                 // #RISQUE : j'ai mis 2 en estimant que ce serait l'id des réalisateurs mais bon hein :v 
    try{
        $requeteReal->execute([$video]);
        $listeReal = $requeteReal->fetchAll();
        $connexion = null;
        return $projet;
    }
    catch(Exception e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }


 /**
* @Nom : getCadreurs
* @Description : renvoie la liste des cadreurs d'une vidéo
* @video : id de la vidéo concernée
 */

 function getCadreurs($video)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $requeteCadreur = $connexion->prepare('SELECT nom, prenom 
    FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve
    WHERE idVideo = ? AND idRole = 1');                                                 // #RISQUE : j'ai mis 1 en estimant que ce serait l'id des cadreurs mais bon hein :v                  
    try{
        $requeteCadreur->execute([$video]);
        $listeCadreurs = $requeteCadreur->fetchAll();
        $connexion = null;
        return $listeCadreurs;
    }
    catch(Exception e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

/**
* @Nom : getResponsableSon
* @Description : renvoie la liste des responsables sons d'une vidéo
* @video : id de la vidéo concernée
 */

 function getResponsableSon($video)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $requeteResponsable = $connexion->prepare('SELECT nom, prenom 
    FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve
    WHERE idVideo = ? AND idRole = 3');                                                 // #RISQUE : j'ai mis 3 en estimant que ce serait l'id des responsablesSons mais bon hein :v 
    try{
        $requeteResponsable->execute([$video]);
        $listeResponsable = $requeteResponsable->fetchAll();
        $connexion = null;
        return $listeResponsable;
    }
    catch(Exception e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

 /**
* @Nom : getRealisateur
* @Description : renvoie la liste des réalisateurs d'une vidéo
* @video : id de la vidéo concernée
 */

 function getProjet($video)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $requeteProf = $connexion->prepare('SELECT mtd_edito_projet 
    FROM Media
    WHERE idVideo = ?');                                                 // #RISQUE : j'ai mis 3 en estimant que ce serait l'id des responsablesSons mais bon hein :v 
    try{
        $requeteProf->execute([$video]);
        $projet = $requeteProf->fetchAll();
        $connexion = null;
        return $projet;
    }
    catch(Exception e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

?>