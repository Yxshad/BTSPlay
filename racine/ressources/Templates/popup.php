<link href="../ressources/Style/popup.css" rel="stylesheet">
<div class="popup">
    <button class="cross" onclick="retirerPopUp()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
        </svg>
    </button>
    <h1><?php echo $titre; ?></h1>
    <hr/>
    <p class="explication"><?php echo $explication; ?></p>
    <div class="btns">
        <button id="btn1"><?php echo $btn1['libelle']; ?></button>
        <?php if ($btn2 != null) { ?>
            <button id="btn2"><?php echo $btn2['libelle']; ?></button>
        <?php } ?>
        
    </div>
</div>

<div class="voile-popup"></div>

<script>
    boutonsPopUp(JSON.parse('<?php echo json_encode($btn1)?>'), JSON.parse('<?php echo json_encode($btn2); ?>'));
</script>

<!--

Pour faire appartaitre la popup, il faut utiliser en php la fonction controleurPopUp($titre, $explication, $btn1, $btn2)
$titre et $explications sont des strings.

$btn1 et $btn2 ont une structure qui ressemble à ça : 

$btn1 = [
    "libelle" => "Oui!",
    "arguments" => [["action","supprimerVideo"], ["idvideo", "5"]]
];

Libelle sera le texte affiché sur le bouton.
arguments est une liste de paire de string, une paire correspond au nom de la variable envoyé en post et le deuxième est la valeur de la variable.
Dans cette exemple, le $btn1 envera dans le fichier controleur.php les variables $_POST['action'] = 'supprimerVideo' et $_POST['idvideo'] = '5'

Si l'appel de la popup doit se faire après l'action de l'utilisateur, on peut faire la même chose avec du js avec l'attribut onclick
alors, la déclaration de btn1 se fera comme ça:

let btn1 = {
    libelle : "Oui!",
    arguments : [["action","supprimerVideo"], ["idvideo", "5"]]
}

-->