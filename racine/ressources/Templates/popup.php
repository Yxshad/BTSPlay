<link href="../ressources/Style/popup.css" rel="stylesheet">
<div class="popup">
    <button class="cross" onclick="cacherPopup()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
        </svg>
    </button>
    <h1><?php echo $nouveauTitre; ?></h1>
    <hr/>
    <p class="explication"><?php echo $nouveauTexte; ?></p>
    <div class="btns">
        <button class="btn1" onclick="btn1(); cacherPopup();" data-fonctions="" data-args="">Confirmer</button>
        <button class="btn2" onclick="btn2(); cacherPopup()" data-fonctions="">Annuler</button>
    </div>
</div>

<div class="voile-popup"></div>
