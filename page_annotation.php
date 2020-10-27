<?php
session_start();
?>

<html>
    <head>
        <title>ANNOTATION</title>
        <link rel="shortcut icon" href="images/annotation.png"/>
        <style>
            #user {
                text-align: right;
            }
            table,td {
                border-collapse: collapse;
                text-align: center;
            }
            #descripteurs {
                text-align: left;
            }
        </style>
    </head>
    <body>
        <?php
            $id_utilisateur=$_SESSION['id'];
            echo "<p id='user'>".strtoupper($_SESSION['nom'])." ".ucfirst($_SESSION['prenom'])." ($id_utilisateur)</p>";
            $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
            $menu_automatique = array("accueil","visualisation_accord");
            foreach($menu_automatique as $value){
                echo "<a href=\"page_".$value.".php\">".$value."</a><br/>";
            }
        ?>
        <table width="100%" border="2" align="center">
            <tr>
                <td width="30%">
                    <?php
                    $afficheextrait=$bdd->prepare('SELECT * FROM corpus
                        WHERE corpus.id_extrait NOT IN (SELECT id_extrait FROM annotation 
                                WHERE annotation.id_utilisateur=:id_utilisateur)');
                    $afficheextrait->execute(array('id_utilisateur'=>$id_utilisateur));
                    $extrait=$afficheextrait->fetch();
                    if (! $extrait) {
                        echo "Vous avez annoté tous les extraits. Procédez à la visualisation.";
                    }
                    else {
                        echo $extrait['extrait'];
                        $id_extrait=$extrait['id_extrait'];
                    }
                    ?>
                </td>
                <td>
                    <table width="100%" height="100%" border="2">
                        <?php
                        echo "
                        <form method=\"post\" action=\"page_annotation.php\">
                        <input type=\"hidden\" name=\"id_extrait\" value=$id_extrait>
                        <input type=\"hidden\" name=\"id_utilisateur\" value=$id_utilisateur>
                        ";
                        $registres=array("soutenu","courant","familier","poubelle");
                        $proportions=array();
                        $registre_descripteurs=array();
                        foreach ($registres as $registre) {
                            echo "
                            <tr height=\"20%\">
                                <td width=\"30%\">
                                    <b>$registre</b><br>
                                    <input type=\"radio\" name=\"proportions[$registre]\" value=0 checked> 0 %<br>
                                    <input type=\"radio\" name=\"proportions[$registre]\" value=1> 25 %<br>
                                    <input type=\"radio\" name=\"proportions[$registre]\" value=2> 50 %<br>
                                    <input type=\"radio\" name=\"proportions[$registre]\" value=3> 75 %<br>
                                    <input type=\"radio\" name=\"proportions[$registre]\" value=4> 100 %
                                </td>
                                <td id=\"descripteurs\">
                            ";
                                    $descripteurs=$bdd->query('SELECT id_descripteur, descripteur FROM descripteurs');
                                    while ($descrips=$descripteurs->fetch()) {
                                        $id_descrip=$descrips['id_descripteur'];
                                        $descrip=$descrips['descripteur'];
                                        echo "
                                        <input type=\"checkbox\" name=\"registre_descripteurs[$registre][]\" value=$id_descrip> $id_descrip $descrip<br>
                                        ";
                                    }
                            echo "
                                </td>
                            </tr>
                            ";
                        }
                        echo "
                        <tr>
                            <td rowspan=\"2\">
                                <b>Signaler un problème :</b><br>
                                <input type=\"checkbox\" name=\"problemes[]\" value=\"erreur de formatage\"> Erreur de formatage<br>
                                <input type=\"checkbox\" name=\"problemes[]\" value=\"mauvaise qualité de texte\"> Mauvaise qualité de texte<br>
                                <input type=\"checkbox\" name=\"problemes[]\" value=\"autre\"> Autre (précisez)<br>
                                <input type=\"text\" name=\"autreprobleme\">
                            </td>
                            <td colspan=\"2\">
                                <input type=\"submit\" name=\"extraitannote\" value=\"Suivant\">
                            </td>
                        </tr>
                        <tr>
                            <td colspan=\"2\">xxx annotés sur xxx extraits</td>
                        </tr>
                        </form>
                        ";
                        ?>
                    </table>
                </td>
            </tr>
        </table>
        <?php
        if (isset($_POST['extraitannote'])) {
            $id_extrait=$_POST['id_extrait'];
            $id_utilisateur=$_POST['id_utilisateur'];
            $proportions=$_POST['proportions'];
            $registre_descripteurs=$_POST['registre_descripteurs'];
            $problemes=$_POST['problemes'];
            # à faire : faut vérifier la somme de proportion est bien 4 (signifie 100%), sinon, alerte et retourner
            # à faire : faut vérifier proportion>0 <=> descripteur(s) choisi(s)
            $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
            $insertanno=$bdd->prepare('INSERT INTO annotation (id_extrait,id_utilisateur)
                        VALUES (:id_extrait,:id_utilisateur)');
            $insertanno->execute(array('id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
            $insertanno->closeCursor();
            $insertpro=$bdd->prepare('UPDATE annotation SET pro_s=:s,pro_c=:c,pro_f=:f,pro_p=:p
                        WHERE id_extrait=:id_extrait AND id_utilisateur=:id_utilisateur');
            $insertpro->execute(array('s'=>$proportions['soutenu'],'c'=>$proportions['courant'],
                        'f'=>$proportions['familier'],'p'=>$proportions['poubelle'],
                        'id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
            $insertpro->closeCursor();
            # à faire : essayer de le réaliser dans un boucle

            # Insertion de descripteurs par registre
            $registres=array("soutenu","courant","familier","poubelle");
            $reg_descrips=array();
            foreach ($registres as $registre) {
                $reg_descrips[$registre]=implode(";",$registre_descripteurs[$registre]);
            }
            $insertdes=$bdd->prepare('UPDATE annotation SET des_s=:s,des_c=:c,des_f=:f,des_p=:p
                        WHERE id_extrait=:id_extrait AND id_utilisateur=:id_utilisateur');
            $insertdes->execute(array('s'=>$reg_descrips['soutenu'],'c'=>$reg_descrips['courant'],
                        'f'=>$reg_descrips['familier'],'p'=>$reg_descrips['poubelle'],
                        'id_extrait'=>$id_extrait,'id_utilisateur'=>$id_utilisateur));
            $insertdes->closeCursor();
            # à faire : insertion de problèmes signalés
            echo "<script>location.href='page_annotation.php';</script>";
        }
        ?>
    </body>
</html>