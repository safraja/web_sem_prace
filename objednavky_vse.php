<?php
include_once './konfigurace.php';

if(@$_SESSION["opravneni"] != "spravce")
{
    http_response_code(404);
    include_once "chyba.php";
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
            $objednavky = Databaze::Dotaz("SELECT *, uzivatel.jmeno AS zakaznik_jmeno, 
                zajezd.jmeno AS zajezd_jmeno FROM `objednavka` JOIN zajezd USING(id_zajezd)
                JOIN uzivatel ON (uzivatel.id_uzivatel = objednavka.id_zakaznik);")->fetchAll();

            if($objednavky == null)
            {
                echo "<p>Nejsou žádné objednávky.</p>";
            }
            else
            {
                echo "<table class='objednavky'>";
                echo "<tr><th>Jméno zákazníka</th><th>Přijmení zákazníka</th><th>Jméno zájezdu</th><th>Dospělých</th><th>Seniorů/studentů</th><th>Dětí</th><th>Cena</th></tr>";

                foreach ($objednavky as $objednavka)
                {
                    $zakaznik_jmeno = htmlspecialchars($objednavka["zakaznik_jmeno"]);
                    $zakaznik_prijmeni = htmlspecialchars($objednavka["prijmeni"]);

                    $jmeno = htmlspecialchars($objednavka["zajezd_jmeno"]);
                    $cena = $objednavka["pocet_dospely"]*$objednavka["cena_dospely"]
                        + $objednavka["pocet_senior"]*$objednavka["cena_senior"]
                        + $objednavka["pocet_dite"]*$objednavka["cena_dite"];

                    echo "<tr>";
                    echo "<td>{$zakaznik_jmeno}</td>";
                    echo "<td>{$zakaznik_prijmeni}</td>";
                    echo "<td>{$jmeno}</td>";
                    echo "<td>{$objednavka["pocet_dospely"]}</td>";
                    echo "<td>{$objednavka["pocet_senior"]}</td>";
                    echo "<td>{$objednavka["pocet_dite"]}</td>";
                    echo "<td>{$cena} Kč</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
            ?>
        </main>
    </body>
</html>