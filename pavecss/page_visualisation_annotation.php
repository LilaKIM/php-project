<?php
session_start();
?>
<?php
    $id_utilisateur=$_SESSION['id_utilisateur'];
    $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
    $identification=$bdd->prepare('SELECT * FROM utilisateurs WHERE id=:id');
    $identification->execute(array('id'=>$id_utilisateur));
    $userinfo=$identification->fetch();
    $identification->closeCursor();
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
                    Visualisation - l'annotation
                </h1>
            </div>
            <div id="page-inner">
	
        <?php
           
            # Menu automatique vers page d'accueil et visualisation_accord            

            # Test pour voir si l'utilisateur est admin 
            # Si admin, on pourrait choisir l'id des autres pour regarder leurs annotations          
            $statut = $bdd->query('SELECT statut FROM utilisateurs WHERE id LIKE '.$id_utilisateur);
            while($data = $statut -> fetch()){$statut_utilisateur = $data['statut'];}
            // if ($statut_utilisateur == 'admin'){echo "admin";}else{echo $statut_utilisateur;}
            $statut->closeCursor();

            #boutons permettant de selectionner un des critères (portion/taille_text/descriptieurs)
            echo "
            <form method=\"post\">
                <INPUT type=\"submit\" class=\"waves-effect waves-light red lighten-2 btn\" name=\"proportion\" value=\"Proportion\" >
                <INPUT type=\"submit\" class=\"waves-effect waves-light red lighten-2 btn\" name=\"taille_text\" value=\"Taille du text\">
                <INPUT type=\"submit\" class=\"waves-effect waves-light red lighten-2 btn\" name=\"descripteurs\" value=\"Descripteurs\" >
            </form>";
            
            # si bouton 'proportion' cliqué : on affiche une table contenant des registres avec de différentes proportions à choisir
            # après bouton 'rechercher', on n'a plus de cette table de choix... à modifier
			?>
			<div class="col">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-content">
			
			<?php
			
            if(isset($_POST['proportion'])){
                proportion($id_utilisateur, $statut_utilisateur, $bdd);
            }

            # si bouton 'taille text' cliqué : une table des extraits de taille indiquée s'affiche (id n'intervient pas)
            elseif(isset($_POST['taille_text'])){
                taille_txt($bdd);
            }

            # si bouton 'descripteurs' cliqué : on a une table des descripteurs à choix multiples
            elseif(isset($_POST['descripteurs'])){
                descripteurs($id_utilisateur, $statut_utilisateur, $bdd);
            }
        ?>
		</div>
                        </div>
                    </div>
                    <!--/Annotation : descripteurs-->
                </div>
        <?php
            function proportion($id_utilisateur, $statut, $bdd){
                $registres=array("soutenu","courant","familier","poubelle");
                ##Pourquoi ça ne fonctionne pas quand post ?
                echo "<form method=\"get\">";
                foreach ($registres as $value){
                    echo "<p><b>".ucfirst($value).": </b></p>
                    <INPUT class=\"red lighten-2\" type=\"radio\" name=\"registre_proportion[$value]\" id = \"$value 0 %\" value=\"0\" checked><label for=\"$value 0 %\"> 0%</label>
                    <INPUT class=\"red lighten-2\" type=\"radio\" name=\"registre_proportion[$value]\" id = \"$value 25 %\" value=\"1\"><label for=\"$value 25 %\"> 25%</label>
                    <INPUT class=\"red lighten-2\" type=\"radio\" name=\"registre_proportion[$value]\" id = \"$value 50 %\" value=\"2\"><label for=\"$value 50 %\"> 50%</label>
                    <INPUT class=\"red lighten-2\" type=\"radio\" name=\"registre_proportion[$value]\" id = \"$value 75 %\" value=\"3\"><label for=\"$value 75 %\"> 75%</label>
                    <INPUT class=\"red lighten-2\" type=\"radio\" name=\"registre_proportion[$value]\" id = \"$value 100 %\" value=\"4\"><label for=\"$value 100 %\"> 100%</label><br>";
                }
				echo "
						<div class=\"clearBoth\"></div>
				";
                # Préparer la liste des annotateurs si admin -> permet d'avoir un menu déroulant
                if ($statut == 'admin'){
                    $users_id=array();
                    $users = $bdd->query('SELECT * FROM utilisateurs WHERE statut LIKE "annotateur"') or die(print_r($bdd->errorInfo()));;
                    while ($data = $users->fetch()){$users_id[$data['id']] = strtoupper($data['nom'])." ".ucfirst($data['prenom'])." (".$data['id'].")";}
                    // print_r($users_id);
                    $users -> closeCursor();
                    echo "<p><b>Avec l'annotation de :<br>";  
                    foreach ($users_id as $key => $value){
                        echo "
						<INPUT class=\"red lighten-2\" type=\"radio\" name=\"user_id\" id =\"$value\" value=\"$key\"><label for=\"$value\">".$value."</label><br>
						";
                    }
                    echo "</b></p>";
                }
                
                echo "<INPUT class=\"waves-effect waves-light red lighten-2 btn\" type=\"submit\" name=\"research_proportion\" value=\"Rechercher\"></form>";
                ###??????????????????????????????????? pourquoi post ne marche pas à la place de get
                if (isset($_GET['research_proportion'])){
                    if ($_GET['user_id']){
                        $id_utilisateur = $_GET['user_id'];
                    }
                    $reg = $_GET['registre_proportion'];
                    // print_r($reg);
                    foreach ($reg as $value){
                        $i += $value ;
                    }
                    if ($i < 4){
                        echo "<p>Attention, la somme des proportions est inférieure à 100%.</p>";
                    }
                    elseif ($i > 4){
                        echo "<p>Attention, la somme des proportions est supérieure à 100%.</p>";
                    }
                    else {
                        $id_extrait=array();
                        $annotation = $bdd->query('SELECT * FROM annotation WHERE pro_s LIKE '.$reg['soutenu']." AND pro_c LIKE ".$reg['courant']." AND pro_f LIKE ".$reg['familier']." AND pro_p LIKE ".$reg['poubelle']." AND id_utilisateur LIKE ".$id_utilisateur);
                        while ($data = $annotation->fetch()){
                            array_push($id_extrait, $data['id_extrait']);
                        }
                        $annotation -> closeCursor();

                        $user_infos = $bdd -> query('SELECT * FROM utilisateurs WHERE id LIKE '.$id_utilisateur);
                        while ($data = $user_infos->fetch()){echo "<table><tr><td><b>".$data['id']."</b></td><td><b>".strtoupper($data['nom'])." ".ucfirst($data['prenom'])."</b></td></tr>";}
                        $user_infos -> closeCursor();

                        for ($i=0;$i<count($id_extrait);$i++){
                            $extrait = $bdd->query('SELECT * FROM corpus WHERE id_extrait LIKE '.$id_extrait[$i]);
                            while ($data = $extrait->fetch()){
                                echo "<tr><td>".$data['id_extrait']."</td><td>".$data['extrait']."</td></tr>";
                            }
                            $extrait ->closeCursor();
                        }
                        echo "</table>";
                    }
                }
                
                
                

            }


            function taille_txt($bdd){
                $menu_deroulant = array("Chercher les textes dont le nombre de caractères est", "inférieur à", "supérieur à", "entre");
                echo "<form method=\"get\"><p><b>";
                for ($i=1;$i<count($menu_deroulant);$i++){
                    if ($i!=count($menu_deroulant)-1){
                        echo "
						<INPUT class=\"red lighten-2\" type=\"radio\" name=\"choix\" id =\"$menu_deroulant[$i]\" value=\"$menu_deroulant[$i]\"><label for=\"$menu_deroulant[$i]\">".$menu_deroulant[$i]."</label><br>";
                    }
                    else {
                        echo "<INPUT class=\"red lighten-2\" type=\"radio\" name=\"choix\" id =\"$menu_deroulant[$i]\" value=\"$menu_deroulant[$i]\" checked><label for=\"$menu_deroulant[$i]\">".$menu_deroulant[$i]."</label><br>";
                    }
                }
                echo "</b></p><INPUT type=\"text\" name=\"value_length\" value=\"Ex) 10,50\"><br><INPUT class=\"waves-effect waves-light red lighten-2 btn\" type=\"submit\" name=\"research_nb\" value=\"Rechercher\"></form>";
                if (isset($_GET['research_nb'])){
					$id_extrait =array();
                    $titre = array();
                    $length = $_GET['value_length'];
                    $choix = array("inférieur à", "supérieur à", "entre");
                    $choix_nb = $_GET['choix'];
                    $annotation = $bdd->query('SELECT * FROM corpus');
                    if ($choix_nb == "inférieur à") { # Si inf
                        while ($data = $annotation->fetch()){
                            if (strlen($data['extrait']) < $length) {
                                array_push($id_extrait, $data['id_extrait']);
                            }
                        }
                        array_push($titre, $choix_nb);
                        array_push($titre, $length);
                        // print_r($id_extrait);
                    }
                    elseif ($choix_nb == "supérieur à"){ # Si sup
                        while ($data = $annotation->fetch()){
                            if (strlen($data['extrait']) > $length) {
                                array_push($id_extrait, $data['id_extrait']);
                            }
                        }
                        array_push($titre, $choix_nb);
                        array_push($titre, $length);
                        // print_r($id_extrait);
                    }
                    elseif ($choix_nb == $choix[2]){ #Si entre
                        $pos = strpos($length, ",");
                        $nb_avant = substr($length, 0, $pos);
                        $nb_apres = substr($length, $pos+1, strlen($length));
                        // echo $nb_avant;
                        // echo $nb_apres;
                        while ($data = $annotation->fetch()){
                            if ($nb_avant < strlen($data['extrait']) and strlen($data['extrait']) < $nb_apres) {
                                array_push($id_extrait, $data['id_extrait']);
                            }
                        }
                        array_push($titre, $choix_nb);
                        array_push($titre, $length);
                        // print_r($id_extrait);
                    }
                    $annotation->closeCursor();
                    
                    echo "<table><tr><td><b>".$titre[0]."</b></td><td><b>".$titre[1]."</b></td></tr>";
                    for ($i=0;$i<count($id_extrait);$i++){
                        $extrait = $bdd->query('SELECT * FROM corpus WHERE id_extrait LIKE '.$id_extrait[$i]);
                        while ($data = $extrait->fetch()){
                            echo "<tr><td>".$data['id_extrait']."</td><td>".$data['extrait']."</td></tr>";
                        }
                        $extrait->closeCursor();
                    }
                    echo "</table>";

                    // print_r($id_extrait);
                }
                
                

            }


            function descripteurs($id_utilisateur, $statut, $bdd){
                $descripteurs=$bdd->query('SELECT id_descripteur, descripteur FROM descripteurs');
                echo "<form method=\"post\"><table>";
                while ($descrips=$descripteurs->fetch()) {
                    echo "<tr><td><INPUT type=\"checkbox\" name=\"descripteurs[]\" value=".$descrips['id_descripteur'].">".$descrips['descripteur']."</td></tr><tr>";
                }
                $descripteurs->closeCursor();

                if ($statut == 'admin'){
                    $users_id=array();
                    $users = $bdd->query('SELECT * FROM utilisateurs WHERE statut LIKE "annotateur"') or die(print_r($bdd->errorInfo()));;
                    while ($data = $users->fetch()){$users_id[$data['id']] = strtoupper($data['nom'])." ".ucfirst($data['prenom'])." (".$data['id'].")";}
                    // print_r($users_id);
                    $users -> closeCursor();
                    echo "<td><SELECT name=\"user_id\">";  
                    foreach ($users_id as $key => $value){
                        echo "<OPTION value=".$key.">".$value."</OPTION>";
                    }
                    echo "</SELECT></td>";
                }
                echo "<td><INPUT type=\"submit\" name=\"research_desc\" value=\"Rechercher\"></td></tr></table></form>";
                if (isset($_POST['research_desc'])){
                    if ($_POST['user_id']){
                        $id_utilisateur = $_POST['user_id'];
                    }
                    $id_extrait =array();
                    $desc = $_POST['descripteurs'];
                    $class_desc= array("des_c", "des_f", "des_s", "des_p");
                    $annotation = $bdd->query('SELECT id_extrait, des_s, des_c, des_f, des_p FROM annotation WHERE id_utilisateur LIKE '.$id_utilisateur);
                    while ($data = $annotation->fetch()){
                        foreach ($desc as $value){
                            foreach ($class_desc as $class_value){
                                if (strpos($data[$class_value], $value)!=false or $data[$class_value]==$value or strval(strpos($data[$class_value], $value)) =="0"){
                                    array_push($id_extrait, $data['id_extrait']);
                                }
                            }
                        }
                    }
                    $annotation->closeCursor();

                    $user_infos = $bdd -> query('SELECT * FROM utilisateurs WHERE id LIKE '.$id_utilisateur);
                        while ($data = $user_infos->fetch()){echo "<table><tr><td><b>".$data['id']."</b></td><td><b>".strtoupper($data['nom'])." ".ucfirst($data['prenom'])."</b></td></tr>";}
                        $user_infos -> closeCursor();

                    for ($i=0;$i<count($id_extrait);$i++){
                        $extrait = $bdd->query('SELECT * FROM corpus WHERE id_extrait LIKE '.$id_extrait[$i]);
                        while ($data = $extrait->fetch()){
                            echo "<tr><td>".$data['id_extrait']."</td><td>".$data['extrait']."</td></tr>";
                        }
                        $extrait->closeCursor();
                    }
                    echo "</table>";

                    // print_r($id_extrait);
                }
            }

            function menu_auto($rubriques_possibles){
                $rubs = explode(", ", $rubriques_possibles);
                echo "<table><tr>";
                foreach($rubs as $value){
                    echo "<td><a href=\"page_".$value.".php\">".ucfirst($value)."</a></td>";
                }
                echo "</tr></table>";
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