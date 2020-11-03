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
                    Visualisation - l'accord inter annotateur
                </h1>
            </div>
            <div id="page-inner">
                <?php
                    # Vérifier si tous les extraits sont bien annotés
                    $interrogevalidite=$bdd->prepare('SELECT min(validite) FROM annotation 
                                    WHERE id_utilisateur=:id_utilisateur');
                    $interrogevalidite->execute(array('id_utilisateur'=>$id_utilisateur));
                    $validite=$interrogevalidite->fetch();
                    $interrogevalidite->closeCursor();
                    if ($validite[0]==0) {
                        echo "
                            <div class=\"alert alert-warning\">
                                Veuillez refaire les annotations invalides avant de passer à la visualisation.
                            </div>
                        ";
                    }
                    else {
                        $infoadmin=infoAdmin($bdd);
                        $id_admin=$infoadmin['id'];
                        $nom_admin = $infoadmin['nom'];
                        $prenom_admin = $infoadmin['prenom'];
                        if ($id_admin==$id_utilisateur) {
                            echo "
                                <div class=\"col-md-12\">
                                    <div class=\"card\">
                                        <div class=\"card-content\">
                                            <div class=\"table-responsive\">
                                                <table class=\"table\">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Nom</th>
                                                            <th>Rappel</th>
                                                            <th>Précision</th>
                                                            <th>F-mesure</th>
                                                            <th>Kappa</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                            ";
                            $interrogeannotateurs=$bdd->query('SELECT * FROM utilisateurs 
                                    WHERE statut=\'annotateur\'');
                            while ($infoannotateur=$interrogeannotateurs->fetch()) {
                                $id_annotateur=$infoannotateur['id'];
                                $nom_annotateur=$infoannotateur['nom'];
                                $prenom_annotateur=$infoannotateur['prenom'];
                                $f = calculFmesure($bdd, $id_admin, $id_annotateur);
                                $rappel = round($f[0], 2);
                                $precision = round($f[1], 2);
                                $fmesure = round(2*$rappel*$precision/($rappel+$precision), 2);
                                $kappa = round(calculKappa($bdd, $id_admin, $id_annotateur), 2);
                                echo "
                                                        <tr>
                                                            <td>$id_annotateur</td>
                                                            <td>".strtoupper($nom_annotateur)." ".ucfirst($prenom_annotateur)."</td>
                                                            <td>$rappel</td>
                                                            <td>$precision</td>
                                                            <td>$fmesure</td>
                                                            <td>$kappa</td>
                                                        </tr>
                                ";
                            }
                            $interrogeannotateurs->closeCursor();
                            echo "
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ";
                        }
                        else {
                            $f = calculFmesure($bdd, $id_admin, $id_utilisateur);
                            $rappel = round($f[0], 2);
                            $precision = round($f[1], 2);
                            $fmesure = round(2*$rappel*$precision/($rappel+$precision), 2);
                            $kappa = round(calculKappa($bdd, $id_admin, $id_utilisateur), 2);
                            echo "
                            <div classe=\"row\">
                                <div class=\"col-sm-6 col-md-3\">
                                    <div class=\"card-panel text-center\">
                                        <h3>Rappel</h3><br>
                                        <div class=\"easypiechart\" id=\"easypiechart-blue\" data-percent=" . strval($rappel * 100) . " >
                                        <span class=\"percent\">$rappel</span>
                                        </div>                            
                                    </div>
                                </div>
                                <div class=\"col-sm-6 col-md-3\">
                                    <div class=\"card-panel text-center\">
                                        <h3>Précision</h3><br>
                                        <div class=\"easypiechart\" id=\"easypiechart-red\" data-percent=" . strval($precision * 100) . " >
                                        <span class=\"percent\">$precision</span>
                                        </div>                            
                                    </div>
                                </div>
                                <div class=\"col-sm-6 col-md-3\">
                                    <div class=\"card-panel text-center\">
                                        <h3>F-mesure</h3><br>
                                        <div class=\"easypiechart\" id=\"easypiechart-teal\" data-percent=" . strval($fmesure * 100) . " >
                                        <span class=\"percent\">$fmesure</span>
                                        </div>                            
                                    </div>
                                </div>
                                <div class=\"col-sm-6 col-md-3\">
                                    <div class=\"card-panel text-center\">
                                        <h3>Kappa</h3><br>
                                        <div class=\"easypiechart\" id=\"easypiechart-orange\" data-percent=" . strval($kappa * 100) . " >
                                        <span class=\"percent\">$kappa</span>
                                        </div>                            
                                    </div>
                                </div>
                            </div>
                        <p>*Référence : " . strtoupper($nom_admin) . " " . ucfirst($prenom_admin) . "</p>
                        ";
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
    function getAnno($bdd,$id) {
        $interroge=$bdd->prepare('SELECT id_extrait,pro_s,pro_c,pro_f,pro_p FROM annotation 
                        WHERE id_utilisateur=:id');
        $interroge->execute(array('id'=>$id));
        $anno=$interroge->setFetchMode(PDO::FETCH_ASSOC);
        $anno=$interroge->fetchAll();
        $interroge->closeCursor();
        return $anno;
    }
    function getAccord($bdd,$idref,$ideva) {
        $anno1=getAnno($bdd,$idref);
        $anno2=getAnno($bdd,$ideva);
        $regs=array('pro_s','pro_c','pro_f','pro_p');
        $accord=array('pro_s'=>0,'pro_c'=>0,'pro_f'=>0,'pro_p'=>0);
        $i=1;
        while ($i<=count($anno1)) {
            foreach ($regs as $reg) {
                $accord[$reg]+=min($anno1[$i-1][$reg],$anno2[$i-1][$reg]);
            }
            $i++;
        }
        return $accord;
    }
    function getSum($bdd,$idref,$ideva) {
        $interrogeSum=$bdd->prepare('SELECT SUM(pro_s) AS s,SUM(pro_c) AS c,SUM(pro_f) AS f,SUM(pro_p) AS p FROM annotation 
                                WHERE id_utilisateur IN (:id1,:id2) GROUP BY id_utilisateur');
        $interrogeSum->execute(array('id1'=>$idref,'id2'=>$ideva));
        $sommepro=$interrogeSum->setFetchMode(PDO::FETCH_ASSOC);
        $sommepro=$interrogeSum->fetchAll();
        $interrogeSum->closeCursor();
        return $sommepro;
    }
    function calculFmesure($bdd,$idref,$ideva) {
        $annoref=getAnno($bdd,$idref);
        $somme=getSum($bdd,$idref,$ideva);
        $total=4*count($annoref);
        $accord=getAccord($bdd,$idref,$ideva);
        $regs=array('s','c','f','p');
        $vp=array();
        $r=array();
        $p=array();
        $fm=array();
        foreach ($regs as $reg) {
            $vp[$reg]=$accord['pro_'.$reg];
            if ($somme[0][$reg]==0) {
                $r[$reg]=0.5;
            }
            else {
                $r[$reg]=$vp[$reg]/$somme[0][$reg];
            }
            if ($somme[1][$reg]==0) {
                $p[$reg]=0.5;
            }
            else {
                $p[$reg]=$vp[$reg]/$somme[1][$reg];
            }
        }
        $rappel=array_sum($r)/4;
        $precision=array_sum($p)/4;
        return array($rappel,$precision);
    }
    function calculKappa($bdd,$idref,$ideva) {
        $anno1=getAnno($bdd,$idref);
        $total=4*count($anno1);
        # Calcul de Probabilité d'accord (Pa)
        $accord=getAccord($bdd,$idref,$ideva);
        $pa=array_sum($accord)/$total;
        # Calcul de Probabilité d'accord simultané (Pe)
        $pe=0;
        $somme=getSum($bdd,$idref,$ideva);
        foreach (array('s','c','f','p') as $reg) {
            $pe+=($somme[0][$reg]/$total)*($somme[1][$reg]/$total);
        }
        # Calcul du kappa
        $k=($pa-$pe)/(1-$pe);
        return $k;
    }

?>

