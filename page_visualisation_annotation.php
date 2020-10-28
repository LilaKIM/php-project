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
            $id_utilisateur=$_SESSION['id'];
            echo "<p id='user'>".strtoupper($_SESSION['nom'])." ".ucfirst($_SESSION['prenom'])." (".$id_utilisateur.")</p>";

            # Menu automatique vers page d'accueil et visualisation_accord
            menu_auto("accueil, visualisation_accord");

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
                proportion($id_utilisateur);
            }

            # si bouton 'taille text' cliqué : ?????
            elseif(isset($_POST['taille_text'])){
                taille_txt();
            }

            # si bouton 'descripteurs' cliqué : on a une table des descripteurs à choix multiples
            elseif(isset($_POST['descripteurs'])){
                descripteurs($id_utilisateur);
            }
        ?>
        <?php
            function proportion($id_utilisateur){
                $registres=array("soutenu","courant","familier","poubelle");
                $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');

                ##Pourquoi ça ne fonctionne pas quand post ?
                echo "<form method=\"get\"><table>";
                foreach ($registres as $value){
                    echo "<tr><td>".ucfirst($value).": </td><td> 
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"0\" checked> 0%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"1\"> 25%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"2\"> 50%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"3\"> 75%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"4\"> 100%</td></tr>";
                }
                echo "</table><br/><INPUT type=\"submit\" name=\"research_proportion\" value=\"Rechercher\"></form>";

                ###???????????????????????????????????
                if (isset($_GET['research_proportion'])){
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
                        // print_r($id_extrait);
                        echo "<table>";
                        for ($i=0;$i<count($id_extrait);$i++){
                            $extrait = $bdd->query('SELECT * FROM corpus WHERE id_extrait LIKE '.$id_extrait[$i]);
                            while ($data = $extrait->fetch()){
                                echo "<tr><td>".$data['id_extrait']."</td><td>".$data['extrait']."</td></tr>";
                            }
                        }
                        echo "</table>";
                    }
                }
            }


            function taille_txt(){
                $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
                $menu_deroulant = array("Chercher les textes dont le nombre de caractères est", "inférieur à", "supérieur à", "entre");
                echo "<form method=\"get\"><table><td><SELECT name=\"choix\">";
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
                    
                    echo "<table><tr><td>".$titre[0]."</td><td>".$titre[1]."</td></tr>";
                    for ($i=0;$i<count($id_extrait);$i++){
                        $extrait = $bdd->query('SELECT * FROM corpus WHERE id_extrait LIKE '.$id_extrait[$i]);
                        while ($data = $extrait->fetch()){
                            echo "<tr><td>".$data['id_extrait']."</td><td>".$data['extrait']."</td></tr>";
                        }
                    }
                    echo "</table>";

                    // print_r($id_extrait);
                }
                
                

            }


            function descripteurs($id_utilisateur){
                $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
                $descripteurs=$bdd->query('SELECT id_descripteur, descripteur FROM descripteurs');
                echo "<form method=\"post\"><table>";
                while ($descrips=$descripteurs->fetch()) {
                    echo "<tr><td><INPUT type=\"checkbox\" name=\"descripteurs[]\" value=".$descrips['id_descripteur'].">".$descrips['descripteur']."</td></tr>";
                }

                echo "</table><br/><INPUT type=\"submit\" name=\"research_desc\" value=\"Rechercher\"></form>";

                if (isset($_POST['research_desc'])){
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
                    echo "<table>";
                    for ($i=0;$i<count($id_extrait);$i++){
                        $extrait = $bdd->query('SELECT * FROM corpus WHERE id_extrait LIKE '.$id_extrait[$i]);
                        while ($data = $extrait->fetch()){
                            echo "<tr><td>".$data['id_extrait']."</td><td>".$data['extrait']."</td></tr>";
                        }
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