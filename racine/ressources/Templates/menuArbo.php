<?php

$directory_local = __DIR__ . "/../../stockage";

?>


<div class="main-menuArbo">
    <div class="menuArbo local">
        <?php echo controleurArborescenceLocal($directory_local); ?>
    </div>
    <div class="menuArbo PAD">
        <?php echo controleurArborescencePAD(""); ?>
    </div>
    <div class="menuArbo ARCH">
        <?php echo controleurArborescenceARCH(""); ?>
    </div>

    <div class="radio">
        <label for="local">Local</label>
        <input type="radio" name="a" id="local">

        <label for="PAD">PAD</label>
        <input type="radio" name="a" id="PAD">

        <label for="ARCH">ARCH</label>
        <input type="radio" name="a" id="ARCH">
    </div>

    <button onclick="ouvrirMenuArbo()">></button>
</div>





<script>
    document.addEventListener('DOMContentLoaded', function() {
        gestion_click_dossier();
        gestionOngletsArborescence();
    });

    function ouvrirMenuArbo(){
        let mainMenu = document.querySelector(".main-menuArbo");
        if (mainMenu.classList.contains('ouvert')) {
            mainMenu.classList.remove('ouvert');
        } else {
            mainMenu.classList.add('ouvert');
        }
    }

    function gestionOngletsArborescence() {
        const radios = document.querySelectorAll('.radio input[type="radio"]');
        const menus = document.querySelectorAll('.menuArbo');
        
        function setActiveTab(tabId) {
            // Désactiver tous les menus
            menus.forEach(menu => menu.style.display = 'none');
            
            // Activer le menu correspondant à l'onglet sélectionné
            const activeMenu = document.querySelector(`.menuArbo.${tabId}`);
            if (activeMenu) {
                activeMenu.style.display = 'block';
            }
        }

        // Vérifie s'il y a un paramètre "tab" dans l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || "local"; // "local" par défaut

        // Activer l'onglet correspondant au paramètre de l'URL
        setActiveTab(activeTab);

        // Cocher le bouton radio correspondant
        const activeRadio = document.getElementById(activeTab);
        if (activeRadio) {
            activeRadio.checked = true;
        }

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                const tabId = radio.id;
                setActiveTab(tabId);

                // Met à jour l'URL sans recharger la page
                const newUrl = `${window.location.pathname}?tab=${tabId}`;
                window.history.pushState({ path: newUrl }, '', newUrl);
            });
        });
    }

    // Appeler la fonction pour initialiser la gestion des onglets
    
</script>