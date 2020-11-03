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
                    Administration
                </h1>
            </div>
            <div id="page-inner">
                <?php
                    # Revérifier si l'utilisateur actuel est bien l'admin
                    # Pour éviter qu'un non admin entre directement l'URL et y avoir accès
                    $infoadmin=infoAdmin($bdd);
                    $id_admin=$infoadmin['id'];
                    if ($id_utilisateur!=$id_admin) {
                        echo "
                            <div class=\"alert alert-danger\">
                                Vous devez être l'administrateur pour y avoir accès.
                            </div>
                        ";

                    }
                    else {
                        echo "
                        <div class=\"card\">
                            <form action=\"#\"  method=\"post\" enctype=\"multipart/form-data\">
                                <div class=\"card-content\">
                                    <div class=\"file-field input-field\">
                                        <div class=\"btn\">
                                            <span>Parcourir</span>
                                            <input type=\"file\" name=\"fic\" id=\"fic\">
                                        </div>
                                        <div class=\"file-path-wrapper\">
                                            <input class=\"file-path validate\" type=\"text\" placeholder=\"Choisissez le fichier\">
                                        </div>
                                    </div>
                                    <div class=\"clearBoth\"></div>
                                    Vous voulez télécharger ce fichier pour : 
                                    <input class=\"with-gap\" name=\"motif\" type=\"radio\" value=\"corpus\" id=\"corpus\" />
                                      <label for=\"corpus\">Enrichir le corpus</label>
                                    <input class=\"with-gap\" name=\"motif\" type=\"radio\" value=\"invitation\" id=\"invitation\" />
                                      <label for=\"invitation\">Inviter des annotateurs</label>
                                    <input type=\"submit\" class=\"waves-effect waves-light btn\" name=\"uploadfile\" value=\"commencer\">
                                    <div class=\"clearBoth\"></div>
                                </div>
                            </form>
                        </div>
                        ";
                        issues($bdd);
                    }
                    if ($_FILES["file"]["error"] > 0) {
                        echo "
                            <div class=\"alert alert-warning\">
                                Erreur, veuillez réessayer.
                            </div>
                        ";
                    }
                    else {
                        if ($_POST['motif']=='corpus') {
                            $source=$_FILES['fic']['tmp_name'];
                            $nombreextraits = $bdd->query('SELECT MAX(id_extrait) FROM corpus');
                            $idmax = $nombreextraits->fetch()[0];
                            enrichitCorpus($bdd,$source,$idmax);
                        }
                        elseif ($_POST['motif']=='invitation') {
                            print_r($_POST);
                            $source=$_FILES['fic']['tmp_name'];
                            inviteAnnotateurs($bdd,$source);
                        }
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

<?php
    function infoAdmin($bdd) {
        $interrogeadmin=$bdd->query('SELECT * FROM utilisateurs WHERE statut=\'admin\'');
        $infoadmin=$interrogeadmin->fetch();
        $interrogeadmin->closeCursor();
        return $infoadmin;
    }
    function enrichitCorpus($bdd,$src,$iddebut) {
        if ($fichier=@fopen($src,"r")) {
            #echo "Ouverture du fichier possible";
            $corpus=file_get_contents($src);
            fclose($src);
            $extraits=explode("---------------------\n",$corpus);
            $id_extrait=$iddebut;
            # Configuration de l'encodage pourque les emojis soient connus
            $bdd->query('SET NAMES utf8mb4');
            foreach ($extraits as $extrait) {
                if ($extrait) {
                    $id_extrait++;
                    $ajoutextrait=$bdd->prepare('INSERT INTO corpus (id_extrait, extrait) 
                                    VALUES (:id_extrait, :extrait)');
                    $ajoutextrait->execute(array('id_extrait'=>$id_extrait,'extrait'=>$extrait));
                    $ajoutextrait->closeCursor();
                }
            }
            echo "
                <div class=\"alert alert-success\">
                    L'ajout des extraits est réussi.
                </div>
            ";
        }
        else {
            echo "
                <div class=\"alert alert-warning\">
                    Erreur, veuillez réessayer.
                </div>
            ";
        }
    }
    function inviteAnnotateurs($bdd,$src) {
        if ($fichier=@fopen($src,"r")) {
            #echo "Ouverture du fichier possible";
            $ajoutinvite=$bdd->prepare('INSERT INTO utilisateurs (id,nom,prenom,statut)
                        VALUES (:id_invite,:nom,:prenom,\'invité\')');
            while(!feof($fichier)) {
                $infoinvite=explode("\t",fgets($fichier));
                $id_invite=$infoinvite[0];
                $nom_invite=$infoinvite[1];
                $prenom_invite=$infoinvite[2];
                $ajoutinvite->execute(array('id_invite'=>$id_invite,'nom'=>$nom_invite,'prenom'=>$prenom_invite));
            }
            $ajoutinvite->closeCursor();
            fclose($src);
            echo "
                <div class=\"alert alert-success\">
                    L'ajout des invités est réussi.
                </div>
            ";
        }
        else {
            echo "
                <div class=\"alert alert-warning\">
                    Erreur, veuillez réessayer.
                </div>
            ";
        }
    }

    function issues($bdd)
    {
        $issues = array();
        $response = $bdd->query('SELECT id_extrait, id_utilisateur, problemes FROM annotation WHERE problemes NOT LIKE ""') or die(print_r($bdd->errorInfo()));
        while ($data = $response->fetch()) {
            $issues[$data['id_extrait'] . ";" . $data['id_utilisateur']] = $data['problemes'];
        }
        $response->closeCursor();
        echo "
            <div class=\"col-md-12\">
                <div class=\"card\">
                    <div class=\"card-action\">
                        Problèmes signalés
                    </div>
                    <div class=\"card-content\">
                        <div class=\"table-responsive\">
                            <table class=\"table\">
                                <thead>
                                    <tr>
                                        <th>N° extrait</th>
                                        <th>Utilisateur</th>
                                        <th>Problème(s)</th>
                                        <th>Extrait</th>
                                    </tr>
                                </thead>
                                <tbody>
        ";
        if (!$issues) {
            echo "</tbody></table>";
        } else {
            foreach ($issues as $key => $value) {
                $pos = strpos($key, ";");
                $id_extrait = substr($key, 0, $pos);
                $id_user = substr($key, $pos + 1, strlen($key));
                echo "<tr><td>" . $id_extrait . "</td><td>" . $id_user . "</td><td>" . $value . "</td>";
                $extrait_issues = $bdd->query('SELECT * FROM corpus WHERE id_extrait LIKE ' . $id_extrait);
                while ($corpus = $extrait_issues->fetch()) {
                    echo "<td>" . $corpus['extrait'] . "</td></tr>";
                }
                $extrait_issues->closeCursor();
            }
            echo "</table>";
        }
        echo "</div></div></div></div>";
    }
?>

