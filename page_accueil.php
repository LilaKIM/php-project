<?php
session_start();
$_SESSION['nom']=$_POST['nom'];
$_SESSION['prenom']=$_POST['prenom'];
$_SESSION['id_utilisateur']=$_POST['id'];
$_SESSION['mdp']=$_POST['mdp'];
?>

<html>
    <head>
        <title>ACCUEIL</title>
        <link rel="shortcut icon" href="images/annotation.png"/>
        <style>
            h1 {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <?php
            $nom=$_SESSION['nom'];
            $prenom=$_SESSION['prenom'];
            strtolower($nom);
            strtolower($prenom);
            $id=$_SESSION['id_utilisateur'];
            $mdp=$_SESSION['mdp'];
            $bdd=new PDO('mysql:host=localhost:8889;dbname=php-projet','root','root');
            $identification=$bdd->prepare('SELECT * FROM utilisateurs 
                            WHERE id=:id AND nom=:nom AND prenom=:prenom');
            $identification->execute(array('id'=>$id,'nom'=>$nom,'prenom'=>$prenom));
            $userinfo=$identification->fetch();
            $identification->closeCursor();
            # saisies incorrectes
            # redirigé vers la page de connexion/d'inscription
            if (! $userinfo) {
                echo "Connexion échouée, veuillez vérifier les informations saisies.<br>";
                echo "<a href='page_connexion.php'>Retour à la page de connexion</a>.<br>";
                echo "Ou s'inscrire.<br>";
            }
            # première connexion d'un invité
            # enregistrement de mot de passe, changement de statut
            elseif (! $userinfo['mot_de_passe']) {
                $update=$bdd->prepare('UPDATE utilisateurs SET mot_de_passe=:mdp,statut="annotateur" 
                                WHERE id=:id');
                $update->execute(array('mdp'=>$mdp,'id'=>$id));
                $update->closeCursor();
                echo "<br>Votre mot de passe est bien enregistré.<br>";
                echo "<br>Vous pouvez désormais commencer à travailler.<br>";
            }
            $statut=$userinfo['statut'];
            echo "<h1>Bienvenue ".strtoupper($nom)." ".ucfirst($prenom)." !</h1>";
            $acces=$bdd->prepare('SELECT * FROM acces WHERE statut=:statut');
            $acces->execute(array('statut'=>$statut));
            $useracces=$acces->fetch();
            $acces->closeCursor();
            #print_r($useracces);
            #print_r($useracces['accueil']);
            if (! $useracces['accueil']) {
                echo "<br>Votre demande est en cours, veuillez réessayer ultérieurement.<br>";
            }
            else {
                echo "<h1>Accueil</h1>";
                # menu généré automatiquement (rubrique selon $useracces)
                menu_auto_acces("connexion, accueil, annotation, visualisation_accord, visualisation_annotation, administration", $useracces);

                # présentation du projet
                # description du corpus traité
            }
        ?>
        <?php
            # Menu automatique (sep=', ')
            function menu_auto_acces($rubriques_possibles, $useracces){
                $rubs = explode(", ", $rubriques_possibles);
                echo "<table><tr>";
                foreach($rubs as $value){
                    if ($useracces[$value] == 1){
                        echo "<td><a href=\"page_".$value.".php\">".ucfirst($value)."</a></td>";
                    }
                }
                echo "<tr/><table/>";
            }
        ?>
    </body>
</html>
