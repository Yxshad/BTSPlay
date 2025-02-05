<?php

require_once "../fonctions/controleur.php";

$listeProfesseurs = controleurRecupererListeProfesseurs();

?>
<aside class="filtres">
    <form action="recherche.php" method="POST">
        <div>
            <label>Promotion</label>
            <input type="number" name="annee">
        </div>
        <div>
            <label>Theme</label>
            <input type="number" name="niveau">
        </div> 
        <div>
            <label>Professeur</label>
            <select name="prof">
                <option value=""></option>
                <?php foreach ($listeProfesseurs as $prof) { ?>
                    <option value="<?php echo $prof; ?>"><?php echo $prof; ?></option>
                <?php } ?>
            </select>
        </div>
        <input value="Rechercher" type="submit">
    </form>
    <button class="afficherFiltres"> > </button>
</aside>

<div class="voile"></div>