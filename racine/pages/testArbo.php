<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['path'])) {
    $path = $_POST['path'];
    scan($path);
    exit;
}

function scan($directory){
    $items = scandir($directory);

    foreach ($items as $item) {
        if ($item == '.' || $item == '..' || $item == '.gitkeep') {
            continue;
        }
        
        $path = $directory . '/' . $item;
        if (is_dir($path)) {
            afficherDossier($path, $item);
        } elseif (isVideo($item)) {
            afficherVideo($path, $item);
        } else {
            afficherFichier($path, $item);
        }
    }
}

function isVideo($file) {
    $videoExtensions = ['mp4', 'mxf'];
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $videoExtensions);
}

function afficherDossier($path, $item){ ?>
    <div style="background: red;" data-path ="<?php echo $path; ?>" class="dossier">
        <?php echo $item; ?>
    </div>
<?php }

function afficherVideo($path, $item){ ?>
    <div style="background: blue;" data-path ="<?php echo $path; ?>" class="video">
        <?php echo $item; ?>
    </div>
<?php }

function afficherFichier($path, $item){ ?>
    <div style="background: yellow;" data-path ="<?php echo $path; ?>" class="fichier">
        <?php echo $item; ?>
    </div>
<?php }

$directory = __DIR__ . "/../stockage";

echo scan($directory);

?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        gestion_click_dossier();
    });

    function gestion_click_dossier(){
        const dossiers = document.querySelectorAll('.dossier');
        dossiers.forEach(dossier => {
            if (!dossier.hasListener) {
                dossier.addEventListener('click', function(event) {
                    if (event.target === dossier) {
                        const path = dossier.getAttribute('data-path');
                        console.log(path);
                        if(dossier.style.background === 'red') {
                            dossier.style.background = 'green';
                            dossier.classList.add('ouvert');
                            fetch('testArbo.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: 'path=' + encodeURIComponent(path)
                            })
                            .then(response => response.text())
                            .then(data => {
                                dossier.innerHTML += data;
                                gestion_click_dossier();
                            });
                        } else {
                            dossier.style.background = 'red';
                            dossier.classList.remove('ouvert');
                            while(dossier.childElementCount > 0) {
                                dossier.removeChild(dossier.lastChild);
                            }
                            gestion_click_dossier();
                        }
                    }
                });
                dossier.hasListener = true;
            }
        });
    }
</script>

<style>
    .dossier::before{
        background: url("../ressources/images/closed-file.png");
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        content: " ";
        display: inline-block;
        width: 30px;
        height: 30px;
    }

    .dossier.ouvert::before{
        background: url("../ressources/images/open-file.png");
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
    }

    .dossier, .video, .fichier{
        margin: 5px;
        margin-left: 20px;
    }

    body > div{
        font-size: 2em;
    }
</style>