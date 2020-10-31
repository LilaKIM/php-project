<?php
session_start();
$_SESSION['nom']=$_POST['nom'];
$_SESSION['prenom']=$_POST['prenom'];
$_SESSION['id_utilisateur']=$_POST['id'];
$_SESSION['mdp']=$_POST['mdp'];
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ACCUEIL</title>
	
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link rel="stylesheet" href="assets/materialize/css/materialize.min.css" media="screen,projection" />
    <!-- Bootstrap Styles-->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FontAwesome Styles-->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- Morris Chart Styles-->
    <link href="assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
    <!-- Custom Styles-->
    <link href="assets/css/custom-styles.css" rel="stylesheet" />
    <!-- Google Fonts-->
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
				  <li><a class="dropdown-button waves-effect waves-dark" href="#!" data-activates="dropdown1"><i class="fa fa-user fa-fw"></i> <b><?php
						$nom=$_SESSION['nom'];
						$prenom=$_SESSION['prenom'];echo"$prenom";?></b> <i class="material-icons right">arrow_drop_down</i></a></li>
            </ul>
        </nav>
		<!-- Dropdown Structure -->
<ul id="dropdown1" class="dropdown-content">
<li><a href="#"><i class="fa fa-user fa-fw"></i> My Profile</a>
</li>
<li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
</li> 
<li><a href="#"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
</li>
</ul>
  
  <li>
                            
	   <!--/. NAV TOP  -->
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">

                    
					<?php
						strtolower($nom);
						strtolower($prenom);
						$id=$_SESSION['id_utilisateur'];
						$mdp=$_SESSION['mdp'];
						$bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
						$identification=$bdd->prepare('SELECT * FROM utilisateurs 
										WHERE id=:id AND nom=:nom AND prenom=:prenom');
						$identification->execute(array('id'=>$id,'nom'=>$nom,'prenom'=>$prenom));
						$userinfo=$identification->fetch();
						$identification->closeCursor();
						# saisies incorrectes
						# redirigé vers la page de connexion/d'inscription
						if (! $userinfo) {
							echo "Connexion échouée, veuillez vérifier les informations saisies.<br>";
							echo "<a href='page_connexion.php'>Retour à la page de connexion</a>.<br>";
							echo "Ou s'inscrire.<br>";
						}
						# première connexion d'un invité
						# enregistrement de mot de passe, changement de statut
						elseif (! $userinfo['mot_de_passe']) {
							$update=$bdd->prepare('UPDATE utilisateurs SET mot_de_passe=:mdp,statut="annotateur" 
											WHERE id=:id');
							$update->execute(array('mdp'=>$mdp,'id'=>$id));
							$update->closeCursor();
							echo "<br>Votre mot de passe est bien enregistré.<br>";
							echo "<br>Vous pouvez désormais commencer à travailler.<br>";
						}
						$statut=$userinfo['statut'];
						#echo "<h1>Bienvenue ".strtoupper($nom)." ".ucfirst($prenom)." !</h1>";
						$acces=$bdd->prepare('SELECT * FROM acces WHERE statut=:statut');
						$acces->execute(array('statut'=>$statut));
						$useracces=$acces->fetch();
						$acces->closeCursor();
						#print_r($useracces);
						#print_r($useracces['accueil']);
						if (! $useracces['accueil']) {
							echo "<br>Votre demande est en cours, veuillez réessayer ultérieurement.<br>";
						}
						else {
							#echo "<h1>Accueil</h1>";
							# menu généré automatiquement (rubrique selon $useracces)
							menu_auto_acces("connexion, accueil, annotation, visualisation_accord, visualisation_annotation, administration", $useracces);

							# présentation du projet
							# description du corpus traité
						}
					?>
					<?php
						# Menu automatique (sep=', ')
						function menu_auto_acces($rubriques_possibles, $useracces){
							$rubs = explode(", ", $rubriques_possibles);
							
							foreach($rubs as $value){
								if ($useracces[$value] == 1){
									echo "<li><a href=\"page_".$value.".php\" class=\"waves-effect waves-dark\">".ucfirst($value)."</a></li>";
								}
							}
						}
					?>

                </ul>

            </div>

        </nav>
        <!-- /. NAV SIDE  -->
      
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

			
                <div class="row">
                    <div class="col-md-4 col-sm-4">
                   <div class="card teal">
						<div class="card pink darken-1">
						<div class="card-content white-text">
						  <span class="card-title">Annotation</span>
						  <p>Démarrer votre travail.</p>
						</div>
						<div class="card-action">
						  <a href="#">This is a link</a>
						</div>
						</div>
					 </div>
                </div>
                <div class="col-md-4 col-sm-4">
                   <div class="card">
						<div class="card-content">
						  <span class="card-title">Visualisation Accord</span>
						  <p>Visualiser l'accord</p>
						</div>
						<div class="card-action">
						  <a href="#">This is a link</a>
						</div>
					  </div>
                </div>
                <div class="col-md-4 col-sm-4">
                        <div class="card blue-grey darken-1">
						<div class="card-content white-text">
						  <span class="card-title">Visualisation Annotation</span>
						  <p>Visualiser l'annotation</p>
						</div>
						<div class="card-action">
						  <a href="#">This is a link</a>
						</div>
					  </div>
                </div>
                </div>			           	   												        				
        
				</footer>
            </div>
            <!-- /. PAGE INNER  -->
        </div>
        <!-- /. PAGE WRAPPER  -->
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