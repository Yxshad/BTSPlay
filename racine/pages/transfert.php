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

<?php 
include '../ressources/Templates/header.php';
?>

<div class="container">
    <div class="colonnes">
        <div class="colonne-1">
            <h1>Transferts</h1>
            <div class="transferts">
                <div class="lignes">
                    <!-- Résultat ajax -->
                </div>
                <div class="commande">
                    <p>Commande de conversion</p>
                    <input type="text" placeholder="ffmpeg -i $video 2>&1">
                    <a class="btn" onclick="lancerConvertion()">Lancer conversion</a>
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
function lancerConvertion() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        console.log(this.responseText);
    }
    xhttp.open("POST", "../fonctions/fonctions.php");
    
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhttp.send("action=lancerConvertion");
}

function ajax() {
  const xhttp = new XMLHttpRequest();
  xhttp.onload = function() {
    document.querySelector('.transferts .lignes').innerHTML = this.responseText;
  }
  xhttp.open("POST", "../fonctions/fonctions.php");
  
  xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhttp.send("action=scanDecoupe");
}

ajax();

setInterval( ajax , 5000);

</script>
