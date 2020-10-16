<html>
    <head>
        <title>TP 1 - Question 1.2</title>
    </head>
    <body>
        <p>
            <center>
                <?php
                    $array = array("Lila", "YoungJu", "Chenjing", "Lijing", "Tian", "Qi");
                    echo "<b>1. Triez les prénoms par ordre alphabétique puis affichez 1 prénom par ligne</b><br/><br/>";
                    sort($array);
                    foreach($array as $value)
                    {
                        echo "$value <br/>";
                    }
                    echo "<hr/><b>2. Affichez cette fois les prénoms par ordre d'indice décroissant puis affichez 1 prénom par ligne</b><br/><br/>";
                    for ($i = count($array)-1; $i >= 0 ; $i--)
                    {
                        echo "$array[$i]<br/>";
                    }
                ?>
            </center>
        </p>
    </body>
</html>