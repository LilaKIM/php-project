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
                    VISUALISATION - L'ACCORD INTER ANNOTATEUR
                </h1>
            </div>
            <div id="page-inner">
                <?php
                    kappa($id_utilisateur, $bdd);
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
    function kappa($user_id, $bdd){
        $ref = 	21912371;
        $annotationRef = array();
        $annotationNotRef = array();
        $query = 'SELECT id_extrait, id_utilisateur, pro_s, pro_c, pro_f, pro_p FROM annotation WHERE id_utilisateur LIKE '.$user_id." OR id_utilisateur LIKE ".$ref;
        $annotation = $bdd->query($query);
        while ($data = $annotation -> fetch()){
            if ($data['id_utilisateur']==$ref){
                $annotationRef[$data['id_extrait']] = $data['pro_s'].$data['pro_c'].$data['pro_f'].$data['pro_p'];
            }
            else {$annotationNotRef[$data['id_extrait']] = $data['pro_s'].$data['pro_c'].$data['pro_f'].$data['pro_p'];}
        }

        if (count($annotationNotRef)==count($annotationRef)){
            echo "oking";
            probabilite_accord($annotationRef, $annotationNotRef);
        }
        else {echo "<p>Il est possible que vous n'ayez pas encore terminé votre annotation.</p>";}
        $annotation -> closeCursor();
    }
    
    function probabilite_accord($ref, $annot){
        $grand_matrice = array("S"=>array(),"C"=>array(),"F"=>array(),"P"=>array());
        // print_r($grand_matrice);
        $registres = array(0 => "S", 1=>"C",2=>"F",3=>"P");
        $proportion = array(0=>0, 1=>0.25, 2=>0.5, 3=>0.75, 4=>1);
        $total = 0;
        for ($i=1; $i < count($ref)+1;$i++){ # pour chaque extrait
            // echo $ref[$i],"/",$annot[$i],"<br>";
            $temp_array_r=array();
            $temp_array_a=array();
            for ($x=0;$x<count($registres);$x++){ #pour chaque registre
                array_push($temp_array_r, $proportion[$ref[$i][$x]]);
                array_push($temp_array_a, $proportion[$annot[$i][$x]]);
            }

            for ($y=0;$y<count($registres);$y++){
                if ($temp_array_r[$y]==$temp_array[$y]){ # Si soutenu r 0,25 soutenu a 0,25
                    $grand_matrice[$registres[$y]][$registres[$y]] += $temp_array_r[$y]; #si le même résultat dans les deux, on garde
                }
                elseif ($temp_array_r[$y] < $temp_array_a[$y] && $temp_array_r[$y]!=0){ #soutenu r 0,25 soutenu a 0,5
                    $grand_matrice[$registres[$y]][$registres[$y]] += $temp_array_r[$y] ;
                    $temp_annot = $temp_array_a[$y]-$temp_array_r[$y];
                    try{
                        if ($temp_array_r[$y+1] != $temp_array_a[$y+1] && $temp_array_r[$y+1] == $temp_annot){ # courant r 25 soutenu a 25
                            $grand_matrice[$registres[$y]][$registres[$y+1]] += $temp_annot;
                        } 
                        #apparemment il manque les cas comme 
                        #courant r 50 > soutenu a 25 
                        #courant r < soutenu a 
                        elseif ($temp_array_r[$y+2] != $temp_array_a[$y+2] && $temp_array_r[$y+2] == $temp_annot){ # familier r 25 soutenu a 25
                            $grand_matrice[$registres[$y]][$registres[$y+2]] += $temp_annot;
                        }
                        elseif ($temp_array_r[$y+3] != $temp_array_a[$y+3] && $temp_array_r[$y+3] == $temp_annot){ # poubelle r 25 soutenu a 25
                            $grand_matrice[$registres[$y]][$registres[$y+3]] += $temp_annot;
                        }
                    } catch (Exception $e){
                        echo 'Exception reçue : ',  $e->getMessage(), "\n";
                    }
                }
                elseif ($temp_array_r[$y] > $temp_array_a[$y] && $temp_array_r[$y]!=0){ # soutenu r 0,5 soutenu a 0,25
                    $grand_matrice[$registres[$y]][$registres[$y]] += $temp_array_a[$y] ;
                    $temp_annot = $temp_array_r[$y]-$temp_array_a[$y];
                    try{
                        if ($temp_array_r[$y+1] != $temp_array_a[$y+1] && $temp_array_a[$y+1] == $temp_annot){ # soutenu r 25 courant a 25
                            $grand_matrice[$registres[$y+1]][$registres[$y]] += $temp_annot;
                        }
                        elseif ($temp_array_r[$y+2] != $temp_array_a[$y+2] && $temp_array_a[$y+2] == $temp_annot){ # soutenu r 25 familier a 25
                            $grand_matrice[$registres[$y+2]][$registres[$y]] += $temp_annot;
                        }
                        elseif ($temp_array_r[$y+3] != $temp_array_a[$y+3] && $temp_array_a[$y+3] == $temp_annot){ # soutenu r 25 familier a 25
                            $grand_matrice[$registres[$y+3]][$registres[$y]] += $temp_annot;
                        }
                    } catch (Exception $e){
                        echo 'Exception reçue : ',  $e->getMessage(), "\n";
                    }
                }
            }
        }
        echo $total ;
        print_r($grand_matrice);
    }
?>