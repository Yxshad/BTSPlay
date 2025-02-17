<?php

$directory = __DIR__ . "/../../stockage";

?>


<div class="main-menuArbo">
    <div class="menuArbo">
        <?php echo controleurArborescence($directory); ?>
    </div>
    <button onclick="ouvrirMenuArbo()">></button>
</div>





<script>
    document.addEventListener('DOMContentLoaded', function() {
        gestion_click_dossier();
    });

    function ouvrirMenuArbo(){
        let mainMenu = document.querySelector(".main-menuArbo");
        if (mainMenu.classList.contains('ouvert')) {
            mainMenu.classList.remove('ouvert');
        } else {
            mainMenu.classList.add('ouvert');
        }
    }
</script>