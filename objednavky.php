<?php
include_once './konfigurace.php';

if(@$_SESSION["id_uzivatel"] == null)
{
    ?>
    <!doctype html>
    <html>
        <head>
            <meta charset='utf8'>
            <title>VŠE Travel | Objednávky</title>
            <link type='text/css' rel='stylesheet' href='styly.css'>
        </head>
        <body>
            <header class='horni-lista hlavni'>
                <h1>VŠE Travel</h1>
                <?php
                include_once "menu.php";
                ?>
            </header>
            <main class='odsazene'>
                <h2>Vyžadobáno přihlášení</h2>
                <p>Pro zobrazení seznamu objednávek se musíte přihlásit.</p>
            </main>
        </body>
    </html>
    <?php
    exit();
}
?>

<!doctype html>
<html>
    <head>
        <meta charset='utf8'>
        <title>VŠE Travel | Objednávky</title>
        <link type='text/css' rel='stylesheet' href='styly.css'>
    </head>
    <body>
        <header class='horni-lista hlavni'>
            <h1>VŠE Travel</h1>
            <?php
            include_once "menu.php";
            ?>
        </header>
        <main class='odsazene'>
            <h2>Objednávky</h2>
            <?php
            $objednavky = Databaze::Dotaz("SELECT * FROM `objednavka` JOIN zajezd USING(id_zajezd) 
                WHERE objednavka.id_zakaznik = ?;", array($_SESSION["id_uzivatel"]))->fetchAll();

            if($objednavky == null)
            {
                echo "<p>Nemáte žádné objednávky.</p>";
            }
            else
            {
                echo "<table class='objednavky'>";
                echo "<tr><th>Jméno zájezdu</th><th>Dospělých</th><th>Seniorů/studentů</th><th>Dětí</th><th>Cena</th><th>Akce</th></tr>";

                foreach ($objednavky as $objednavka)
                {
                    $jmeno = htmlspecialchars($objednavka["jmeno"]);
                    $cena = $objednavka["pocet_dospely"]*$objednavka["cena_dospely"]
                        + $objednavka["pocet_senior"]*$objednavka["cena_senior"]
                        + $objednavka["pocet_dite"]*$objednavka["cena_dite"];

                    echo "<tr>";
                    echo "<td>{$jmeno}</td>";
                    echo "<td>{$objednavka["pocet_dospely"]}</td>";
                    echo "<td>{$objednavka["pocet_senior"]}</td>";
                    echo "<td>{$objednavka["pocet_dite"]}</td>";
                    echo "<td>{$cena} Kč</td>";
                    echo "<td><a href='zrusit_objednavku.php?id_zajezdu={$objednavka["id_zajezd"]}'>Zrušit objednávku</a></td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
            ?>
        </main>
    </body>
</html>