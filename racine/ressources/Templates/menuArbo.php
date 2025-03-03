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

    <button onclick="ouvrirMenuArbo()">
        <svg fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
        </svg>
    </button>
</div>


<div class="voile"></div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        gestion_click_dossier();
        gestionOngletsArborescence();
    });    
</script>