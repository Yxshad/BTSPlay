<div class="popup">
    <button class="cross" onclick="retirerPopUp()">X</button>
    <h1><?php echo htmlspecialchars($titre); ?></h1>
    <p class="explication"><?php echo $explication; ?></p>
    <div class="btns">
        <button id="btn1"><?php echo $btn1; ?></button>
        <button id="btn2"><?php echo $btn2; ?></button>
    </div>
</div>

<div class="voile-popup"></div>