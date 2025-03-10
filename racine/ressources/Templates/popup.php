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