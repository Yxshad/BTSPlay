<?php
/**
 *  @NOM : methodes.php
 *  @DESCRIPTION : Fonctions php liées aux manipulations (insertions, suppressions...) sur la base de données
 *  @CREATION : 19/12/2024
 *  @DERNIERE MODIFICATION : 29/12/2024 - XXHXX
 *  @COLLABORATEURS : Elsa Lavergne
 */

 require '../ressources/constantes.php'

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
* @Nom : insertionProfesseur
* @Description : gère l'insertion des professeurs et lie le professeur à un/des projets
* @nomProf et prenomProf : assez explicite, il serait préférable de renvoyer les deux individuellement pour faire les comparaisons en bd plus facilement mais j'arrangerai ça plus tard au pire
 */
function insertionProfesseur($video, $nomProf, $prenomProf)
{
    $connexion = connexionBD();                     
    try{
        $verif = $connexion->prepare('SELECT * from Professeur where nom = ? and prenom=?')  
        $profAAjouter= $verif->execute([
            $nomProf, $prenomProf]); 
        
        //ON VERIFIE SI ON A DEJA LE PROFESSEUR EN BD

        if ($profAAjouter.length == 0) {
            $verif = $connexion->prepare('INSERT INTO Professeur (nom, prenom) VALUES (?, ?)')  
            $profAAjouter->execute([$nomProf, $prenomProf]); 
            $connexion->commit();
        }

        //C'est dégueulasse, il est 22h10 un jeudi je modifierai ça PLUS TARD

        $verif = $connexion->prepare('SELECT * from Professeur where nom = ? and prenom=?')  
        $profAAjouter= $verif->execute([
            $nomProf, $prenomProf]); 
        
        $setIDProf = $connexion->prepare('UPDATE media 
        SET professeurReferent = ?,
        WHERE id = ? ');      
        $setIDProf->execute([
            $profAAjouter[id]
            $video]);          
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
* @Nom : insertionDonneesEditoriales
* @Description : insère les métadonnées éditoriales sur la vidéo concernée
 */

 function insertionDonneesEditoriales($video, $listeEdito)
 {
    insertionProfesseur($video, $listeEdito[NOMPROF], $listeEdito[PRENOMPROF]);

    try{
        
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
* @Nom : getProjet
* @Description : renvoie le projet lié à une vidéo
* @video : id de la vidéo concernée
 */

 function getProjet($video)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $requeteProj = $connexion->prepare('SELECT libelle 
    FROM Projet JOIN Media ON Projet.id = Media.projet
    WHERE Media.id = ?');                                                 
    try{
        $requeteProj->execute([$video]);
        $projet = $requeteProj->fetchAll();
        $connexion = null;
        return $projet;
    }
    catch(Exception e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }


 /* TESTS RAPIDES */
 $liste = [MTD_TITRE =>  "23_6h_JIN_Fermetur.mxf",
 MTD_FPS => 25,
 MTD_RESOLUTION => "1920x1080",
 MTD_DUREE => "00:00:15",
 MTD_FORMAT => "16:9"
 ];

 $liste2 = ['Titre' =>  "23_6h_JIN_Fermetur.mxf",
                'FPS' => 25,
                'Durée' => "1920x1080",
                'Resolution' => "00:00:15",
                'Format' => "16:9"
                ];

insertionDonneesTechniques($liste);

?>