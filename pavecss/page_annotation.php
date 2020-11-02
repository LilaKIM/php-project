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
                        $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
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
            <form method="post" action="page_annotation.php" id="form">
            <?php
                $interrogeextrait=$bdd->prepare('SELECT * FROM corpus
                                WHERE corpus.id_extrait NOT IN (SELECT id_extrait FROM annotation
                                WHERE annotation.id_utilisateur=:id_utilisateur)');
                $interrogeextrait->execute(array('id_utilisateur'=>$id_utilisateur));
                $extrait=$interrogeextrait->fetch();
                if (! $extrait) {
                    $texte="Vous avez parcouru tous les extraits.<br>Refaites ce qui sont invalides, puis procédez à la visualisation.";
                }
                else {
                    $texte=$extrait['extrait'];
                    $id_extrait=$extrait['id_extrait'];
                }
            ?>
                <div class="col">
                    <div class="col-md-4 col-sm-4">
                        <div class="card red lighten-2">
                            <div class="card-content white-text">
                                <?php
                                echo "
                                    <input type=\"hidden\" name=\"id_extrait\" value=$id_extrait form=\"form\">
                                    <input type=\"hidden\" name=\"id_utilisateur\" value=$id_utilisateur form=\"form\">
                                ";
                                echo "
                                    <span class=\"card-title\">Tweet n°$id_extrait</span>
                                    <p>$texte</p>
                                ";
                                $regs=array("soutenu","courant","familier","poubelle");
                                $anno_pro=array();
                                $anno_des=array();
                                ?>
                            </div>
                        </div>
                        <!--/Tweet : id & texte-->
                        <div class="card">
                            <div class="card-content">
                                <?php
                                # Proportion
                                foreach ($regs as $reg) {
                                    echo "<p><b>".strtoupper($reg)."</b></p>
                                        <input class=\"red lighten-2\" type=\"radio\" name=\"anno_pro[$reg]\" value=0 id=\"$reg 0 %\" form=\"form\" checked><label for=\"$reg 0 %\"> 0 %</label>
                                        <input class=\"red lighten-2\" type=\"radio\" name=\"anno_pro[$reg]\" value=1 id=\"$reg 25 %\" form=\"form\"><label for=\"$reg 25 %\"> 25 %</label>
                                        <input class=\"red lighten-2\" type=\"radio\" name=\"anno_pro[$reg]\" value=2 id=\"$reg 50 %\" form=\"form\"><label for=\"$reg 50 %\"> 50 %</label>
                                        <input class=\"red lighten-2\" type=\"radio\" name=\"anno_pro[$reg]\" value=3 id=\"$reg 75 %\" form=\"form\"><label for=\"$reg 75 %\"> 75 %</label>
                                        <input class=\"red lighten-2\" type=\"radio\" name=\"anno_pro[$reg]\" value=4 id=\"$reg 100 %\" form=\"form\"><label for=\"$reg 100 %\"> 100 %</label><br>
                                    ";
                                }
                                echo "
                                        <div class=\"clearBoth\"></div>
                                ";
                                ?>
                            </div>
                        </div>
                        <!--/Annotation : proportion-->
                        <div class="card">
                            <div class="card-content">
                                <p><b>Signaler un problèmes :</b></p><br>
                                <input type="checkbox" class="filled-in" id="errid1" name="problemes[]" value="erreur de formatage" form="form"/>
                                <label for="errid1">Erreur de formatage</label>
                                <input type="checkbox" class="filled-in" id="errid2" name="problemes[]" value="mauvaise qualité de texte" form="form"/>
                                <label for="errid2">Mauvaise qualité de texte</label>
                                <div class="input-field col s12">
                                <input id="autre" name="autreprobleme" type="text" class="validate" form="form">
                                <label for="autre">Autre</label>
                                <div class=\"clearBoth\"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="col-md-6 col-sm-6">
                        <div class="card">
                            <div class="card-content">
                                <?php
                                # Tabs
                                echo "
                                        <div class=\"col\">
                                        <ul class=\"tabs\">
                                    ";
                                foreach ($regs as $reg) {
                                    echo "
                                            <li class=\"tab col s3\"><a href=\"#".$reg."\">".ucfirst($reg)."</a></li>
                                    ";
                                }
                                echo "
                                        </ul>
                                        </div>
                                        <div class=\"clearBoth\"><br /></div>
                                ";
                                # Descripteurs
                                $interrogedescrip=$bdd->query('SELECT id_descripteur, descripteur FROM descripteurs');
                                $descrips=$interrogedescrip->fetchAll();
                                $interrogedescrip->closeCursor();
                                foreach ($regs as $reg) {
                                    echo "<div id=\"$reg\" class=\"col s12\">";
                                    foreach ($descrips as $descrip) {
                                        $id_des = $descrip['id_descripteur'];
                                        $des = $descrip['descripteur'];
                                        echo "
                                            <input type=\"checkbox\" name=\"anno_des[$reg][]\" value=$id_des class=\"filled-in\" id=\"$reg $id_des\" form=\"form\">
                                            <label for=\"$reg $id_des\">$id_des $des</label></p>
                                        ";
                                    }
                                    echo "</div>";
                                }
                                echo "
                                        <div class=\"clearBoth\"></div>
                                ";
                                ?>
                            </div>
                        </div>
                    </div>
                    <!--/Annotation : descripteurs-->
                </div>
                <div class="col">
                    <div class="col-md-2 col-sm-2">
                        <div class="card">
                            <div class="card-content">
                                <input type="submit" class="waves-effect waves-light red lighten-2 btn" name="enregistrement" value="Enregistrer" form="form">
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-content">
                                <?php
                                    $interrogeanno=$bdd->prepare('SELECT * FROM annotation 
                                            WHERE id_utilisateur=:id_utilisateur');
                                    $interrogeanno->execute(array('id_utilisateur'=>$id_utilisateur));
                                    $annos=$interrogeanno->fetchAll();
                                    echo "
                                        <form method=\"post\" action=\"page_annotation.php\" id=\"plan\">
                                        <input type=\"hidden\" name=\"id_utilisateur\" value=$id_utilisateur form=\"plan\">
                                        Annotation(s) invalide(s) :<br><br>
                                    ";
                                    foreach ($annos as $anno) {
                                        $id=$anno['id_extrait'];
                                        if ($anno['validite']==0) {
                                            echo "
                                                <input class=\"red lighten-2\" type=\"radio\" name=\"idskip\" value=$id id=$id form=\"plan\"><label for=$id> $id</label>
                                            ";
                                        }
                                    }
                                    echo "
                                        </p>
                                        <input type=\"submit\" class=\"waves-effect waves-light red lighten-2 btn\" name=\"refaire\" value=\"Refaire\" form=\"plan\">
                                        </form>
                                    ";
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/Plan -->
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
    if (isset($_POST['refaire'])) {
        $id_extrait=$_POST['idskip'];
        $id_utilisateur=$_POST['id_utilisateur'];
        $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
        $deleteanno=$bdd->prepare('DELETE FROM annotation 
                        WHERE id_extrait=:id_extrait AND id_utilisateur=:id_utilisateur');
        $deleteanno->execute(array('id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
        $deleteanno->closeCursor();
        echo "<script>location.href='page_annotation.php';</script>";
    }
    if (isset($_POST['enregistrement'])) {
        $id_extrait=$_POST['id_extrait'];
        $id_utilisateur=$_POST['id_utilisateur'];
        $anno_pro=$_POST['anno_pro'];
        $anno_des=$_POST['anno_des'];
        $problemes=$_POST['problemes'];
        # Vérification de validité de l'annotation
        function examine_anno($pro,$des) {
            if (array_sum($pro) != 4) {return 0;}
            $regs=array("soutenu","courant","familier","poubelle");
            foreach ($regs as $reg) {
                if ($pro[$reg] == 0 and $des[$reg]) {return 0;}
                if ($pro[$reg] > 0 and !$des[$reg]) {return 0;}
            }
            return 1;
        }
        $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
        # Ajout de l'annotation
        # validite=0 signifie que l'utilisateur a au moins rencontré cet extrait
        $insertanno=$bdd->prepare('INSERT INTO annotation (id_extrait,id_utilisateur,validite)
                        VALUES (:id_extrait,:id_utilisateur,:validite)');
        $insertanno->execute(array('id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur,
                        'validite'=>examine_anno($anno_pro,$anno_des)));
        $insertanno->closeCursor();
        # Insertion de proportions
        $insertpro=$bdd->prepare('UPDATE annotation SET pro_s=:s,pro_c=:c,pro_f=:f,pro_p=:p
                        WHERE id_extrait=:id_extrait AND id_utilisateur=:id_utilisateur');
        $insertpro->execute(array('s'=>$anno_pro['soutenu'],'c'=>$anno_pro['courant'],
            'f'=>$anno_pro['familier'],'p'=>$anno_pro['poubelle'],
            'id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
        $insertpro->closeCursor();
        # Insertion de descripteurs par registre
        $regs=array("soutenu","courant","familier","poubelle");
        $reg_descrips=array();
        foreach ($regs as $reg) {
            $reg_descrips[$reg]=implode(";",$anno_des[$reg]);
        }
        $insertdes=$bdd->prepare('UPDATE annotation SET des_s=:s,des_c=:c,des_f=:f,des_p=:p
                        WHERE id_extrait=:id_extrait AND id_utilisateur=:id_utilisateur');
        $insertdes->execute(array('s'=>$reg_descrips['soutenu'],'c'=>$reg_descrips['courant'],
            'f'=>$reg_descrips['familier'],'p'=>$reg_descrips['poubelle'],
            'id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
        $insertdes->closeCursor();
        # Insertion de problèmes signalés
        if ($problemes) {
            $pblm=implode(";",$problemes);
            $insertpblm=$bdd->prepare('UPDATE annotation SET problemes=:problemes
                    WHERE id_extrait=:id_extrait AND id_utilisateur=:id_utilisateur');
            $insertpblm->execute(array('problemes'=>$pblm,'id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
            $insertpblm->closeCursor();
        }
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