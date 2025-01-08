<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/transfert.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php include '../ressources/Templates/header.php';?>

<div class="container">
    <div class="colonnes">
        <div class="colonne-1">
            <h1>Transferts</h1>
            <div class="transferts">
                <div class="lignes">
                    <?php for ($i=0; $i < 5; $i++) { ?>
                        <div class="ligne">
                            <div class="fleches">
                                <a class="fleche-haut">
                                    <img src="../ressources/Images/arrow.png" alt="flèche">
                                </a>
                                <a class="fleche-bas">
                                    <img src="../ressources/Images/arrow.png" alt="flèche">
                                </a>
                            </div>
                            <div class="imgVideo">
                                <img src="../ressources/Images/imgVideo.png" alt="">
                            </div>
                            <div class="info">
                                <p class="nomVideo">video.mpeg<?php echo $i; ?></p>
                                <p class="poidsVideo">20 go</p>
                            </div>
                            <div class="progress">
                                <div class="valeur">0</div>
                                %
                            </div>
                            <div class="bouton">
                                <a class="pause">
                                    <img src="../ressources/Images/pause.png" alt="pause">
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="commande">
                    <p>Commande de conversion</p>
                    <input type="text" placeholder="ffmpeg -i $video 2>&1">
                </div>
                
            </div>
        </div>
        <div class="symbole">
            >
        </div>
        <div class="colonne-2">
            <h2>Vidéos en attente de métadonnées</h2>
            <div class="dates">
                <div class="nomColonne">
                    <p>Date</p>
                    <p>Nom</p>
                </div>
                <?php for ($i=0; $i < 6; $i++) { ?>
                    <div class="ligne">
                        <div>
                            <p>04/05/2025 17:42</p>
                            <p>vidéo.mpeg</p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
const data = new URLSearchParams();
data.append('action', 'scanDecoupe');
fetch('../fonctions/fonctions.php', {
        method: 'POST',
        body: data
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(result => {
        console.log(result); // Afficher le résultat dans la console
        // Traitez le résultat comme nécessaire
    })
    .catch(error => {
        console.error('Erreur lors de la requête:', error);
    });


</script>