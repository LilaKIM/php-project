<?php
session_start();
?>
<?php
    $id_utilisateur=$_SESSION['id_utilisateur'];
    $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
    $identification=$bdd->prepare('SELECT * FROM utilisateurs WHERE id=:id');
    $identification->execute(array('id'=>$id_utilisateur));
    $userinfo=$identification->fetch();
    $identification->closeCursor;
    $nom=$userinfo['nom'];
    $prenom=$userinfo['prenom'];
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Visualisation l'accord inter annotateur</title>
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
                    ANNOTATION
                </h1>
            </div>
            <div id="page-inner">
<?php
# Premier extrait sans aucune annotation, valide ou non
$interogeextrait=$bdd->prepare('SELECT * FROM corpus
WHERE corpus.id_extrait NOT IN (SELECT id_extrait FROM annotation
WHERE annotation.id_utilisateur=:id_utilisateur)');
$interogeextrait->execute(array('id_utilisateur'=>$id_utilisateur));
$extrait=$interogeextrait->fetch();
if (! $extrait) {
$texte="Vous avez annoté tous les extraits. Procédez à la visualisation.";
}
else {
$texte=$extrait['extrait'];
$id_extrait=$extrait['id_extrait'];
}
?>

                <div class="row">
                    <div class="col-md-8 col-sm-8">
                        <div class="card">
                            <div class="card-content">
                                <?php
                                echo "
                                <span class=\"card-title\">Tweet n°$id_extrait</span>
                                <p>$texte</p>
                                ";
                                ?>
                            </div>
                        </div>
				</div>
                </div>
				<div class="row">
                    <?php
                    echo "
					<form method=\"post\" action=\"page_annotation.php\">
					<div class=\"col-md-5 col-sm-5\">
                    <div class=\"card\">
                        <div class=\"card-action\">
                            Annotation
                        </div>
                        <div class=\"card-content\">
                           
							<ul class=\"collapsible\" data-collapsible=\"accordion\">
									
									<input type=\"hidden\" name=\"id_extrait\" value=$id_extrait>
									<input type=\"hidden\" name=\"id_utilisateur\" value=$id_utilisateur>
									";
									$registres=array("soutenu","courant","familier","poubelle");
									$proportions=array();
									$registre_descripteurs=array();
									foreach ($registres as $registre) {
										echo "
										<li>
										<div class=\"collapsible-header\">$registre</div>
										<div class=\"collapsible-body\">
										
										
										<p>
										<input type=\"radio\" name=\"proportions[$registre]\" value=0 id=\"$registre 0 %\" checked><label for=\"$registre 0 %\"> 0 %</label>
										<input type=\"radio\" name=\"proportions[$registre]\" value=1 id=\"$registre 25 %\" ><label for=\"$registre 25 %\"> 25 %</label>
										<input type=\"radio\" name=\"proportions[$registre]\" value=2 id=\"$registre 50 %\" ><label for=\"$registre 50 %\"> 50 %</label>
										<input type=\"radio\" name=\"proportions[$registre]\" value=3 id=\"$registre 75 %\" ><label for=\"$registre 75 %\"> 75 %</label>
										<input type=\"radio\" name=\"proportions[$registre]\" value=4 id=\"$registre 100 %\"><label for=\"$registre 100 %\"> 100 %</label>
										</p><p>
										";
										$descripteurs=$bdd->query('SELECT id_descripteur, descripteur FROM descripteurs');
										while ($descrips=$descripteurs->fetch()) {
											$id_descrip=$descrips['id_descripteur'];
											$descrip=$descrips['descripteur'];
											echo "
											
											<input type=\"checkbox\" name=\"registre_descripteurs[$registre][]\" value=$id_descrip class=\"filled-in\" id=\"$registre $id_descrip\" >
											<label for=\"$registre $id_descrip\"> $id_descrip $descrip</label></p>
										";}
									echo"

                                        </li>";}
                        echo"          
							</div></ul>
							 </button>							
                            </div>
                        </div>
					
					
					<div class=\"col-lg-5\">
                    <div class=\"card\">
                        <div class=\"card-action\">
                            Signaler un problème :
                        </div>
                        <div class=\"card-content\">
								
								
									<p><input type=\"checkbox\" class=\"filled-in\" id=\"errid1\" name=\"problemes[]\" value=\"erreur de formatage\"/>
									<label for=\"errid1\">Erreur de formatage</label>
									<input type=\"checkbox\" class=\"filled-in\" id=\"errid2\" name=\"problemes[]\" value=\"mauvaise qualité de texte\"/>
									<label for=\"errid2\">Mauvaise qualité de texte</label></p>
								
								
								
								  
									<div class=\"input-field col s12\">
									  <input id=\"autre\" name=\"autreprobleme\" type=\"text\" class=\"validate\">
									  <label for=\"autre\">Autre</label>
									</div>
								  
								<div class=\"clearBoth\"></div>
						</div>	
					</div></div><input type=\"submit\" name=\"extraitannote\" value=\"Suivant\">								  
							</form>";
							?>




                </div>


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
	<?php
    if (isset($_POST['extraitannote'])) {
        $id_extrait=$_POST['id_extrait'];
        $id_utilisateur=$_POST['id_utilisateur'];
        $proportions=$_POST['proportions'];
        $registre_descripteurs=$_POST['registre_descripteurs'];
        $problemes=$_POST['problemes'];
        # à faire : faut vérifier la somme de proportion est bien 4 (signifie 100%), sinon, alerte et retourner
        # à faire : faut vérifier proportion>0 <=> descripteur(s) choisi(s)
        $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
        $insertanno=$bdd->prepare('INSERT INTO annotation (id_extrait,id_utilisateur)
                        VALUES (:id_extrait,:id_utilisateur)');
        $insertanno->execute(array('id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
        $insertanno->closeCursor();
        $insertpro=$bdd->prepare('UPDATE annotation SET pro_s=:s,pro_c=:c,pro_f=:f,pro_p=:p
                        WHERE id_extrait=:id_extrait AND id_utilisateur=:id_utilisateur');
        $insertpro->execute(array('s'=>$proportions['soutenu'],'c'=>$proportions['courant'],
            'f'=>$proportions['familier'],'p'=>$proportions['poubelle'],
            'id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
        $insertpro->closeCursor();
        # à faire : essayer de le réaliser dans un boucle

        # Insertion de descripteurs par registre
        $registres=array("soutenu","courant","familier","poubelle");
        $reg_descrips=array();
        foreach ($registres as $registre) {
            $reg_descrips[$registre]=implode(";",$registre_descripteurs[$registre]);
        }
        $insertdes=$bdd->prepare('UPDATE annotation SET des_s=:s,des_c=:c,des_f=:f,des_p=:p
                        WHERE id_extrait=:id_extrait AND id_utilisateur=:id_utilisateur');
        $insertdes->execute(array('s'=>$reg_descrips['soutenu'],'c'=>$reg_descrips['courant'],
            'f'=>$reg_descrips['familier'],'p'=>$reg_descrips['poubelle'],
            'id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
        $insertdes->closeCursor();
        # à faire : insertion de problèmes signalés
        echo "<script>location.href='page_annotation.php';</script>";
    }
    ?>
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