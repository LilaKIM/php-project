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
                <INPUT type=\"submit\" name=\"proportion\" value=\"proportion\" >
                <INPUT type=\"submit\" name=\"taille_text\" value=\"taille_text\">
                <INPUT type=\"submit\" name=\"descripteurs\" value=\"descripteurs\" >
            </form>";

            # si bouton 'proportion' cliqué : on affiche une table contenant des registres avec de différentes proportions à choisir
            # après bouton 'rechercher', on n'a plus de cette table de choix... à modifier
            if(isset($_POST['proportion'])){
                proportion();
            }

            # si bouton 'taille text' cliqué : ?????
            if(isset($_POST['taille_text'])){
                taille_txt($id_utilisateur);
            }

            # si bouton 'descripteurs' cliqué : on a une table des descripteurs à choix multiples
            if(isset($_POST['descripteurs'])){
                descripteurs($id_utilisateur);
            }
        ?>
        <?php
            if (isset($_POST['research_proportion'])) {
                $id_extrait=$_POST['id_extrait'];
                $id_utilisateur=$_POST['id_utilisateur'];
                $proportions=$_POST['proportions'];
                $registre_descripteurs=$_POST['registre_descripteurs'];
                $problemes=$_POST['problemes'];}


            function proportion(){
                $registres=array("soutenu","courant","familier","poubelle");
                echo "<form method=\"post\"><table>";
                foreach ($registres as $value){
                    echo "<tr><td>".ucfirst($value).": </td><td> 
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"0%\" checked> 0%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"25%\"> 25%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"50%\"> 50%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"75%\"> 75%
                    <INPUT type=\"radio\" name=\"registre_proportion[$value]\" value=\"100%\"> 100%</td></tr>";
                }
                echo "</table><br/><INPUT type=\"submit\" name=\"research_proportion\" value=\"Rechercher\"></form>";
                if (!empty($_POST['research_proportion'])){
                    echo "ok"; # pourquoiiiiiii ok ne s'affiche pas
                    $reg = $_POST['registre_proportion'];
                    foreach ($reg as $value){
                        echo $value;
                    }
                }
            }


            function taille_txt(){
                $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
                echo "text ok";
            }


            function descripteurs($id_utilisateur){
                $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
                $descripteurs=$bdd->query('SELECT id_descripteur, descripteur FROM descripteurs');
                echo "<form method=\"post\"><table>";
                while ($descrips=$descripteurs->fetch()) {
                    echo "<tr><td><INPUT type=\"checkbox\" name=\"descripteurs[]\" value=".$descrips['id_descripteur'].">".$descrips['descripteur']."</td></tr>";
                }

                ## pourquoi après la table???
                echo "<INPUT type=\"submit\" name=\"research_desc\" value=\"Rechercher\"></form>";

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