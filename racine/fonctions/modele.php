<?php

 /**
 * \file modele.php
 * \version 1.1
 * \brief Fonctions php liées aux manipulations (insertions, suppressions...) sur la base de données
 * \author Elsa Lavergne
 * */

/**
* \fn connexionBD
* \brief Permet de se connecter en base de données et de checker au passage s'il y a eu des erreurs de connexion
* \return mysqlClient - Objet de connexion à la base de données
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la connexion à la BD : " . $e->getMessage());
        die('Erreur : ' . $e->getMessage());
    }
}

#########################################
# 
#        INSERTIONS DANS LA BD
#
 ########################################

 /**
  * \fn insertionDonneesTechniques($listeMetadonnees)
  * \brief crée la vidéo en base de données et insère les métadonnées techniques associées 
  * \param listeMetadonnees - liste des metadonnées techniques à insérer
  */
  function insertionDonneesTechniques($listeMetadonnees)
  {
    $connexion = connexionBD();
    // Construction de la requête
    $videoAAjouter = $connexion->prepare(
        'INSERT INTO Media (
            URI_NAS_PAD, 
            URI_NAS_ARCH, 
            URI_STOCKAGE_LOCAL,
            mtd_tech_titre,
            mtd_tech_duree,
            mtd_tech_resolution,
            mtd_tech_fps,
            mtd_tech_format
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    try {
    if(!getVideo($listeMetadonnees[MTD_URI_STOCKAGE_LOCAL], $listeMetadonnees[MTD_TITRE]))
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de l'insertion des données de la vidéo " . $listeMetadonnees[MTD_TITRE] .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
  }


/**
  * \fn insertionProjet($projet)
  * \brief gère l'insertion des projets si celui-ci n'est pas déjà dans la bd
  * \param projet - Projet que l'on veut insérer
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de l'insertion du projet " . $projet .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
}

/**
  * \fn insertionEtudiant($etudiant)
  * \brief Gère l'insertion des étudiants et lie le professeur à un/des projets
  * \param etudiant - Nom et prenom de l'étudiant
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
            ajouterLog(LOG_CRITICAL, "Erreur lors de l'insertion de l'étudiant " . $etudiant .
            " : " . $e->getMessage());
            $connexion->rollback();
            $connexion = null;
        }
    }
}

#########################################
 
#          ASSIGNER DANS LA BD
  
 ########################################

/**
 * \fn assignerProjet($idVideo, $projet)
 * \brief Permet d'assigner un projet au média
 * \param idVideo - l'id de la vidéo à laquelle on assigne le projet
 * \param projet - libelle du projet
 */
 function assignerProjet($idVideo, $projet) {
    $connexion = connexionBD();
    try {
        $idProjet = getProjet($projet);
        if (!$idProjet && $projet!="") {
            insertionProjet($projet);
            $idProjet = getProjet($projet);
        }
        $setIDProjet = $connexion->prepare('UPDATE media 
                                          SET projet = ?, date_modification = CURRENT_TIMESTAMP
                                          WHERE id = ?');
        $setIDProjet->execute([
            $idProjet,
            $idVideo
        ]);
        $connexion->commit();
        $connexion = null;
    } catch (Exception $e) {
        ajouterLog(LOG_CRITICAL, "Erreur lors de l'assignation du projet " . $projet . " à la vidéo " . $idVideo .
        " : " . $e->getMessage());
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
    }
}

/**
 * \fn assignerDescription($idVideo, $description)
 * \brief Permet d'assigner une description au média
 * \param idVideo - l'id de la vidéo à laquelle on assigne la description
 * \param description - description de la vidéo
 */
function assignerDescription($idVid, $description)
{
   $connexion = connexionBD();  
   try{
       $requete = $connexion->prepare('UPDATE Media SET description = ?, date_modification = CURRENT_TIMESTAMP WHERE id = ?');
       $requete->execute([$description, $idVid]);
       $connexion->commit();  
       $connexion = null;
   }
   catch(Exception $e)
   {
       ajouterLog(LOG_CRITICAL, "Erreur lors de l'assignation de la description " . $description . " à la vidéo " . $idVideo .
       " : " . $e->getMessage());
       $connexion->rollback();
       $connexion = null;
   }
}

 /**
 * \fn assignerProfReferent($idVideo, $prof)
 * \brief Permet d'assigner un professeur référent au projet
 * \param idVideo - l'id de la vidéo à laquelle on assigne le professeur
 * \param prof - nomComplet du professeur
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
                SET professeurReferent = NULL, date_modification = CURRENT_TIMESTAMP
                WHERE id = ?');
                $setIDProf->execute([
                $idVideo
                ]);
                $connexion = null;
            }
            else {
                // Mettre à jour la table `media` avec l'ID du professeur
            $setIDProf = $connexion->prepare('UPDATE media 
            SET professeurReferent = ?, date_modification = CURRENT_TIMESTAMP
            WHERE id = ?');
            $setIDProf->execute([
            $idProf,
            $idVideo
            ]);
            $connexion->commit();
            $connexion = null;
            }
        } catch (Exception $e) {
            ajouterLog(LOG_CRITICAL, "Erreur lors de l'assignation du professeur " . $prof . " à la vidéo " . $idVideo .
            " : " . $e->getMessage());
            if ($connexion) {
                $connexion->rollback();
            }
            $connexion = null;
        }
    }
}


/**
 * \fn assignerRole($idVideo, $listeCadreurs)
 * \brief Permet d'assigner un ou des cadreurs au projet
 * \param idVideo - l'id de la vidéo à laquelle on assigne le professeur
 * \param listeCadreurs - supposément une chaîne de caractères contenant tous les cadreurs
 */
function assignerRole($idVideo, $nomRole, $personne) {
    $connexion = connexionBD();

    try {
        // Vérifier si le rôle existe, sinon l'ajouter
        $stmt = $connexion->prepare('SELECT id FROM Role WHERE libelle = ?');
        $stmt->execute([$nomRole]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$role) {
            // Le rôle n'existe pas, on l'ajoute
            $requeteInsertRole = $connexion->prepare('INSERT INTO Role (libelle) VALUES (?)');
            $requeteInsertRole->execute([$nomRole]);
            // Récupérer l'ID du rôle inséré
            $idRole = $connexion->lastInsertId();
        } else {
            // Le rôle existe déjà
            $idRole = $role['id'];
        }
        

        // Vérifier si l'étudiant existe, sinon l'ajouter
        $stmt = $connexion->prepare('SELECT id FROM Etudiant WHERE nomComplet = ?');
        $stmt->execute([$personne]);
        
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$etudiant) {
            // L'étudiant n'existe pas, on l'ajoute
            $requeteInsertEtud = $connexion->prepare('INSERT INTO Etudiant (nomComplet) VALUES (?)');
            $requeteInsertEtud->execute([$personne]);
            $idEtudiant = $connexion->lastInsertId();
            
        } else {
            // L'étudiant existe déjà
            $idEtudiant = $etudiant['id'];
            
        }

        // Insérer l'assignation du rôle dans Participer
        $requeteInsertPart = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) VALUES (?, ?, ?)');
        $requeteInsertPart->execute([$idVideo, $idEtudiant, $idRole]);
        

        // Valider la transaction
        $connexion->commit();

    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $connexion->rollBack();
        // Loguer l'erreur
        ajouterLog(LOG_CRITICAL, "Erreur lors de l'assignation du rôle '$nomRole' pour la vidéo $idVideo : " . $e->getMessage());
    } finally {
        // Fermer la connexion
        $connexion = null;
    }
}




/**
 * \fn assignerCadreur($idVideo, $listeCadreurs)
 * \brief Permet d'assigner un ou des cadreurs au projet
 * \param idVideo - l'id de la vidéo à laquelle on assigne le professeur
 * \param listeCadreurs - supposément une chaîne de caractères contenant tous les cadreurs
 */
 function assignerCadreur($idVideo, $listeCadreurs){
    $connexion = connexionBD();    
    if($listeCadreurs == ""){
        $cadreur = $connexion->prepare('DELETE FROM Participer 
                            WHERE (idMedia = ? AND idRole = ?)');
                        $cadreur->execute([$idVideo, 1]);
                        
        $connexion->commit();   
    }
    else if ($listeCadreurs != "") {
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
            ajouterLog(LOG_CRITICAL, "Erreur lors de l'assignation du cadreur " . $listeCadreurs . " à la vidéo " . $idVideo .
            " : " . $e->getMessage());
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

/**
 * \fn assignerResponsable($idVideo, $listeResponsable)
 * \brief Permet d'assigner un ou des responsables sons
 * \param idVideo - l'id de la vidéo à laquelle on assigne l'élève
 * \param listeResponsable - supposément une chaîne de caractères contenant tous les responsables son
 */
 function assignerResponsable($idVideo, $listeResponsable){
    $connexion = connexionBD(); 
    if($listeResponsable == ""){
        $responsable = $connexion->prepare('DELETE FROM Participer 
                            WHERE (idMedia = ? AND idRole = ?)');
                        $responsable->execute([$idVideo, 3]);
                        
        $connexion->commit();   
    }
    else if ($listeResponsable != "") {
        $connexion = connexionBD();
        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les responsables son
        $listeResponsable = trim(preg_replace('/\s*,\s*/', ', ', $listeResponsable));
        $tabResponsable = explode(', ', $listeResponsable);
        try{
            //On efface toutes les données responsables son pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $responsable = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $responsable->execute([$idVideo, 3]);
            for ($i=0; $i < count($tabResponsable); $i++) { 
                if(!etudiantInBD($tabResponsable[$i]))
                {
                    insertionEtudiant($tabResponsable[$i]);
                }
                // Récupérer l'ID de l'élève
                $idEtudiant = getIdEtudiant($tabResponsable[$i]);
                // Insertion si non existant
                $responsable = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) 
                    VALUES (?, ?, ?)');
                $responsable->execute([$idVideo, $idEtudiant, 3]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            ajouterLog(LOG_CRITICAL, "Erreur lors de l'assignation du responsable son " . $listeResponsable . " à la vidéo " . $idVideo .
            " : " . $e->getMessage());
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

 /**
 * \fn assignerRealisateur($idVideo, $listeRealisateurs)
 * \brief Permet d'assigner un ou des réalisateurs
 * \param idVideo - l'id de la vidéo à laquelle on assigne l'élève
 * \param listeRealisateur - supposément une chaîne de caractères contenant tous les réalisateurs
 */
 function assignerRealisateur($idVideo, $listeRealisateurs){
    $connexion = connexionBD(); 
    if($listeRealisateurs == ""){
        $realisateur = $connexion->prepare('DELETE FROM Participer 
                            WHERE (idMedia = ? AND idRole = ?)');
                        $realisateur->execute([$idVideo, 2]);
                        
        $connexion->commit();   
    }
    if ($listeRealisateurs != "") {
        $connexion = connexionBD();
        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les realisateurs
        $listeRealisateurs = trim(preg_replace('/\s*,\s*/', ', ', $listeRealisateurs));
        $tabRealisateur = explode(', ', $listeRealisateurs);
        try{
            //On efface toutes les données realisateurs pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $realisateur = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $realisateur->execute([$idVideo, 2]);
            for ($i=0; $i < count($tabRealisateur); $i++) { 
                if(!etudiantInBD($tabRealisateur[$i]))
                {
                    insertionEtudiant($tabRealisateur[$i]);
                }
                // Récupérer l'ID de l'élève
                $idEtudiant = getIdEtudiant($tabRealisateur[$i]);
                // Insertion si non existant
                $realisateur = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) 
                    VALUES (?, ?, ?)');
                $realisateur->execute([$idVideo, $idEtudiant, 2]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            ajouterLog(LOG_CRITICAL, "Erreur lors de l'assignation du réalisateur " . $listeRealisateurs . " à la vidéo " . $idVideo .
            " : " . $e->getMessage());
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

 /**
 * \fn assignerPromotion($idVid, $valPromo)
 * \brief Assigne la promotion durant laquelle la vidéo a été produite
 * \param idVid - ID de la vidéo
 * \param valPromo - La promotion qu'on va assigner
 */
 function assignerPromotion($idVid, $valPromo)
 {
    $connexion = connexionBD();  
    try{
        $cadreur = $connexion->prepare('UPDATE Media SET promotion = ?, date_modification = CURRENT_TIMESTAMP WHERE id = ?');
        $cadreur->execute([$valPromo, $idVid]);
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        ajouterLog(LOG_CRITICAL, "Erreur lors de l'assignation de la promotion " . $valPromo . " à la vidéo " . $idVideo .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
 }

#################################################
 
#    FONCTIONS "GETTERS" DE RECHERCHE SUR LES TABLES
 
####################################################*/

/**
* \fn getProjet($projet)
* \brief Renvoie le projet lié à une vidéo
* \param projet - nom du projet
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération du projet " . $projet .
        " : " . $e->getMessage());
        $connexion = null;
    }
 }

  /**
 * \fn getRole($etudiant)
 * \brief Renvoie un rôle
 * \param role - Nom du rôle
 */
function getAllRoles()
{
   $connexion = connexionBD();
   $requeteRole = $connexion->prepare('SELECT * 
   FROM Role');                                                 
   try{
       $requeteRole->execute();
       $roleCherche = $requeteRole->fetchAll(PDO::FETCH_ASSOC);
       $connexion = null;
       return $roleCherche;
   }
   catch(Exception $e)
   {
       ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération du role " . $role .
       " : " . $e->getMessage());
       $connexion->rollback();
       $connexion = null;
   }
}

  /**
 * \fn getRole($role)
 * \brief Renvoie un rôle
 * \param role - Nom du rôle
 */
function getRole($role)
{
   $connexion = connexionBD($role);
   $requeteRole = $connexion->prepare('SELECT * 
   FROM Role
   WHERE libelle = ?');                                                 
   try{
       $requeteRole->execute([$role]);
       $roleCherche = $requeteRole->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       return $roleCherche;
   }
   catch(Exception $e)
   {
       ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération du role " . $role .
       " : " . $e->getMessage());
       $connexion->rollback();
       $connexion = null;
   }
}

/**
 * \fn getIdEtudiant($etudiant)
 * \brief Renvoie l'id d'un étudiant
 * \param etudiant - Nom complet de l'étudiant
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération de l'id de l'étudiant " . $etudiant .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
 }

 /**
 * \fn getVideo($URIStockageLocal, titreVideo)
 * \brief Renvoie 1 si la vidéo existe dans la base de données
 * \param URIStockageLocal - chemin de l'espace local de la vidéo
 * \param titreVideo - titre de la vidéo
 */
function getVideo($URIStockageLocal, $titreVideo){
    $connexion = connexionBD();
    $requeteVid = $connexion->prepare('SELECT 1 
    FROM Media
    WHERE URI_STOCKAGE_LOCAL = ? AND mtd_tech_titre = ?');                             
   try{
       $requeteVid->execute([$URIStockageLocal, $titreVideo]);
       $videoPresente = $requeteVid->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       if ($videoPresente) {
            return true;
       } 
       else {
           return false;
       }   
   }
   catch(Exception $e)
   {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération de la vidéo " . $URIStockageLocal . $titreVideo .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
        return false;
   }
}

 /**
 * \fn getIdVideoURIetTitre($path, $titre, $ftp_server)
 * \brief Renvoie l'id d'une vidéo
 * \param path - chemin de l'espace local de la vidéo
 * \param titre - Nom de la vidéo à retourner
 * \param ftp_server - Nom du server pour définir le champ
 */
function getIdVideoURIetTitre($path, $titre, $ftp_server){
    $connexion = connexionBD();

    if ($ftp_server == NAS_ARCH) {
        $requeteVid = $connexion->prepare('SELECT id 
            FROM Media
            WHERE URI_NAS_ARCH = ? AND mtd_tech_titre = ?'); 
    } elseif ($ftp_server == NAS_PAD) {
        $requeteVid = $connexion->prepare('SELECT id 
            FROM Media
            WHERE URI_NAS_PAD = ? AND mtd_tech_titre = ?'); 
    } else {
        $requeteVid = $connexion->prepare('SELECT id 
            FROM Media
            WHERE URI_STOCKAGE_LOCAL = ? AND mtd_tech_titre = ?');
    }
                                                
   try{
       $requeteVid->execute([$path, $titre]);
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération de la vidéo " . $path . $titre .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
        return false;
   }
}

 /**
 * \fn getInfosVideo($idVideo)
 * \brief Renvoie toutes les informations d'une vidéo
 * \param idVideo - ID de la vidéo dont on veut les informations
 */
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des informations de la vidéo " . $idVideo .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
   }
}


function getURISVideo($idVideo)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT URI_NAS_PAD, URI_NAS_ARCH, mtd_tech_titre, URI_STOCKAGE_LOCAL
                                        FROM Media
                                        WHERE id = ?');                                                 
   try{
       $requeteVid->execute([$idVideo]);
       $infosVideo = $requeteVid->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       if ($infosVideo) {
        ajouterLog(LOG_INFORM, "ok");
        return $infosVideo;
       } 
       else {
        ajouterLog(LOG_INFORM, "pas ok");
           return false;
       }
   }
   catch(Exception $e)
   {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération de l'URI de la vidéo " . $idVideo . 
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
   }
}

/**
 * \fn getTitreURIEtId($nbMaxVideo)
 * \brief Renvoie tous les titres, ids des vidéos et leur chemin d'accès dans la machine locale
 * \param nbMaxVideo - Le nombre maximum de vidéos dont on veut obtenir les informations
 * \return resultat - Tableau des vidéos et informations obtenues
 */
function getTitreURIEtId($nbMaxVideo) {
    try {
        $connexion = connexionBD();
        $requeteVid = $connexion->prepare('SELECT id,
        URI_STOCKAGE_LOCAL, mtd_tech_titre
        FROM Media
        ORDER BY date_modification DESC
        LIMIT :nbVideo');
        $requeteVid->bindParam(":nbVideo", $nbMaxVideo,PDO::PARAM_INT);
        $requeteVid->execute();
        $resultat = $requeteVid->fetchAll(PDO::FETCH_ASSOC);
        $connexion = null;
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau
        } else {
            return false; // Aucun résultat trouvé
        }
    } catch (Exception $e) {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des URIs et titres des vidéos : " . $e->getMessage());
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
        error_log('Erreur dans getTitreURIEtId: ' . $e->getMessage());
        return false;
    }
}

/**
 * \fn getProfId($profNom, $profPrenom)
 * \brief Renvoie l'id du professeur ayant le nom et prenom spécifié
 * \param profNom - Nom du professeur
 * \param profPrenom - Prénom du professeur
 * \return profCherche - Données sur le professeur obtenu 
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération de l'id du professeur " . $profNom . $profPrenom .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
   }
}

/**
 * \fn getProjetIntitule($idProjet)
 * \brief Renvoie l'intitulé du projet
 * \param idProjet - Id du projet qu'on recherche
 * \return projet - Données du projet
 */
function getProjetIntitule($idProjet){
    $connexion = connexionBD();
    try {
        $requeteProjet = $connexion->prepare('SELECT intitule FROM Projet WHERE id = ?');
        $requeteProjet->execute([$idProjet]);
        $projet = $requeteProjet->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return $projet ? $projet["intitule"] : false;
    } catch (Exception $e) {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération du nom du projet " . $idProjet .
        " : " . $e->getMessage());
        error_log("Erreur lors de la récupération du projet: " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
}

/**
 * \fn getIdProjetVideo
 * \brief Renvoie le projet associé à la vidéo via un ID, false si aucun projet n'est attribué
 * \param idVideo - Id de la vidéo dont on cherche le projet
 * \return projet - Intitulé du projet
 */
function getIdProjetVideo($idVideo) {
    try {
        $connexion = connexionBD();
        $requeteVid = $connexion->prepare('SELECT projet FROM `Media` WHERE id=:idVideo;');
        $requeteVid->bindParam(":idVideo", $idVideo,PDO::PARAM_INT);
        $requeteVid->execute();
        $resultat = $requeteVid->fetch(PDO::FETCH_ASSOC)["projet"];
        $connexion = null;
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau
        } else {
            return false;
        }
    } catch (Exception $e) {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération du nom du projet avec la vidéo " . $idVideo .
        " : " . $e->getMessage());
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
        error_log('Erreur dans getTitreURIEtId: ' . $e->getMessage());
        return false;
    }
}

/**
 * \fn getProfNomPrenom($identifiant)
 * \brief Renvoie le nom et le prénom du professeur
 * \param identifiant - identifiant du professeur dont on recherche les informations
 * \return profCherche - Données du prof recherché
 */
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des informations du professeur " . $identifiant .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
   }
}

/**
 * \fn getAllProfesseurs()
 * \brief Renvoie la liste de tous les professeurs (nom + prenom)
 * \return professeurs - Liste de tous les professeurs
 */
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des informations des professeurs " .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
   }
}

/**
 * \fn getParticipants($idVid)
 * \brief Retourne tous les participants d'un projet
 * \param idVid - La vidéo dont on veut connaître tous les participants pour sa production
 * \return [realisateur,cadreur,son] - Tableau des participants dans chaque rôle
 */
function getParticipants($idVid) {
    $connexion = connexionBD();
    
    // Fonction pour récupérer et formater les noms des participants selon leur rôle
    function fetchParticipants($connexion, $idVid, $idRole) {
        $requete = $connexion->prepare('SELECT Etudiant.nomComplet FROM Etudiant JOIN Participer ON Etudiant.id = Participer.idEtudiant WHERE Participer.idMedia = ? AND Participer.idRole = ?');
        $requete->execute([$idVid, $idRole]);
        $result = $requete->fetchAll(PDO::FETCH_COLUMN);
        return $result ? implode(", ", $result) : "";
    }
    
    // Récupération des participants selon les rôles
    $realisateur = fetchParticipants($connexion, $idVid, 2);
    $cadreur = fetchParticipants($connexion, $idVid, 1);
    $son = fetchParticipants($connexion, $idVid, 3);
    
    // Fermeture de la connexion
    $connexion = null;
    
    return [
        'Realisateur' => $realisateur,
        'Cadreur' => $cadreur,
        'Son' => $son
    ];
}

/**
 * \fn getAllParticipants($etudiant)
 * \brief Renvoie un rôle
 * \param role - Nom du rôle
 */
function getRolesEtParticipantsDeVideo($idMedia)
{
   $connexion = connexionBD();
   $requeteRole = $connexion->prepare('SELECT * 
   FROM Role 
   JOIN Participer ON role.id = participer.idRole
   JOIN Etudiant ON Etudiant.id = participer.idEtudiant
   WHERE Participer.idMedia = ?');                                                 
   try{
       $requeteRole->execute([$idMedia]);
       $rolesEtParticipants = $requeteRole->fetchAll(PDO::FETCH_ASSOC);
       $connexion = null;
       return $rolesEtParticipants;
   }
   catch(Exception $e)
   {
       ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération du role " . $role .
       " : " . $e->getMessage());
       $connexion->rollback();
       $connexion = null;
   }
}

###########################
#     TRUE / FALSE
############################


/**
 * \fn etudiantInBD($etudiant)
 * \brief Renvoie un boléen si l'élève est dans la base de données
 * \param etudiant - Etudiant recherché
 * \return boolean (Vrai ou Faux)
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération de l'étudiant " . $etudiant .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
}

/**
 * \fn fetchAll($sql)
 * \brief fetchAll("SELECT * FROM Media"); va renvoyer toutes les infos des vidéos
 * NE PAS UTILISER SI POSSIBLE. TOUJOURS PRÉFÉRER LA CRÉATION D'UNE NOUVELLE FONCTION
 * \param sql - Requête qu'on aimerait exécuter
 * \return resultat - Données récupérées via la requête
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
        ajouterLog(LOG_CRITICAL, "Erreur SQL " .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
        error_log('Erreur : ' . $e->getMessage());
    }
}

/**
 * \fn verifierPresenceVideoStockageLocal($cheminFichier, $nomFichier)
 * \brief Vérifie qu'une vidéo existe bien dans le stockage local. Renvoie 1 si une vidéo existe
 * \param cheminFichier - chemin de l'espace local de la vidéo
 * \param nomFichier - nom du fichier
 * \return boolean
 */
function verifierPresenceVideoStockageLocal($cheminFichier, $nomFichier)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT 1
   FROM Media
   WHERE URI_STOCKAGE_LOCAL = ?
   AND mtd_tech_titre = ?');                                                 
   try{
       $requeteVid->execute([$cheminFichier, $nomFichier]);
       $vidPresente = $requeteVid->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return (bool)$vidPresente;
   }
   catch(Exception $e)
   {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la vérification de la présence de la vidéo " . $cheminFichier . $nomFichier .
        "dans le stock local : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
   }
}

/**
 * \fn connexionProfesseur($loginUser, $passwordUser)
 * \brief Fonction qui regarde si un prof existe pour un couple login/mdp passé en paramètre
 * \param loginUser - Identifiant de connexion du professeur
 * \param passwordUser - Mot de passe de l'utilisateur
 * \return resultatRequeteConnexion - Données retournées par la requête de connexion
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
        ajouterLog(LOG_CRITICAL, "Erreur lors de la connexion du professeur " . $loginUser .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
   }
}

/**
 * \fn recupererProjetDerniereVideoModifiee()
 * \brief Fonction qui récupère le projet contenant la dernière vidéo modifiée
 * \return resultatRequeteConnexion - Données retournées par la requête de connexion
 */
function recupererProjetDerniereVideoModifiee(){
    $connexion = connexionBD();                     
    try{
         $requeteConnexion = $connexion->prepare('SELECT projet FROM Media
            WHERE projet IS NOT NULL
            ORDER BY date_modification DESC
            LIMIT 1;');   
         $requeteConnexion->execute();
         $resultatRequeteConnexion = $requeteConnexion->fetch(PDO::FETCH_ASSOC);
         $connexion = null;
         if (!empty($resultatRequeteConnexion["projet"])) {
            return $resultatRequeteConnexion["projet"];
        } else {
            return false;
        }
    }
    catch(Exception $e)
    {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération du projet de la dernière vidéo modifiée " .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
 }

/**
 * Fonction qui renvoie la liste des vidéos transférées récemment
 * en attente de métadonnées
 */
function recupererDernieresVideosTransfereesSansMetadonnees($nb_videos_historique_transfert){
    $connexion = connexionBD();                  
    try{
         $requeteConnexion = $connexion->prepare('SELECT id,date_creation,mtd_tech_titre FROM Media
			WHERE `professeurReferent` IS NULL AND `promotion` IS NULL AND `Description` IS NULL AND `theme` IS NULL
            ORDER BY date_creation DESC
            LIMIT :nb_videos_historique_transfert');
         $requeteConnexion->bindParam(":nb_videos_historique_transfert", $nb_videos_historique_transfert,PDO::PARAM_INT);
         $requeteConnexion->execute();
         $resultatRequeteConnexion = $requeteConnexion->fetchAll(PDO::FETCH_ASSOC);
         $connexion = null;
         if (!empty($resultatRequeteConnexion)) {
            return $resultatRequeteConnexion;
        } else {
            return false;
        }
    }
    catch(Exception $e)
    {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des vidéos transférées sans métadonnées " .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
 }

/**
 * \fn recupererUriTitreVideosMemeProjet($idProjet)
 * \brief Fonction qui retourne id, URI_STOCKAGE_LOCAL, mtd_tech_titre et projet selon un projet
 * \param idProjet - Identifiant d'un projet
 * \return resultatRequeteConnexion - Retourne une vidéo si trouvé
 */
 function recupererUriTitreVideosMemeProjet($idProjet){
    $connexion = connexionBD();                     
    try{
        //#RISQUE : Ajouter une LIMIT avec la constante NB_VIDEO_PAR_SLIDER
         $requeteConnexion = $connexion->prepare('SELECT id, URI_STOCKAGE_LOCAL, mtd_tech_titre, projet
            FROM Media
            WHERE projet = ?
            ORDER BY date_modification DESC');   
         $requeteConnexion->execute([$idProjet]);
         $resultatRequeteConnexion = $requeteConnexion->fetchAll(PDO::FETCH_ASSOC);
         $connexion = null;
         if (!empty($resultatRequeteConnexion)) {
            return $resultatRequeteConnexion;
        } else {
            return false;
        }
    }
    catch(Exception $e)
    {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des vidéos du projet " . $idProjet .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
 }
 
 /**
 * \fn supprimerVideoDeBD($idVideo)
 * \brief Supprime les données d'une vidéo
 * \param idVideo - L'ID de la vidéo qu'on veut supprimer
 */
function supprimerVideoDeBD($idVideo){ //#RISQUE : Ajouter un try catch
    $connexion = connexionBD();
    $requeteConnexion=$connexion->prepare('DELETE FROM MEDIA WHERE id = ?');
    $requeteConnexion->execute([$idVideo]);
    $connexion->commit();
}

 /**
 * \fn supprimerVideoNADPAD($idVideo)
 * \brief Supprime le lien d'une vidéo vers son NAS PAD
 * \param idVideo - L'ID de la vidéo qu'on veut supprimer
 */
function supprimerVideoNASPAD($idVideo){ //#RISQUE : Ajouter un try catch
    $connexion = connexionBD();
    $requeteConnexion=$connexion->prepare('UPDATE MEDIA SET URI_NAS_PAD = null WHERE id = ?');
    $requeteConnexion->execute([$idVideo]);
    $connexion->commit();
}

 /**
 * \fn supprimerVideoNADARCH($idVideo)
 * \brief Supprime le lien d'une vidéo vers son NAS ARCH
 * \param idVideo - L'ID de la vidéo qu'on veut supprimer
 */
function supprimerVideoNASARCH($idVideo){ //#RISQUE : Ajouter un try catch
    $connexion = connexionBD();
    $requeteConnexion=$connexion->prepare('UPDATE MEDIA SET URI_NAS_ARCH = null WHERE id = ?');
    $requeteConnexion->execute([$idVideo]);
    $connexion->commit();
}

 /**
 * \fn recupererAutorisationsProfesseurs()
 * \brief Retourne la listes des professeurs et de leurs autorisations
 */
function recupererAutorisationsProfesseurs(){
    $connexion = connexionBD();
    try{
            $requeteConnexion = $connexion->prepare('SELECT Professeur.nom, Professeur.prenom, Professeur.role, Autorisation.professeur, Autorisation.modifier, Autorisation.supprimer, Autorisation.diffuser, Autorisation.administrer
                FROM Autorisation
                JOIN Professeur ON Professeur.identifiant = Autorisation.professeur');
            $requeteConnexion->execute();
            $resultatRequeteConnexion = $requeteConnexion->fetchAll(PDO::FETCH_ASSOC);
            $connexion = null;
            return $resultatRequeteConnexion;
    }
    catch(Exception $e)
    {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des autorisations des professeurs " .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
}

function recupererAutorisationsProfesseur($identifiant){
    $connexion = connexionBD();
    try{
        $requeteConnexion = $connexion->prepare('SELECT modifier, diffuser, supprimer, administrer
            FROM Autorisation
            WHERE professeur = ?');
        $requeteConnexion->execute([$identifiant]);
        $resultatRequeteConnexion = $requeteConnexion->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return $resultatRequeteConnexion;
    }
    catch(Exception $e)
    {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des autorisations du professeur " . $identifiant .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
}

function mettreAJourAutorisations($prof, $colonne, $etat){
    $valeur = ($etat == "true") ? 1 : 0 ;
    $connexion = connexionBD();
    try{
        $requete = $connexion->prepare('UPDATE Autorisation SET ' . $colonne . ' = ? WHERE professeur = ?');
        $requete->execute([$valeur, $prof]);
        $connexion->commit();
        $connexion = null;
    }
    catch(Exception $e)
    {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la mise à jour des autorisations du professeur " . $prof .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
}

function deleteFromRoles($idVid, $idRole){
    $connexion = connexionBD();
    try{
        $reqDelete = $connexion->prepare('DELETE FROM Participer WHERE idMedia = ? AND idRole = ?');
        $reqDelete->execute([$idVid, $idRole]);
        $connexion->commit();
        $connexion = null;
    }
    catch(Exception $e)
    {
        ajouterLog(LOG_CRITICAL, "Erreur lors de la mise à jour des autorisations du professeur " . $prof .
        " : " . $e->getMessage());
        $connexion->rollback();
        $connexion = null;
    }
}

 /**
 * \fn getAllProfesseursReferent()
 * \brief Récupère tout les professeur liés a une vidéo
 */
function getAllProfesseursReferent(){
    $connexion = connexionBD();
    try{
        $requete = "SELECT DISTINCT professeurReferent, nom, prenom FROM media JOIN Professeur ON Professeur.identifiant = Media.professeurReferent WHERE professeurReferent IS NOT NULL ORDER BY professeurReferent ASC";
        $sql = $connexion->prepare($requete);
        $sql->execute();
        $professeurs = $sql->fetchAll();
        $connexion = null;
        return $professeurs;
    }
    catch(Exception $e){
        $connexion = null;
    }   
}

function faireRecherche($motsClefs){
    //préparation de la requête
    $connexion = connexionBD();

    $motsClefs = explode(' ', $motsClefs);
    $listeChamps = ["mtd_tech_titre", "description", "Professeur.nom", "Professeur.prenom", "projet", "promotion"];

    $queryParts = [];
    $query = "SELECT * FROM Media LEFT JOIN Professeur ON Media.professeurReferent = Professeur.identifiant WHERE ";

    foreach ($listeChamps as $champ) {
        $likeParts = array_map(function($mot) use ($champ) {
            return $champ . ' LIKE "%' . $mot . '%"';
        }, $motsClefs);
        
        $queryParts[] = implode(' OR ', $likeParts);
    }

    $query .= implode(' OR ', $queryParts);    
    try{
        $sql = $connexion->prepare($query);
        $sql->execute();
        $recherche = $sql->fetchAll();
        $connexion = null;
        return $recherche;
    }
    catch(Exception $e){
        $connexion = null;
    } 
}

function faireRechercheAvance($prof = null, $description = null, $projet = null){
    $connexion = connexionBD();

    $requete = "SELECT * FROM Media";
    $conditions = [];

    if ($prof) {
        $conditions[] = "professeurReferent = '$prof'";
    }

    if ($description) {
        $conditions[] = "description LIKE '%$description%'";
    }

    if ($projet) {
        $conditions[] = "projet = '$projet'";
    }

    // Ajout des conditions si elles existent
    if (!empty($conditions)) {
        $requete .= " WHERE " . implode(" AND ", $conditions);
    }

    try{
        $sql = $connexion->prepare($requete);
        $sql->execute();
        $recherche = $sql->fetchAll();
        $connexion = null;
        return $recherche;
    }
    catch(Exception $e){
        ajouterLog(LOG_CRITICAL, "Erreur lors de la recherche avancée " .
         " : " . $e->getMessage());
         $connexion->rollback();
        $connexion = null;
    } 

}

function getAllProjet(){
    $connexion = connexionBD();
    $requeteProf = $connexion->prepare('SELECT intitule 
    FROM Projet');                                                 
    try{
        $requeteProf->execute();
        $professeurs = $requeteProf->fetchAll(PDO::FETCH_ASSOC);
        $connexion = null;
        return $professeurs;
    }
    catch(Exception $e)
    {
         ajouterLog(LOG_CRITICAL, "Erreur lors de la récupération des informations des professeurs " .
         " : " . $e->getMessage());
         $connexion->rollback();
         $connexion = null;
    }
}
?>