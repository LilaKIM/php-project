<?php
session_start();
?>

<html>
    <head>
        <title>VISUALISATION RÉSULTAT D'ANNOTATION</title>
        <link rel="shortcut icon" href="images/annotation.png"/>
        <style>
            #user {
                text-align: right;
            }
            table,td {
                border-collapse: collapse;
                text-align: left;
            }
            #descripteurs {
                text-align: left;
            }
        </style>
    </head>
    <body>
        <?php
            $id_utilisateur=$_SESSION['id_utilisateur'];
            echo "<p id='user'>".strtoupper($_SESSION['nom'])." ".ucfirst($_SESSION['prenom'])." (".$id_utilisateur.")</p>";
            # Menu automatique vers page d'accueil et visualisation_accord
            menu_auto("accueil, visualisation_accord");

            # Test pour voir si l'utilisateur est admin 
            # Si admin, on pourrait choisir l'id des autres pour regarder leurs annotations
            $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
            $statut = $bdd->query('SELECT statut FROM utilisateurs WHERE id LIKE '.$id_utilisateur);
            while($data = $statut -> fetch()){$statut_utilisateur = $data['statut'];}
            if ($statut_utilisateur == 'admin'){echo "admin";}else{echo $statut_utilisateur;}
            $statut->closeCursor();

            #boutons permettant de selectionner un des critères (portion/taille_text/descriptieurs)
            echo "
            <form method=\"post\">
                <INPUT type=\"submit\" name=\"proportion\" value=\"Proportion\" >
                <INPUT type=\"submit\" name=\"taille_text\" value=\"Taille du text\">
                <INPUT type=\"submit\" name=\"descripteurs\" value=\"Descripteurs\" >
            </form>";
            
            # si bouton 'proportion' cliqué : on affiche une table contenant des registres avec de différentes proportions à choisir
            # après bouton 'rechercher', on n'a plus de cette table de choix... à modifier
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
        <?php
            function proportion($id_utilisateur, $statut, $bdd){
                $registres=array("soutenu","courant","familier","poubelle");
                ##Pourquoi ça ne fonctionne pas quand post ?
                echo "<form method=\"get\"><table>";
                foreach ($registres as $value){
                    echo "<tr><td>".ucfirst($value).": </td><td> 
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"0\" checked> 0%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"1\"> 25%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"2\"> 50%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"3\"> 75%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"4\"> 100%</td></tr><tr>";
                }
                # Préparer la liste des annotateurs si admin -> permet d'avoir un menu déroulant
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
                
                echo "<td><INPUT type=\"submit\" name=\"research_proportion\" value=\"Rechercher\"></td></tr></table></form>";
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
                        echo "Attention, la somme des proportions est inférieure à 100%.";
                    }
                    elseif ($i > 4){
                        echo "Attention, la somme des proportions est supérieure à 100%.";
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
                
                $annotation->closeCursor();
                $extrait->closeCursor();

            }


            function taille_txt($bdd){
                $menu_deroulant = array("Chercher les textes dont le nombre de caractères est", "inférieur à", "supérieur à", "entre");
                echo "<form method=\"get\"><table><tr><td><SELECT name=\"choix\">";
                for ($i=1;$i<count($menu_deroulant);$i++){
                    if ($i!=count($menu_deroulant)-1){
                        echo "<OPTION value=".$menu_deroulant[$i].">".$menu_deroulant[$i]."</OPTION>";
                    }
                    else {
                        echo "<OPTION value=".$menu_deroulant[$i]." selected>".$menu_deroulant[$i]."</OPTION>";
                    }
                }
                echo "</td><td><INPUT type=\"text\" name=\"value_length\" value=\"Ex) 10,50\"></td></tr></table><br/><INPUT type=\"submit\" name=\"research_nb\" value=\"Rechercher\"></form>";
                
                if (isset($_GET['research_nb'])){
                    $id_extrait =array();
                    $titre = array();
                    $length = $_GET['value_length'];
                    $choix = array("inférieur à", "supérieur à", "entre");
                    $choix_nb = $_GET['choix'];
                    $annotation = $bdd->query('SELECT * FROM corpus');
                    if ($choix_nb == "inférieur") { # Si inf
                        while ($data = $annotation->fetch()){
                            if (strlen($data['extrait']) < $length) {
                                array_push($id_extrait, $data['id_extrait']);
                            }
                        }
                        array_push($titre, $choix_nb);
                        array_push($titre, $length);
                        // print_r($id_extrait);
                    }
                    elseif ($choix_nb == "supérieur"){ # Si sup
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
                    
                    echo "<table><tr><td>".$titre[0]."</td><td>".$titre[1]."</td></tr>";
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
    </body>
</html>