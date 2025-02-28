<?php

$directory_local = __DIR__ . "/../../stockage";

?>


<div class="main-menuArbo">
    <div class="dossiers">
        <div class="menuArbo local">
            <?php echo controleurArborescenceLocal($directory_local); ?>
        </div>
        <div class="menuArbo PAD">
            <?php echo controleurArborescencePAD(""); ?>
        </div>
        <div class="menuArbo ARCH">
            <?php echo controleurArborescenceARCH(""); ?>
        </div>
    </div>

    <div class="radio">
        <label>
            Stockage local
            <input type="radio" name="a" id="local">
        </label>

        <label>
            PAD
            <input type="radio" name="a" id="PAD">
        </label>

        <label>
            ARCH
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