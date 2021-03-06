﻿<?php
session_start();
if ($_POST['mdp']) {
    $_SESSION['nom'] = $_POST['nom'];
    $_SESSION['prenom'] = $_POST['prenom'];
    $_SESSION['id_utilisateur'] = $_POST['id'];
    $_SESSION['mdp'] = $_POST['mdp'];
}
?>
<?php
    # Vérification des informations entrées
    $nom=strtolower($_SESSION['nom']);
    $prenom=strtolower($_SESSION['prenom']);
    $id_utilisateur=$_SESSION['id_utilisateur'];
    $mdp=$_SESSION['mdp'];
    # L'utilisateur existe-t-il dans la BDD ?
    $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
    $identification=$bdd->prepare('SELECT * FROM utilisateurs 
                    WHERE id=:id AND nom=:nom AND prenom=:prenom');
    $identification->execute(array('id'=>$id_utilisateur,'nom'=>$nom,'prenom'=>$prenom));
    $userinfo=$identification->fetch();
    $identification->closeCursor;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Accueil</title>
        <link rel="shortcut icon" href="images/icone.png"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="stylesheet" href="assets/materialize/css/materialize.min.css" media="screen,projection" />
        <link href="assets/css/bootstrap.css" rel="stylesheet" />
        <link href="assets/css/font-awesome.css" rel="stylesheet" />
        <link href="assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
        <link href="assets/css/custom-styles.css" rel="stylesheet" />
        <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
        <link rel="stylesheet" href="assets/js/Lightweight-Chart/cssCharts.css">
    </head>
    <body>
    <div id="wrapper">
        <nav class="navbar navbar-default top-navbar" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle waves-effect waves-dark" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand waves-effect waves-dark" href="page_accueil.php"><i class="large material-icons">insert_chart</i> <strong>ACCUEIL</strong></a>
                <div id="sideNav" href=""><i class="material-icons dp48">toc</i></div>
            </div>
            <ul class="nav navbar-top-links navbar-right">
                <li><a class="dropdown-button waves-effect waves-dark" href="#!" data-activates="dropdown1">
                    <i class="fa fa-user fa-fw"></i>
                    <b>
                        <?php
                        echo strtoupper($nom)." ".ucfirst($prenom)." (".$id_utilisateur.")";
                        ?>
                    </b>
                    <i class="material-icons right">arrow_drop_down</i>
                </a></li>
            </ul>
        </nav>
        <ul id="dropdown1" class="dropdown-content">
            <li><a href="page_connexion.php"><i class="fa fa-sign-out fa-fw"></i> Se déconnecter</a></li>
        </ul>
        <div id="page-wrapper">
            <div class="header">
                <h1 class="page-header">
                    ACCUEIL
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#">ACCUEIL</a></li>
                </ol>
            </div>
            <div id="page-inner">
                <?php
                    # Si l'utilisateur n'existe pas => Nom/Prénom/Identifiant incorrect
                    # Redirigé vers la page de connexion
                    if (! $userinfo) {
                        echo "
                        <div class=\"alert alert-warning\">
    						Connexion échouée, veuillez vérifier les informations saisies et réessayer.
    						<a href='page_connexion.php'>Page de connexion</a>
					    </div>
					    ";
                    }
                    # Si l'utilisateur existe, mais le MdP est vide => Première connexion d'un invité
                    # Enregistrement de MdP & Changement de statut vers l'annotateur
                    elseif (! $userinfo['mot_de_passe']) {
                        $update=$bdd->prepare('UPDATE utilisateurs SET mot_de_passe=:mdp,statut="annotateur" 
                                WHERE id=:id');
                        $update->execute(array('mdp'=>$mdp,'id'=>$id_utilisateur));
                        $update->closeCursor();
                        echo "
                        <div class=\"alert alert-info\">
    						Votre mot de passe est bien enregistré.
    						Rafraîchessez la page (F5), vous pouvez désormais commencer à travailler.
					    </div>
					    ";
                    }
                    # Si l'utilisateur existe, mais le MdP entré est incorrect
                    # Redirigé vers la page de connexion
                    elseif ($mdp != $userinfo['mot_de_passe']) {
                        echo "
                        <div class=\"alert alert-warning\">
    						Mot de passe incorrect, veuillez réessayer. <a href='page_connexion.php'>Page de connexion</a>
					    </div>
					    ";
                    }
					# Si tout les info sont correct
					# Affchicher la presentation
					elseif ($mdp == $userinfo['mot_de_passe']) {
						echo "
						<div class=\"row\">
                    
						<div class=\"col-md-12\">
						<div class=\"card\">
							<div class=\"card-action\">
							 Presentation
							</div>        
							 <div class=\"card-content\"> 
							 <p>Bienvenue à notre site qui est créé dans le cadre d'un projet pour le cours BDD et web dynamique. Nous sommes quatre étudiantes, Tian XIE, Lijing HUANG, Young-ju NA et Lila KIM. Le but de ce projet est de construire un site où nous pouvons faire des annotations et les résultats se rendent automatiquement dans une base de données. </p>
							 <p>Pour le moment, nous travaillons sur un corpus qui comprend 200 tweets et qui est construit par Jade Mekki.</p>
							   <div class=\"clearBoth\"><br/></div>
								
							 </div>
						</div>
						</div>				
					
						</div>
						
						";
					
					}
                ?>
            </div>
            <!-- /. PAGE INNER  -->
        </div>
        <!-- /. PAGE WRAPPER  -->
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                    <?php
                        # Vérification d'accès de l'utilisateur
                        $statut=$userinfo['statut'];
                        $acces=$bdd->prepare('SELECT * FROM acces WHERE statut=:statut');
                        $acces->execute(array('statut'=>$statut));
                        $useracces=$acces->setFetchMode(PDO::FETCH_ASSOC);
                        $useracces=$acces->fetchAll();
                        $acces->closeCursor();
                        # Menu automatique
                        function menu_auto_acces($rubs,$nom_rubs){
                            foreach ($rubs as $page=>$acces) {
                                if ($acces == 1){
                                    echo "<li><a href=\"page_".$page.".php\" class=\"waves-effect waves-dark\">".$nom_rubs[$page]."</a></li>";
                                }
                            }
                        }
                        $rubriques = array("annotation"=>"Annotation",
                            "visualisation_accord"=>"Visualisation de l'accord<br>inter annotateur",
                            "visualisation_annotation"=>"Visualisation de l'annotation",
                            "administration"=>"Administration");
                        menu_auto_acces($useracces[0],$rubriques);
                    ?>
                </ul>
            </div>
        </nav>
        <!-- /. NAV SIDE  -->
    </div>
    <!-- /. WRAPPER  -->

    <!-- JS Scripts-->
    <!-- jQuery Js -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- Bootstrap Js -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/materialize/js/materialize.min.js"></script>
    <!-- Metis Menu Js -->
    <script src="assets/js/jquery.metisMenu.js"></script>
    <!-- Morris Chart Js -->
    <script src="assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="assets/js/morris/morris.js"></script>
    <script src="assets/js/easypiechart.js"></script>
    <script src="assets/js/easypiechart-data.js"></script>
    <script src="assets/js/Lightweight-Chart/jquery.chart.js"></script>
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>

    </body>
</html>