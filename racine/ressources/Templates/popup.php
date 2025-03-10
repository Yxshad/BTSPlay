<link href="../ressources/Style/popup.css" rel="stylesheet">
<div class="popup">
    <button class="cross" onclick="retirerPopUp()">X</button>
    <h1><?php echo $titre; ?></h1>
    <p class="explication"><?php echo $explication; ?></p>
    <div class="btns">
        <button id="btn1"><?php echo $btn1['libelle']; ?></button>
        <button id="btn2"><?php echo $btn2['libelle']; ?></button>
    </div>
</div>

<div class="voile-popup"></div>

<script>
    boutonsPopUp(JSON.parse('<?php echo json_encode($btn1)?>'), JSON.parse('<?php echo json_encode($btn2); ?>'));
</script>