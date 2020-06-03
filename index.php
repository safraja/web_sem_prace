<?php
include_once './konfigurace.php';
?>

<!doctype html>
<html>
    <head>
        <meta charset='utf8'>
        <title>VŠE Travel | Hlavní stránka</title>
        <link type='text/css' rel='stylesheet' href='styly.css'>
    </head>
    <body>
        <header class='horni-lista hlavni'>
            <h1>VŠE Travel</h1>
            <?php
            include_once "menu.php";
            ?>
        </header>
        <main>
            <?php
            $zajezdy = Databaze::Dotaz("SELECT zajezd.*, stat.jmeno AS stat FROM zajezd 
                INNER JOIN stat ON (zajezd.lokalita = stat.kod)")->fetchAll();
            foreach ($zajezdy as $zajezd)
            {
                $jmeno = htmlspecialchars($zajezd['jmeno']);
                $stat = htmlspecialchars($zajezd['stat']);
                $popis = htmlspecialchars($zajezd["popis"]);

                echo "<article class='zajezd'>";
                echo "<figure><img src='obrazky/{$zajezd['id_zajezd']}.jpg' alt=''></figure>";
                echo "<section>";
                echo "<header>";
                echo "<h2>{$jmeno}</h2>";
                echo "<span>";
                echo "<a href='objednat.php?id_zajezdu={$zajezd['id_zajezd']}'>Objednat</a>";
                if(@$_SESSION["opravneni"] == "spravce")
                {
                    echo " <a href='pridat_zajezd.php?id_zajezdu={$zajezd['id_zajezd']}'>Upravit</a>";
                }
                echo "</span>";
                echo "</header>";
                echo "<p>{$popis}</p>";
                echo "<h3>Podrobnosti:</h3>";
                echo "<table>";
                echo "<tr><th>Stát:</th><td>{$stat}</td></tr>";

                $datum1 = new DateTime($zajezd['zacatek']);;
                $datum2 = new DateTime($zajezd['konec']);;
                $datum1 = $datum1->format('j. n. Y');
                $datum2 = $datum2->format('j. n. Y');

                echo "<tr><th>Termín:</th><td>{$datum1} - {$datum2}</td></tr>";
                echo "<tr><th>Cena:</th><td>{$zajezd['cena_dospely']} Kč (základní),
                          {$zajezd['cena_senior']} Kč (student/senior), {$zajezd['cena_dite']} Kč (dítě)</td></tr>";
                echo "</table>";
                echo "</section>";
                echo "</article>";
            }

            ?>
        </main>
    </body>
</html>