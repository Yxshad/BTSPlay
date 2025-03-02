<?php

$directory_local = __DIR__ . "/../../stockage";

?>


<div class="main-menuArbo">
    <div class="dossiers">
        <div class="menuArbo local">
            <?php echo controleurArborescence($directory_local, ESPACE_LOCAL); ?>
        </div>
        <div class="menuArbo PAD">
            <?php echo controleurArborescence("", NAS_PAD); ?>
        </div>
        <div class="menuArbo ARCH">
            <?php echo controleurArborescence("", NAS_ARCH); ?>
        </div>
    </div>

    <div class="radio">
        <label>
            Stockage local
            <input type="radio" name="a" id="local">
        </label>

        <label>
            NAS PAD
            <input type="radio" name="a" id="PAD">
        </label>

        <label>
            NAS ARCH
            <input type="radio" name="a" id="ARCH">
        </label>
        
    </div>

    <button onclick="ouvrirMenuArbo()">></button>
</div>


<div class="voile"></div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        gestion_click_dossier();
        gestionOngletsArborescence();
    });    
</script>
