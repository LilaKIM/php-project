<?php
session_start();
?>

<html>
    <head>
        <title>VISUALISATION RÉSULTAT D'ANNOTATION</title>
        <link rel="shortcut icon" href="images/annotation.png"/>
        <style>
            h1 {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <?php
            $menu_automatique = array("accueil", "visualisation_accord");
            foreach($menu_automatique as $value){
                echo "<a href=\"page_".$value.".php\">".$value."</a><br/>";
            }
        ?>
    </body>
</html>
