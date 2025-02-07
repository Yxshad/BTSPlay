<link href="../ressources/Style/header.css" rel="stylesheet">
<script src="../ressources/Script/script.js"></script>
</head>
<body>
    <header>
        <div class="container">
            <a href="home.php">
                <div class="logo-bts">
                    <img src="../ressources/Images/logo_BTS_Play.png" alt="logo">
                </div>
            </a>

            <form class="recherche">
                <input type="search" placeholder="Barre de recherche">
                <button>
                    <div class="logo-search">
                        <img src="../ressources/Images/loupe.png" alt="Rechercher">
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

                    <a class="btnSousMenu">
                        <?php echo $_SESSION["loginUser"]; ?>
                        <div class="logo-compte">
                            <img src="../ressources/Images/account.png" alt="Compte">
                        </div>
                    </a>
                    <div class="sousMenu">

                        <?php if(controleurVerifierAcces(ACCES_ADMINISTRATION)){ ?>
                            <a id="lienAdmin" href="pageAdministration.php">Page d'administration</a>
                        <?php } ?>

                        <a href="logout.php">
                            <div class="logo-compte">
                                <img src="../ressources/Images/logout.png" alt="Compte">
                            </div>
                            Déconnecter
                        </a>

                    </div>
                <?php } ?>
            </div>
        </div>
    </header>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log("salut");
        affichageSousMenu();
    });
    </script>
