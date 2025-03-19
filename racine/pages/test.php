<?php require_once "../fonctions/controleur.php";
      require_once "../fonctions/ffmpeg.php";
    function ddd($array){
        if ($array) {
            foreach($array as $ligne){
                var_dump($ligne);
                echo "<br/>";
            }
        }else{
            print_r("No data");
        }
        echo "<br/>";
    }
    
?>