<?php

/**
 * 
 * Regroupement des fonctions sur la BD qui sont mises de côté. Eventuellement utile mais problème de cohérence code
 * 
 * 
 */

 /**
* @Nom : getRealisateur
* @Description : renvoie la liste des réalisateurs d'une vidéo
* @video : id de la vidéo concernée
 */

 function getRealisateur($video)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $requeteReal = $connexion->prepare('SELECT nomComplet 
    FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve
    WHERE idMedia = ? AND idRole = 2');                                                 // #RISQUE : j'ai mis 2 en estimant que ce serait l'id des réalisateurs mais bon hein :v 
    try{
        $requeteReal->execute([$video]);
        $listeReal = $requeteReal->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        
        $connexion = null;
        return $listeReal;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";                                                      //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
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
    $requeteCadreur = $connexion->prepare('SELECT nomComplet
    FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve
    WHERE idMedia = ? AND idRole = 1');                                                 // #RISQUE : j'ai mis 1 en estimant que ce serait l'id des cadreurs mais bon hein :v                  
    try{
        $requeteCadreur->execute([$video]);
        $listeCadreurs = $requeteCadreur->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        $connexion = null;
        return $listeCadreurs;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";                                             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
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
    $requeteResponsable = $connexion->prepare('SELECT nomComplet 
    FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve
    WHERE idMedia = ? AND idRole = 3');                                                 // #RISQUE : j'ai mis 3 en estimant que ce serait l'id des responsablesSons mais bon hein :v 
    try{
        $requeteResponsable->execute([$video]);
        $listeResponsable = $requeteResponsable->fetch(PDO::FETCH_ASSOC);               // Récupère une seule ligne sous forme de tableau associatifs
        $connexion = null;
        return $listeResponsable;
    }
    catch(Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";                                      //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }



 //////////////////////

 $liste2 = ['Titre' =>  "23_6h_JIN_Fermetur.mxf",
                'FPS' => 25,
                'Resolution' => "1920x1080",
                'Duree' => "00:00:15",
                'Format' => "16:9"
                ];

$liste3 = ['Titre' =>  "AAAAAAAAAH.mxf",
'FPS' => 25,
'Resolution' => "1920x1080",
'Duree' => "00:00:15",
'Format' => "16:9"
];

$listeEditoriale = ['prof' => 'Michael Jackson',
                    'cadreurs' => 'Michael Jackson, Lyxandre TktJeChercheLeNom',
                    'responsables' => 'Solène Martin',
                    'realisateurs' => 'Julien Loridant',
                    'projet' => 'Projet de Fin dannée 2024'];

$listeEditoriale2 = ['prof' => 'Michael Jackson',
                'cadreurs' => 'Michael Jackson',
                'responsables' => 'Axel Marrier',
                'realisateurs' => 'Nicolas Conguisti, Nicolo Canguisti',
                'projet' => 'Projet de Fin dannée 2024'];


insertionDonneesTechniques($liste2);
insertionDonneesTechniques($liste3);
//var_dump(eleveInBD('Michael Jackson'));
//var_dump(profInBD('Michael Jackson'));

?>