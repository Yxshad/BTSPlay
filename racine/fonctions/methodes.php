<?php
/**
 *  @NOM : methodes.php
 *  @DESCRIPTION : Fonctions php liées aux manipulations (insertions, suppressions...) sur la base de données
 *  @CREATION : 19/12/2024
 *  @DERNIERE MODIFICATION : 29/12/2024 - XXHXX
 *  @COLLABORATEURS : Elsa Lavergne
 */

 require '../ressources/constantes.php';

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
        echo 'Caught exception: ',  $e->getMessage(), "\n";
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
        $mysqlClient = new PDO('mysql:host=mysql_BTSPlay;port=3306:3306;dbname=mydatabase', 'myuser', 'mypassword');
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
      $connexion = connexionBD(); // Connexion à la BD
      $connexion->beginTransaction(); // Démarrage de la transaction
      
      // Construction de la requête
      $videoAAjouter = $connexion->prepare(
          'INSERT INTO Media (
              URI_RACINE_NAS_PAD, 
              URI_RACINE_NAS_ARCH, 
              URI_RACINE_NAS_MPEG,
              mtd_tech_titre,
              mtd_tech_duree,
              mtd_tech_resolution,
              mtd_tech_fps,
              mtd_tech_format
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
      );
  
      try {
          // Ajout des paramètres
          $videoAAjouter->execute([
              URI_RACINE_NAS_PAD, 
              URI_RACINE_NAS_ARCH, 
              URI_RACINE_NAS_MPEG,
              $listeMetadonnees['Titre'],
              $listeMetadonnees['Duree'],
              $listeMetadonnees['Resolution'],
              $listeMetadonnees['FPS'],
              $listeMetadonnees['Format']
          ]);
          $connexion->commit(); // Valider la transaction
          $connexion = null; // Fermeture de la connexion
      } catch (Exception $e) {
          echo 'Caught exception: ',  $e->getMessage(), "\n";
          $connexion->rollback(); // Annuler la transaction
          $connexion = null;
      }
  }

/** NOM : Alejandro Boufarti 
 * Prenom : 
*/

/**
* @Nom : insertionProfesseur
* @Description : gère l'insertion des professeurs et lie le professeur à un/des projets
* @nomProf et prenomProf : assez explicite, il serait préférable de renvoyer les deux individuellement pour faire les comparaisons en bd plus facilement mais j'arrangerai ça plus tard au pire
 */
