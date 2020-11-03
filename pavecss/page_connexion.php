<?php
session_start();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
    <head>
        <title>Site d'Annotation</title>
        <link rel="shortcut icon" href="images/icone.png"/>
        <link href="css/style.css" rel="stylesheet" type="text/css" media="all"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="Login form web template, Sign up Web Templates, Flat Web Templates, Login signup Responsive web template, Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design" />
        <link href='http://fonts.useso.com/css?family=Roboto:500,900italic,900,400italic,100,700italic,300,700,500italic,100italic,300italic,400' rel='stylesheet' type='text/css'>
        <link href='http://fonts.useso.com/css?family=Droid+Serif:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
    </head>
    <body>
    <div class="login">
        <div class="login-top">
            <h1>Bienvenue !</h1>
            <?php
                echo "
                <form method=\"post\" action=\"page_accueil.php\">
                    <input type=\"text\" name=\"nom\" value=\"Nom\" onfocus=\"this.value = '';\" onblur=\"if (this.value == '') {this.value = 'Nom';}\"> 
                    <input type=\"text\" name=\"prenom\" value=\"Prénom\" onfocus=\"this.value = '';\" onblur=\"if (this.value == '') {this.value = 'Prénom';}\">
                    <input type=\"text\" name=\"id\" value=\"Identifiant\" onfocus=\"this.value = '';\" onblur=\"if (this.value == '') {this.value = 'Identifiant';}\">
                    <input type=\"text\" name=\"mdp\" value=\"Mot de passe\" onfocus=\"this.value = '';\" onblur=\"if (this.value == '') {this.value = 'Mot de passe';}\">
                    <input type=\"submit\" value=\"Se connecter\">
                </form>
                "
            ?>
        </div>
        <div class="login-bottom">
            <h3>(1) Nom et prénom sans accent.</h3>
            <h3>(2) L'identifiant est votre numéro étudiant.</h3>
            <h3>(3) Première connexion ? Entrez le mot de passe souhaité.</h3>
        </div>
    </div>
    </body>
</html>
