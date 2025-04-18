<?php 
    session_start();
    require_once '../fonctions/controleur.php';
    $infosVideo = controleurRecupererInfosVideo();

    $idVideo = $infosVideo["idVideo"];
    $nomFichier = $infosVideo["nomFichier"];
    $cheminMiniatureComplet = $infosVideo["cheminMiniatureComplet"];
    $cheminVideoComplet = $infosVideo["cheminVideoComplet"];
    $titreVideo = $infosVideo["titreVideo"];
    $description = $infosVideo["description"];
    $mtdTech = $infosVideo["mtdTech"];
    $mtdEdito = $infosVideo["mtdEdito"];
    $mtdRoles = $infosVideo["mtdRoles"];
    $promotion = $infosVideo["promotion"];
    $URIS = $infosVideo["URIS"];

    $cheminCompletNAS_PAD = null;
    if(!empty($URIS['URI_NAS_PAD'])){
        $cheminCompletNAS_PAD = $URIS['URI_NAS_PAD'].$nomFichier;
    }
    else{
        $URIS['URI_NAS_PAD'] = "Non présente";
    }

    $cheminCompletNAS_ARCH = null;
    if(!empty($URIS['URI_NAS_ARCH'])){
        $cheminCompletNAS_ARCH = $URIS['URI_NAS_ARCH'].$nomFichier;
    }
    else{
        $URIS['URI_NAS_ARCH'] = "Non présente";
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <link href="../ressources/Style/menuArbo.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <!-- #RISQUE : Liens CDN utilisés dans la lib plyr.js -->
    <script src="../ressources/lib/Plyr/plyr.js"></script>
    <link rel="stylesheet" href="../ressources/lib/Plyr/plyr.css" />

    <?php
        require_once '../ressources/Templates/header.php';
        require_once '../ressources/Templates/menuArbo.php';
        chargerPopup();
    ?>

    <div class="contenu">
        <div class="container_principal">
            <div class="container_video">
                <div class="lecteurVideo">
                    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminMiniatureComplet; ?>>
                        <source src="<?php echo $cheminVideoComplet; ?>" type="video/mp4"/>
                    </video>
                </div>
            </div>
            <div class="info_video">

                <div class ="titre_nom">
                    <h1 class="titre"><?php echo $nomFichier; ?></h1>
                    <h2 ><?php echo $titreVideo; ?></h2>
                </div>

                <div class="container-button">
                    <?php
                    if (!empty($cheminCompletNAS_ARCH)){ ?>
                            <button title="Télécharger vidéo" class="btnVideo" onclick="changerTitrePopup('Téléchargement'); 
                                                                                        changerTextePopup('Voulez-vous télécharger la vidéo <?php echo $nomFichier; ?> ?');
                                                                                        changerTexteBtn('Confirmer', 'btn1');
                                                                                        changerTexteBtn('Annuler', 'btn2');
                                                                                        attribuerFonctionBtn('lancerTelechargement','<?php echo $cheminCompletNAS_ARCH; ?>', 'btn1');
                                                                                        afficherBtn('btn2');
                                                                                        cacherBtn('btn3');
                                                                                        afficherPopup();">
                            <div class="logo-btnvideo">
                            <img src="../ressources/Images/télécharger_image.png" alt="">
                            </div>
                            <p>Télécharger</p>
                            </button>
                            <div id="overlay" style="display : none">
                                <div class="loader"></div>
                                <p>Téléchargement en cours. Veuillez rafraîchir la page à la fin du téléchargement</p>
                            </div>
                            <?php
                        }
                        else{ ?>
                            <button title="Télécharger vidéo" class="btnVideo boutonGrise">
                                <div class="logo-btnvideo">
                                    <img src="../ressources/Images/télécharger_image.png" alt="">
                                </div>
                                <p>Indisponible</p>
                            </button> <?php
                        }
                    ?>

                    <?php if (controleurVerifierAcces(ACCES_MODIFICATION)) { ?>
                        <button id="boutonModif" title="Modifier vidéo" class="btnVideo" onclick="window.location.href='formulaireMetadonnees.php?v=<?php echo $idVideo; ?>';">
                            <div class="logo-btnvideo">
                                <img src="../ressources/Images/modifier_video.png" alt="">
                            </div>
                            <p>Modifier</p>
                        </button>
                    <?php } ?>

                    <?php if (controleurVerifierAcces(ACCES_SUPPRESSION)) { ?>
                        <button title="Supprimer vidéo" class="btnVideo" id="btnSuppr" onclick="  changerTitrePopup('Suppression'); 
                                                                                                changerTextePopup('De quel espace voulez-vous supprimer la vidéo <?php echo $nomFichier; ?> ?');
                                                                                                changerTexteBtn('Base de données', 'btn1');
                                                                                                changerTexteBtn('NAS PAD', 'btn2');
                                                                                                changerTexteBtn('NAS Archive', 'btn3');
                                                                                                attribuerFonctionBtn('supprimerVideo','<?php echo $idVideo; ?>, local', 'btn1');
                                                                                                attribuerFonctionBtn('supprimerVideo','<?php echo $idVideo; ?>, PAD', 'btn2');
                                                                                                attribuerFonctionBtn('supprimerVideo','<?php echo $idVideo; ?>, ARCH', 'btn3');
                                                                                                afficherBtn('btn2');
                                                                                                afficherBtn('btn3');
                                                                                                afficherPopup();">
                            <div class="logo-btnvideo">
                                <img src="../ressources/Images/poubelle-de-recyclage.png" alt="">
                            </div>
                            <p>Supprimer</p>
                        </button>
                    <?php } ?>

                    <?php if(controleurVerifierAcces(ACCES_DIFFUSION)){
                            if (!empty($cheminCompletNAS_PAD)){ ?>
                                <button id="boutonDiffusion" title="Diffuser vidéo" class="btnVideo" onclick="  changerTitrePopup('Diffusion'); 
                                                                                                                changerTextePopup('Voulez-vous diffuser la vidéo <?php echo $nomFichier; ?> ?');
                                                                                                                changerTexteBtn('Confirmer', 'btn1');
                                                                                                                changerTexteBtn('Annuler', 'btn2');
                                                                                                                attribuerFonctionBtn('lancerDiffusion','<?php echo $cheminCompletNAS_PAD; ?>', 'btn1');
                                                                                                                afficherBtn('btn2');
                                                                                                                cacherBtn('btn3');
                                                                                                                afficherPopup();">
                                    <div class="logo-btnvideo">
                                        <img src="../ressources/Images/diffuser.png" alt="">
                                    </div>
                                    <p>Diffuser</p>
                                </button> <?php
                            }
                            else{ ?>
                                <button id="boutonDiffusion" title="Diffuser vidéo" class="btnVideo boutonGrise">
                                    <div class="logo-btnvideo">
                                        <img src="../ressources/Images/diffuser.png" alt="">
                                    </div>
                                    <p>Indisponible</p>
                                </button> <?php
                            }
                        }?>
                </div>



            </div>
            <?php if ($description != ""): ?>
                <div class="containerDescription">
                    <p class="description">
                        <?php echo htmlspecialchars($description); ?>
                    </p>
                </div>
            <?php endif; ?>
            
        </div>

        <div class="metadata_detaillee">
            <table>
                <?php
                $metadata = [
                    "URI du NAS PAD" => $URIS['URI_NAS_PAD'],
                    "URI du NAS ARCH" => $URIS['URI_NAS_ARCH'],
                    "Durée" => $mtdTech["mtd_tech_duree"],
                    "Image par seconde" => $mtdTech["mtd_tech_fps"] . " fps",
                    "Résolution" => $mtdTech["mtd_tech_resolution"],
                    "Format" => $mtdTech["mtd_tech_format"],
                    "Projet" => $mtdEdito["projet"],
                    "Promotion" => $promotion,
                    "Professeur référent" => $mtdEdito["professeur"]
                ];
                foreach ($metadata as $key => $value) {
                    echo "<tr>";
                    echo "<td><strong>$key</strong></td>";
                    echo "<td>$value</td>";
                    echo "</tr>";
                }
                if($mtdRoles!=null){
                    foreach ($mtdRoles as $role => $values) { 
                        echo "<tr>";
                        echo "<td><strong>". htmlspecialchars($role) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($values) . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>


<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        initLectureVideo();
        pageLectureVideo();
    });

</script>