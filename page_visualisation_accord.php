<?php
session_start();
?>

<html>
    <head>
        <title>VISUALISATION ACCORD INTER-ANNOTATEURS</title>
        <link rel="shortcut icon" href="images/annotation.png"/>
        <style>
            h1 {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <?php
            # Menu automatique (sep=', ')
            menu_auto("annotation, visualisation_annotation");
        ?>
        <?php
            # Menu automatique (sep=', ')
            function menu_auto($rubriques_possibles){
                $rubs = explode(", ", $rubriques_possibles);
                echo "<table><tr>";
                foreach($rubs as $value){
                    echo "<td><a href=\"page_".$value.".php\">".ucfirst($value)."</a></td>";
                }
                echo "<tr/><table/>";
            }
        ?>
    </body>
</html>
