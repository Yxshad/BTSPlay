


document.addEventListener("DOMContentLoaded", function(event) {
    if (document.querySelector('.afficherFiltres')) {
        document.querySelector('.afficherFiltres').addEventListener('click', (e) => {
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
    }


    if(document.querySelector('.transferts')){
        // Fonction pour déplacer une ligne vers le haut
        function moveUp(button) {
            const ligne = button.parentElement.parentElement; // Trouver la ligne actuelle
            const previousLigne = ligne.previousElementSibling; // Trouver la ligne précédente
    
            if (previousLigne.classList.contains('ligne')) {
                let infoLigne = ligne.innerHTML
                let infoPreviousLigne = previousLigne.innerHTML
    
                ligne.innerHTML = infoPreviousLigne;
                previousLigne.innerHTML = infoLigne;
            }
    
            document.querySelectorAll('.fleche-haut').forEach(button => {
                button.addEventListener('click', function () {
                    moveUp(this); // Passer le bouton cliqué à la fonction
                });
            });
    
            document.querySelectorAll('.fleche-bas').forEach(button => {
                button.addEventListener('click', function () {
                    moveDown(this); // Passer le bouton cliqué à la fonction
                });
            });
        }
    
        // Fonction pour déplacer une ligne vers le bas
        function moveDown(button) {
            const ligne = button.parentElement.parentElement; // Trouver la ligne actuelle
            const nextLigne = ligne.nextElementSibling; // Trouver la ligne suivante
    
            if (nextLigne.classList.contains('ligne')) {
                let infoLigne = ligne.innerHTML
                let infoNextLigne = nextLigne.innerHTML
    
                ligne.innerHTML = infoNextLigne;
                nextLigne.innerHTML = infoLigne;
            }
    
            document.querySelectorAll('.fleche-haut').forEach(button => {
                button.addEventListener('click', function () {
                    moveUp(this); // Passer le bouton cliqué à la fonction
                });
            });
    
            document.querySelectorAll('.fleche-bas').forEach(button => {
                button.addEventListener('click', function () {
                    moveDown(this); // Passer le bouton cliqué à la fonction
                });
            });
        }
    
        // Ajouter des gestionnaires d'événements à toutes les flèches
        document.querySelectorAll('.fleche-haut').forEach(button => {
            button.addEventListener('click', function () {
                moveUp(this); // Passer le bouton cliqué à la fonction
            });
        });
    
        document.querySelectorAll('.fleche-bas').forEach(button => {
            button.addEventListener('click', function () {
                moveDown(this); // Passer le bouton cliqué à la fonction
            });
        });
    }
});