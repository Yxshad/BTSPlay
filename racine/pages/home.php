<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/home.css" rel="stylesheet">
    <script src="../ressources/Script/scripts.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php include '../ressources/Templates/header.php'; //Appel du header ?>

<div class="filtres">
    
    <form action="">
        <div>
            <label>Année</label>
            <input type="number">
        </div>

        <div>
            <label>Niveau</label>
            <input type="number">
        </div>
        
        <input value="Rechercher" type="submit">
    </form>

    <button class="afficherFiltres">></button>
</div>

<div class="container">
    <div class="sliderVideo">
        <h2>Videos !!!</h2>
        <div class="swiperVideo">
            <div class="swiper-wrapper">
                <?php for ($i=0; $i < 30; $i++) { ?>
                    <div class="swiper-slide">
                        <a href="#">
                            <div class="miniature"></div>
                            <h3>Titre</h3>
                        </a>
                    </div>
                <?php } ?> 
            </div>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>

<div class="voile"></div>

<footer>
</footer>

<script>
    document.querySelector('.afficherFiltres').addEventListener('click', (e) => {
        console.log('salut');
        let filtres = document.querySelector('.filtres');
        let voile = document.querySelector('.voile');
        if(filtres.classList.contains('afficher')){
            filtres.classList.remove('afficher');
            voile.classList.remove('afficher');

        }
        else{
            filtres.classList.add('afficher');
            voile.classList.add('afficher');
        }
    });

    const swiperVideo = new Swiper('.swiperVideo', {
        speed: 400,
        spaceBetween: 100,
        slidesPerView: 3,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

</script>