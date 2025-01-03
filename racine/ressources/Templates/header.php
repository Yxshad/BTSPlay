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

            <form class="recherche">
                <input type="search" placeholder="Barre de recherche">
                <button>
                    <div class="logo-search">
                        <img src="../ressources/Images/loupe.png" alt="Rechercher">
                    </div>
                </button>
            </form>
            
            <div class="compte">
                <a href="compte.php">

                    <?php 
                    if(isset($_SESSION["username"])){
                        echo $_SESSION["username"] ;
                    }else{
                        echo "Se connecter";
                    }
                    ?>
                    
                    <div class="logo-compte">
                        <img src="../ressources/Images/account.png" alt="Compte">
                    </div>
                </a>
            </div>
        </div>
    </header>