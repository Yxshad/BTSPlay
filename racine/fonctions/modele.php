<?php
/**
 *  @NOM : methodes.php
 *  @DESCRIPTION : Fonctions php liées aux manipulations (insertions, suppressions...) sur la base de données
 *  @CREATION : 19/12/2024
 *  @DERNIERE MODIFICATION : 08/01/2025 - XXHXX
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
        $dsn = "mysql:host=" . BD_HOST . ";port=" . BD_PORT . ";dbname=" . BD_NAME;
        $mysqlClient = new PDO($dsn, BD_USER, BD_PASSWORD);
        $mysqlClient->beginTransaction(); // Ne pas réassigner à $connexion ici !
        $mysqlClient->exec("SET NAMES 'utf8mb4'");
        return $mysqlClient;
    }
    catch (Exception $e)
    {
        die('Erreur : ' . $e->getMessage());
    }
}

/**#########################################
 *
 *           INSERTIONS DANS LA BD
 * 
 */########################################

 /**
  * @Nom : insertionDonneesTechniques
  * @Description : crée la vidéo en base de données et insère les métadonnées techniques associées 
  * @$listeMetadonnees : liste des metadonnées techniques à insérer
  */
  function insertionDonneesTechniques($listeMetadonnees)
  {
      $connexion = connexionBD();
      // Construction de la requête
      $videoAAjouter = $connexion->prepare(
          'INSERT INTO Media (
              URI_NAS_PAD, 
              URI_NAS_ARCH, 
              URI_NAS_MPEG,
              mtd_tech_titre,
              mtd_tech_duree,
              mtd_tech_resolution,
              mtd_tech_fps,
              mtd_tech_format
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
      );
      try {
        if(!getVideo($listeMetadonnees[MTD_URI_STOCKAGE_LOCAL]))
          // Ajout des paramètres
          $videoAAjouter->execute([
            $listeMetadonnees[MTD_URI_NAS_PAD],
            $listeMetadonnees[MTD_URI_NAS_ARCH],
              $listeMetadonnees[MTD_URI_STOCKAGE_LOCAL],
              $listeMetadonnees[MTD_TITRE],
              $listeMetadonnees[MTD_DUREE],
              $listeMetadonnees[MTD_RESOLUTION],
              $listeMetadonnees[MTD_FPS],
              $listeMetadonnees[MTD_FORMAT]
          ]);
          $connexion->commit();
          $connexion = null;
      } catch (Exception $e) {
          $connexion->rollback();
          $connexion = null;
      }
  }

/**
* @Nom : insertionProfesseur
* @Description : gère l'insertion des professeurs et lie le professeur à un/des projets
* @nomProf et prenomProf : assez explicite, il serait préférable de renvoyer les deux individuellement pour faire les comparaisons en bd plus facilement mais j'arrangerai ça plus tard au pire
 */
function insertionProfesseur($prof)
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
            $connexion = null;
        }
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
}

/**
 * @Nom : insertionProjet
 * @Description : gère l'insertion des projets et lie le projet à un/des médias
 */
