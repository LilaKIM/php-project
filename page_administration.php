<?php
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
?>