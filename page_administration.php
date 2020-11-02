<?php
    # menu généré automatiquement (rubrique selon $useracces)
    menu_auto("accueil, annotation, visualisation_accord, visualisation_annotation, administration");

    # téléchargement de fichier pour enrichir le corpus
    $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
    # pour que les extraits contenant des emojis puissent être ajoutés correctement
    $bdd->query('SET NAMES utf8mb4');
    # à modifier, fichier téléchargé au lieu de source donnée
    $source="2020_M2TALIL_projet_corpus_tweeets.txt";
    if($fichier=@fopen($source,"r")) {
        echo "Ouverture du fichier possible";
        $corpus=file_get_contents($source);
        fclose($source);
        $extraits=explode("---------------------\n",$corpus);
        $id_extrait=0;
        foreach ($extraits as $extrait) {
            if ($extrait) {
                $id_extrait++;
                $ajoutextrait=$bdd->prepare('INSERT INTO corpus (id_extrait, extrait) 
                                    VALUES (:id_extrait, :extrait)');
                $ajoutextrait->execute(array('id_extrait'=>$id_extrait,'extrait'=>$extrait));
                $ajoutextrait->closeCursor();
            }
        }
    }
    else {
        echo "Ouverture du fichier impossible";
    }
    issues($bdd);
?>
<?php
    function issues($bdd){
        $issues = array();
        $response = $bdd -> query('SELECT id_extrait, id_utilisateur, problemes FROM annotation WHERE problemes NOT LIKE ""')or die(print_r($bdd->errorInfo()));
        while ($data = $response -> fetch()){
            $issues[$data['id_extrait'].";".$data['id_utilisateur']] = $data['problemes'];
        }
        $response -> closeCursor();
        
        echo "<h2>Problèmes signalés</h2>";
        echo "<table>";
        if (!$issues){ echo "<tr><td>Nous n'avons aucun problème signalé.</td></tr></table>";}
        else {
            echo "<tr><td>N° extrait</td><td>ID utilisateur</td><td>Motif(s)</td><td>Extrait</td></tr>";
            // print_r($issues);
            foreach ($issues as $key => $value){
                $pos = strpos($key, ";");
                $id_extrait = substr($key, 0, $pos);
                $id_user = substr($key, $pos+1, strlen($key));
                echo "<tr><td>".$id_extrait."</td><td>".$id_user."</td><td>".$value."</td>";
                $extrait_issues=$bdd->query('SELECT * FROM corpus WHERE id_extrait LIKE '.$id_extrait);
                while ($corpus=$extrait_issues->fetch()){echo "<td>".$corpus['extrait']."</td></tr>";}
                $extrait_issues -> closeCursor();
            }
        }
    }

    # Menu automatique (sep=', ')
    function menu_auto($rubriques_possibles){
        $rubs = explode(", ", $rubriques_possibles);
        echo "<table><tr>";
        foreach($rubs as $value){
            echo "<td><a href=\"page_".$value.".php\">".ucfirst($value)."</a></td>";
        }
        echo "</tr></table>";
    }
?>