function insertionProjet($projet)
{
    $connexion = connexionBD();     
    try{
        if(!getProjet($projet))
        {
            $ajoutProjet = $connexion->prepare('INSERT INTO Projet (Intitule) VALUES (?)');
            $ajoutProjet->execute([$projet]); 
            $connexion->commit();
        }
        else {
            $connexion = null;
        }
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
}

/**
* @Nom : insertionEtudiant
* @Description : gère l'insertion des professeurs et lie le professeur à un/des projets
* @nomProf et prenomProf : assez explicite, il serait préférable de renvoyer les deux individuellement pour faire les comparaisons en bd plus facilement mais j'arrangerai ça plus tard au pire
 */
function insertionEtudiant($etudiant)
{
    if ($etudiant != "") {
        $connexion = connexionBD();  
        try{
            $verif = $connexion->prepare('INSERT INTO ETUDIANT (nomComplet) VALUES (?)');
            $etudiantAAjouter= $verif->execute([
                $etudiant]);          
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();
            $connexion = null;
        }
    }
}

/**
* @Nom : insertionDonneesEditoriales
* @Description : insère les métadonnées éditoriales sur la vidéo concernée
 */
 function insertionDonneesEditoriales($videoTitre, $listeEdito)
 {
    $connexion = connexionBD();
    try{
        $idVid = getVideo($videoTitre); //Permet d'obtenir l'id exact de la vidéo à partir du titre 
        insertionProfesseur($listeEdito['prof']);
        assignerProfReferent($idVid, $listeEdito['prof']);
        assignerCadreur($idVid, $listeEdito['cadreurs']);
        assignerResponsable($idVid, $listeEdito['responsables']);
        assignerRealisateur($idVid, $listeEdito['realisateurs']);
        insertionProjet($listeEdito['projet']);
        assignerProjet($idVid, $listeEdito['projet']);
        assignerPromotion($idVid, $listeEdito['promotion']);
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
 }

/**#########################################
 *
 *           ASSIGNER DANS LA BD
 * 
 */########################################

/**
 * assignerProjet
 * Permet d'assigner un projet au média
 * idVideo : l'id de la vidéo à laquelle on assigne le projet
 * projet : libelle du projet
 */
 function assignerProjet($idVideo, $projet) {
    $connexion = connexionBD();
    try {
        $idProjet = getProjet($projet);
        if (!$idProjet) {
            insertionProjet($projet);
            $idProjet = getProjet($projet);
        }
        $setIDProjet = $connexion->prepare('UPDATE media 
                                          SET projet = ?
                                          WHERE id = ?');
        $setIDProjet->execute([
            $idProjet,
            $idVideo
        ]);
        $connexion->commit();
        $connexion = null;
    } catch (Exception $e) {
        if ($connexion) {
            $connexion->rollback();
        }
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
    $lastSpacePos = strrpos($prof, ' ');
    // Vérifier si un espace existe
    if ($lastSpacePos !== false) {
        // Séparer le prénom du nom
        $profNom = substr($prof, 0, $lastSpacePos);  
        $profPrenom = substr($prof, $lastSpacePos + 1);  
        $connexion = connexionBD();
        try {
            $idProf = getProfId($profNom, $profPrenom);

            if(!$idProf)
            {
                $setIDProf = $connexion->prepare('UPDATE media 
                SET professeurReferent = NULL
                WHERE id = ?');
                $setIDProf->execute([
                $idVideo
                ]);
                $connexion = null;
            }
            else {
                // Mettre à jour la table `media` avec l'ID du professeur
            $setIDProf = $connexion->prepare('UPDATE media 
            SET professeurReferent = ?
            WHERE id = ?');
            $setIDProf->execute([
            $idProf,
            $idVideo
            ]);
            $connexion->commit();
            $connexion = null;
            }
        } catch (Exception $e) {
            if ($connexion) {
                $connexion->rollback();
            }
            $connexion = null;
        }
    }
}

/**
 * assignerCadreur
 * Permet d'assigner un ou des cadreurs au projet
 * idVideo : l'id de la vidéo à laquelle on assigne le professeur
 * listeCadreurs : supposément une chaîne de caractères contenant tous les cadreurs
 */
 function assignerCadreur($idVideo, $listeCadreurs){

    if ($listeCadreurs != "") {
        $connexion = connexionBD();
        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les cadreurs
        $listeCadreurs = trim(preg_replace('/\s*,\s*/', ', ', $listeCadreurs));
        $tabCadreur = explode(', ', $listeCadreurs);
        try{
            //On efface toutes les données cadreurs pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $cadreur = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $cadreur->execute([$idVideo, 1]);            
            for ($i=0; $i < count($tabCadreur); $i++) { 
                if(!etudiantInBD($tabCadreur[$i]))
                {
                    insertionEtudiant($tabCadreur[$i]);
                }
                // Récupérer l'ID de l'élève
                $idEtudiant = getIdEtudiant($tabCadreur[$i]);
                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEtudiant, 1]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

/**
 * assignerResponsable
 * Permet d'assigner un ou des responsables sons
 * idVideo : l'id de la vidéo à laquelle on assigne l'élève
 * listeResponsable : supposément une chaîne de caractères contenant tous les cadreurs
 */
 function assignerResponsable($idVideo, $listeResponsable){

    if ($listeResponsable != "") {
        $connexion = connexionBD();
        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les responsables son
        $listeResponsable = trim(preg_replace('/\s*,\s*/', ', ', $listeResponsable));
        $tabResponsable = explode(', ', $listeResponsable);
        try{
            //On efface toutes les données responsables son pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $cadreur = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $cadreur->execute([$idVideo, 3]);
            for ($i=0; $i < count($tabResponsable); $i++) { 
                if(!etudiantInBD($tabResponsable[$i]))
                {
                    insertionEtudiant($tabResponsable[$i]);
                }
                // Récupérer l'ID de l'élève
                $idEtudiant = getIdEtudiant($tabResponsable[$i]);
                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEtudiant, 3]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

 /**
 * assignerRealisateur
 * Permet d'assigner un ou des réalisateurs
 * idVideo : l'id de la vidéo à laquelle on assigne l'élève
 * listeRealisateur : supposément une chaîne de caractères contenant tous les cadreurs
 */
 function assignerRealisateur($idVideo, $listeRealisateurs){
    if ($listeRealisateurs != "") {
        $connexion = connexionBD();
        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les cadreurs
        $listeRealisateurs = trim(preg_replace('/\s*,\s*/', ', ', $listeRealisateurs));
        $tabRealisateur = explode(', ', $listeRealisateurs);
        try{
            //On efface toutes les données cadreurs pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $cadreur = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $cadreur->execute([$idVideo, 2]);
            for ($i=0; $i < count($tabRealisateur); $i++) { 
                if(!etudiantInBD($tabRealisateur[$i]))
                {
                    insertionEtudiant($tabRealisateur[$i]);
                }
                // Récupérer l'ID de l'élève
                $idEtudiant = getIdEtudiant($tabRealisateur[$i]);
                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEtudiant, 2]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

 /**
 * assignerPromotion
 * @Assigne la promotion durant laquelle la vidéo a été produite
 */
 function assignerPromotion($idVid, $valPromo)
 {
    $connexion = connexionBD();  
    try{
        $cadreur = $connexion->prepare('UPDATE Media SET promotion = ? WHERE id = ?');
        $cadreur->execute([$valPromo, $idVid]);
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
 }

/** #################################################
 * 
 *    FONCTIONS "GETTERS" DE RECHERCHE SUR LES TABLES
 * 
 *####################################################*/

 /**
* @Nom : getProjet
* @Description : renvoie le projet lié à une vidéo
* @projet : nom du projet
 */
 function getProjet($projet)
 {
    $connexion = connexionBD();
    $requeteProj = $connexion->prepare('SELECT * 
    FROM Projet
    WHERE Projet.intitule = ?');                                                 
    try{
        $requeteProj->execute([$projet]);
        $projet = $requeteProj->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        if ($projet) {
            return $projet['id'];
           } 
           else {
               return False;
           }
    }
    catch(Exception $e)
    {
        $connexion = null;
    }
 }

/**
 * getIdEtudiant
 * renvoie l'id d'un élève
 * Ce code est catastrophique bref
 */
 function getIdEtudiant($etudiant)
 {
    $connexion = connexionBD();
    $requeteEtudiant = $connexion->prepare('SELECT id 
    FROM Etudiant
    WHERE nomComplet = ?');                                                 
    try{
        $requeteEtudiant->execute([$etudiant]);
        $etudiantCherche = $requeteEtudiant->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return $etudiantCherche['id'];
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
 }

 /**
 * getVideo
 * renvoie l'id d'une vidéo
 * $path : chemin NAS MPEG de la vidéo
 */
function getVideo($path)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT id 
   FROM Media
   WHERE URI_NAS_MPEG = ?');                                                 
   try{
       $requeteVid->execute([$path]);
       $vidID = $requeteVid->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       if ($vidID) {
        return $vidID['id'];
       } 
       else {
           return false;
       }   
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

function getInfosVideo($idVideo)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT * 
   FROM Media
   WHERE id = ?');                                                 
   try{
       $requeteVid->execute([$idVideo]);
       $infosVideo = $requeteVid->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       if ($infosVideo) {
        return $infosVideo;
       } 
       else {
           return false;
       }
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

/**
 * @getUriNASetTitreMPEGEtId
 * @return array|false Renvoie la liste des URI NAS MPEG + titre + id ou false en cas d'échec
 */
function getUriNASetTitreMPEGEtId($nbMaxVideo) {
    try {
        $connexion = connexionBD();
        $requeteVid = $connexion->prepare('SELECT id, URI_NAS_MPEG, mtd_tech_titre FROM Media LIMIT :nbVideo');
        $requeteVid->bindParam(":nbVideo", $nbMaxVideo,PDO::PARAM_INT);
        $requeteVid->execute();
        $resultat = $requeteVid->fetchAll(PDO::FETCH_ASSOC);
        $connexion = null;
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau des URI_NAS_MPEG
        } else {
            return false; // Aucun résultat trouvé
        }
    } catch (Exception $e) {
        ajouterLog(LOG_CRITICAL, "Erreur SQL: " . $e->getMessage());
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
        error_log('Erreur dans getUriNASetTitreMPEG: ' . $e->getMessage());
        return false;
    }
}

/**
 * getProfId
 * renvoie l'id d'un prof
 */
function getProfId($profNom, $profPrenom)
{
   $connexion = connexionBD();
   $requeteProf = $connexion->prepare('SELECT identifiant 
   FROM Professeur
   WHERE nom = ? AND prenom = ?');                                                 
   try{
       $requeteProf->execute([$profNom, $profPrenom]);
       $profCherche = $requeteProf->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       return $profCherche['identifiant'] ?? null;
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

function getProjetIntitule($idProjet){
    $connexion = connexionBD();
    $requeteProjet = $connexion->prepare('SELECT intitule 
    FROM Projet
    WHERE id = ?');                                                 
    try{
        $requeteProjet->execute([$idProjet]);
        $projet = $requeteProjet->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return $projet ? $projet["intitule"] : "";
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
}

function getProfNomPrenom($identifiant)
{
   $connexion = connexionBD();
   $requeteProf = $connexion->prepare('SELECT nom, prenom 
   FROM Professeur
   WHERE identifiant = ?');                                                 
   try{
       $requeteProf->execute([$identifiant]);
       $profCherche = $requeteProf->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       return $profCherche ? [$profCherche['nom'], $profCherche['prenom']] : ["", ""];
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

function getAllProfesseurs(){
    $connexion = connexionBD();
   $requeteProf = $connexion->prepare('SELECT nom, prenom 
   FROM Professeur');                                                 
   try{
       $requeteProf->execute();
       $professeurs = $requeteProf->fetchAll(PDO::FETCH_ASSOC);
       $connexion = null;
       return $professeurs;
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

function getParticipants($idVid) {
    $connexion = connexionBD();
    
    // Requête pour le réalisateur
    $requeteRealisateur = $connexion->prepare('SELECT Etudiant.nomComplet FROM Etudiant JOIN Participer ON Etudiant.id = Participer.idEtudiant WHERE Participer.idMedia = ? AND Participer.idRole = ?');
    $requeteRealisateur->execute([$idVid, 2]);
    $realisateur = $requeteRealisateur->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour le cadreur
    $requeteCadreur = $connexion->prepare('SELECT Etudiant.nomComplet FROM Etudiant JOIN Participer ON Etudiant.id = Participer.idEtudiant WHERE Participer.idMedia = ? AND Participer.idRole = ?');
    $requeteCadreur->execute([$idVid, 1]);
    $cadreur = $requeteCadreur->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour le son
    $requeteSon = $connexion->prepare('SELECT Etudiant.nomComplet FROM Etudiant JOIN Participer ON Etudiant.id = Participer.idEtudiant WHERE Participer.idMedia = ? AND Participer.idRole = ?');
    $requeteSon->execute([$idVid, 3]);
    $son = $requeteSon->fetchAll(PDO::FETCH_ASSOC);

    // Fermeture de la connexion
    $connexion = null;

    // #RISQUE traitement des variables si plusieurs personnes ont le même rôle
    return [
        $realisateur ? $realisateur[0]["nomComplet"] : "",
        $cadreur ? $cadreur[0]["nomComplet"] : "",
        $son ? $son[0]["nomComplet"] : ""
    ];
}

/**###########################
  *     TRUE / FALSE
  ############################*/

/** @etudiantInBD
 *  Renvoie un boléen si l'élève est dans la base de données
 */
function etudiantInBD($etudiant)
{
    $connexion = connexionBD(); 
    $requeteEtudiant = $connexion->prepare('SELECT 1 
    FROM Etudiant
    WHERE etudiant.nomComplet = ?');                                                 
    try{
        $requeteEtudiant->execute([$etudiant]);
        $resultatEtudiant = $requeteEtudiant->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        if(!$resultatEtudiant){
            return False;
        }
        else {
            return True;
        }
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
}

/** profInBD
 *  Renvoie un booléen si le professeur est dans la base
 * 
 */
function profInBD($prof){
    $connexion = connexionBD();                     
    try{
        $requeteProf = $connexion->prepare('SELECT 1 
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
        $connexion->rollback();
        $connexion = null;
    }
}

/*
*  fonction fetchAll couteau suisse
*  ex: fetchAll("SELECT * FROM Media"); va renvoyer toutes les info des vidéos
*  NE PAS UTILISER SI PROSSIBLE. TOUJOURS PREFERER LA CREATION D'UNE NOUVELLE FONCTION
*/
function fetchAll($sql){
    try {
        $connexion = connexionBD();
        $requete = $connexion->prepare($sql);
        $requete->execute();
        $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
        $connexion = null;
        if (!empty($resultat)) {
            return $resultat;
        } else {
            return false;
        }
    } catch (Exception $e) {
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
        error_log('Erreur dans getUriNASetTitreMPEG: ' . $e->getMessage());
        return false;
    }
}

/**
 * verifierPresenceVideoStockageLocal
 * renvoie 1 si une vidéo existe
 * $cheminFichier : chemin de l'espace local de la vidéo
 * $nomFichier : nom du fichier
 */
function verifierPresenceVideoStockageLocal($cheminFichier, $nomFichier)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT 1
   FROM Media
   WHERE URI_NAS_MPEG = ?
   AND mtd_tech_titre = ?');                                                 
   try{
       $requeteVid->execute([$cheminFichier, $nomFichier]);
       $vidPresente = $requeteVid->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return (bool)$vidPresente;
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

/**
 * Fonction qui regarde si un prof existe pour un couple login/mdp passé en paramètre
 * renvoie le rôle si trouvé, false sinon
 */
function connexionProfesseur($loginUser, $passwordUser){
   $connexion = connexionBD();                     
   try{
        $requeteConnexion = $connexion->prepare('SELECT role
        FROM Professeur P
        WHERE P.identifiant = ?
        AND P.motdepasse = ?');   
        $requeteConnexion->execute([$loginUser, $passwordUser]);
        $resultatRequeteConnexion = $requeteConnexion->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return $resultatRequeteConnexion;
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

?>