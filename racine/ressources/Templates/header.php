<link href="../ressources/Style/header.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <a href="home.php">
                <div class="logo-bts">
                    <img src="../ressources/Images/logo_BTS_Play.png" alt="logo">
                </div>
            </a>

            <!-- Formulaire de recherche principale -->
            <form class="recherche" action="../pages/recherche.php" method="GET">
                <input type="search" name="motCle" placeholder="Rechercher une vidéo..." >
                <button type="submit">
                    <div class="logo-search">
                        <img src="../ressources/Images/recherche.png" alt="Rechercher">
                    </div>
                </button>
            </form>
            
            <div class="compte">
                <?php if(!isset($_SESSION["loginUser"])){ ?>
                    <a href="compte.php">                 
                        Se connecter
                        <div class="logo-compte">
                            <img src="../ressources/Images/account.png" alt="Compte">
                        </div>
                    </a>
                <?php }else{ ?>

                    <a class="btnSousMenu" onclick="affichageSousMenu()">
                        <?php echo $_SESSION["loginUser"]; ?>
                        <div class="logo-compte">
                            <img src="../ressources/Images/account.png" alt="Compte">
                        </div>
                    </a>
                    <div class="sousMenu">

                        <?php
                        if(controleurVerifierAcces(ACCES_ADMINISTRATION)){ ?>
                            
                        <a href="pageAdministration.php">
                            <img class='iconeSousMenu' src='../ressources/Images/Parametre.png'>    
                            Paramétrer
                        </a>
                        <?php } ?>

                        <a href="logout.php" >
                            <img class='iconeSousMenu'src='../ressources/Images/logout.png'>
                            Se déconnecter
                        </a>

                    </div>
                <?php } ?>
            </div>
        </div>
    </header>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        affichageSousMenu();
    });
    </script>