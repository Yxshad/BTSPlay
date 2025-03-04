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
            <path fill-rule="evenodd" d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
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