function insertionProfesseur($video, $prof)
{
    $connexion = connexionBD();                     
    try{
        if(!profInBD($prof))
        {
            $ajoutProfesseur = $connexion->prepare('INSERT INTO Professeur (nomComplet) VALUES (?)');
            $ajoutProfesseur->execute([$prof]); 
            $connexion->commit();
        }
        else {
            //Sinon rien à faire
            $connexion = null;
        }
        
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
}

/**
 * assignerProfReferent
 * Permet d'assigner un professeur référent au projet
 * idVideo : l'id de la vidéo à laquelle on assigne le professeur
 * prof : nomComplet du professeur
 */

 function assignerProfReferent($idVideo, $prof) {
    $connexion = connexionBD(); // Connexion à la BD
    $connexion->beginTransaction(); // Démarrage de la transaction
    try {
        // Rechercher le professeur par nom complet
        $profAAjouter = $connexion->prepare('SELECT id FROM Professeur WHERE nomComplet = ?');
        $profAAjouter->execute([$prof]); 
        $profAjoute = $profAAjouter->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif

        // Debugging
        var_dump($profAjoute, "||||", $idVideo);

        // Vérifiez si le professeur existe
        if (!$profAjoute || !isset($profAjoute['id'])) {
            throw new Exception("Professeur non trouvé ou ID manquant pour : $prof");
        }

        // Debug : Vérifier les données avant l'UPDATE
        var_dump("Professeur trouvé :", $profAjoute['id'], "ID vidéo :", $idVideo);

        // Vérification des types (éviter l'erreur Array to string conversion)
        if (!is_scalar($profAjoute['id']) || !is_scalar($idVideo)) {
            throw new Exception("Les données fournies ne sont pas scalaires (idProf ou idVideo).");
        }

        // Mettre à jour la table `media` avec l'ID du professeur
        $setIDProf = $connexion->prepare('UPDATE media 
                                          SET professeurReferent = ?
                                          WHERE id = ?');
        
        // Exécution de la mise à jour
        $setIDProf->execute([
            $profAjoute['id'], // Utilisation de l'ID du professeur récupéré
            $idVideo
        ]);

        // Commit de la transaction
        $connexion->commit();
        $connexion = null;

        echo "Professeur référent assigné avec succès.\n";
    } catch (Exception $e) {
        // Gestion des erreurs
        echo 'Erreur : ',  $e->getMessage(), "\n";
        if ($connexion) {
            $connexion->rollback(); // Annule la transaction en cas d'erreur
        }
        $connexion = null;
    }
}



/**
* @Nom : insertionEleve
* @Description : gère l'insertion des professeurs et lie le professeur à un/des projets
* @nomProf et prenomProf : assez explicite, il serait préférable de renvoyer les deux individuellement pour faire les comparaisons en bd plus facilement mais j'arrangerai ça plus tard au pire
 */
function insertionEleve($video, $eleve)
{
    $connexion = connexionBD();                     
    try{
        $verif = $connexion->prepare('SELECT * from Eleve where nomComplet = ?');
        $eleveAAjouter= $verif->execute([
            $eleve]);          
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
}

/**
 * assignerCadreur
 * Permet d'assigner un ou des cadreurs au projet
 * idVideo : l'id de la vidéo à laquelle on assigne le professeur
 * listeCadreurs : supposément une chaîne de caractères contenant tous les cadreurs
 */

 function assignerCadreur($idVideo, $listeCadreurs){
    $connexion = connexionBD();
    // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
    $tabCadreur = preg_split('/,\s/', $listeCadreurs);
    console.log($tabCadreur);
    try{
        for ($i=0; $i < count($tabCadreur); $i++) { 
            if(!eleveInBD($tabCadreur[i]))
            {
                insertionEleve($idVideo, $tabCadreur[i]);
                $idEleve = getIdEleve($listeCadreur[i]);
                $cadreur = $connexion->prepare('INSERT INTO Participer (idVideo, idEleve, idRole) 
                SET idVideo = ?,
                idEleve = ?,
                idRole = ?');
                $cadreur -> execute([$idVideo, $idEleve, 1]);
            }
            else {
                $cadreur = $connexion->prepare('UPDATE Participer 
                SET idEleve = ?,
                WHERE idVideo = ? AND idRole = ?');
                $cadreur -> execute([$idEleve, $idVideo, 1]);
            }
            
        }
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        $connexion->rollback();             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

/**
* @Nom : insertionDonneesEditoriales
* @Description : insère les métadonnées éditoriales sur la vidéo concernée
 */

 function insertionDonneesEditoriales($videoTitre, $listeEdito)
 {
    $connexion = connexionBD(); // Connexion à la BD
    $connexion->beginTransaction(); // Démarrage de la transaction
    try{
        $idVid = getVideo($videoTitre);           //Permet d'obtenir l'id exact de la vidéo à partir du titre 
        insertionProfesseur($idVid, $listeEdito['prof']);
        assignerProfReferent($idVid, $listeEdito['prof']);
        assignerCadreur($video, $listeEdito['CADREURS']);
        

    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
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
        $listeReal = $requeteReal->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        
        $connexion = null;
        return $listeReal;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
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
        $listeCadreurs = $requeteCadreur->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        $connexion = null;
        return $listeCadreurs;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
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
        $listeResponsable = $requeteResponsable->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatifs
        $connexion = null;
        return $listeResponsable;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
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
        $projet = $requeteProj->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        $connexion = null;
        return $projet;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

/**
 * getIdEleve
 * renvoie l'id d'un élève
 * Ce code est catastrophique bref
 */
 function getIdEleve($eleve)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $requeteProj = $connexion->prepare('SELECT id 
    FROM Eleve
    WHERE nomComplet = ?');                                                 
    try{
        $requeteProj->execute([$video]);
        $eleveCherche = $requeteProj->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        $connexion = null;
        return $eleveCherche['id'];
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

 /**
 * getVideo
 * renvoie l'id d'une vidéo
 * $titre : nom de la vidéo
 */
function getVideo($videoTitre)
{
   $connexion = connexionBD();                                                         // Connexion à la BD
   $requeteVid = $connexion->prepare('SELECT id 
   FROM Media
   WHERE mtd_tech_titre = ?');                                                 
   try{
       $requeteVid->execute([$videoTitre]);
       $vidID = $requeteVid->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
       var_dump("VIDEO ID :",$vidID['id']);
       $connexion = null;
       if ($vidID) {
       } else {
           echo "Aucune vidéo trouvée pour le titre donné.\n";
       }
       return $vidID['id'];
   }
   catch(Exception $e)
   {
       echo 'Caught exception: ',  $e->getMessage(), "\n";
       $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
       $connexion = null;
   }
}

/**###########################
  *     TRUE / FALSE
  ############################*/

  /**   eleveInBD
   *  Renvoie un boléen si l'élève est dans la base de données
   */
    function eleveInBD($eleve)
    {
        $connexion = connexionBD(); 
        $requeteEleve = $connexion->prepare('SELECT nomComplet 
        FROM Eleve
        WHERE eleve.nomComplet = ?');                                                 
        try{
            $requeteEleve->execute([$eleve]);
            $resultatEleve = $requeteEleve->fetchAll();
            $connexion = null;
            if(count($resultatEleve) == 0 ){
                return False;
            }
            else {
                return True;
            }
        }
        catch(Exception $e)
        {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
            $connexion = null;
        }
    }

/** profInBD
 *  Renvoie un booléen si le professeur est dans la base
 * 
 */

 function profInBD($prof)
 {
    $connexion = connexionBD();                     
    try{
        $requeteProf = $connexion->prepare('SELECT nomComplet 
        FROM Professeur
        WHERE Professeur.nomComplet = ?');   
        $requeteProf->execute([$prof]);
            $resultatProf = $requeteProf->fetchAll();
            $connexion = null;
            if(count($resultatProf) == 0 ){
                return False;
            }
            else {
                return True;
            }
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

 /**###########################
  *     TESTS
  ############################*/

 $liste = ['MTD_TITRE' =>  "23_6h_JIN_Fermetur.mxf",
 'MTD_FPS' => 25,
 'MTD_RESOLUTION' => "1920x1080",
 'MTD_DUREE' => "00:00:15",
 'MTD_FORMAT' => "16:9"
 ];

 $liste2 = ['Titre' =>  "23_6h_JIN_Fermetur.mxf",
                'FPS' => 25,
                'Resolution' => "1920x1080",
                'Duree' => "00:00:15",
                'Format' => "16:9"
                ];

$listeEditoriale = ['prof' => 'Michael Jackson',
                    'cadreurs' => 'Michael Jackson, Lyxandre TktJeChercheLeNom',
                    'projet' => 'Projet de Fin dannée 2024'];


//insertionDonneesTechniques($liste2);
//var_dump(eleveInBD('Michael Jackson'));
//var_dump(profInBD('Michael Jackson'));



insertionDonneesEditoriales("23_6h_JIN_Fermetur.mxf", $listeEditoriale)


?>