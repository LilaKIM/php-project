<html>
    <head>
        <title>ANNOTATION</title>
        <link rel="shortcut icon" href="images/annotation.png"/>
        <style>
            h1 {
                text-align: center;
            }
            #hint {
                font-size: small;
                color: red;
            }
        </style>
    </head>
    <body>
        <h1>-- Bienvenue sur le site d'annotation --</h1>
        <?php
            echo "
            <center>
                <form method=\"post\" action=\"page_accueil.php\">
                Nom : <input type=\"text\" name=\"nom\"><br>
                <p id=\"hint\">* en minuscule, sans accent *</p>
                Prénom : <input type=\"text\" name=\"prenom\"><br>
                <p id=\"hint\">* en minuscule, sans accent *</p>
                Identifiant : <input type=\"text\" name=\"id\"><br>
                <p id=\"hint\">* numéro étudiant *</p>
                Mot de passe : <input type=\"text\" name=\"mdp\"><br>
                <p id=\"hint\">* Première connexion d'un invité ? Entrez le mot de passe souhaité*</p><br>
                <input type=\"submit\" value=\"Se connecter\"><br>
                </form>
            </center>
            ";
        ?>
    </body>
</html>

