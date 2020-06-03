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
                <h2>Vyžadováno přihlášení</h2>
                <p>Pro objednání se musíte přihlásit. <a href='prihlaseni.php'>Přihlašte se</a> nebo
                se registrujte následující stránce: Registrace</p>
            </main>
        </body>
    </html>
    <?php
    exit();
}




$chyby = [];

if(@$_POST["odeslani"] != null)
{
    $vychozi_hodnoty = $_POST;


    $objed_zajezd = Databaze::Dotaz("SELECT * FROM zajezd WHERE id_zajezd = ?", array($_POST["id_zajezd"]));
    if($objed_zajezd == null)
    {
        $chyby[] = "Daný zájezd neexistuje.";
    }

    $pocet_dospely = intval($_POST["pocet_dospely"]);
    if(($pocet_dospely < 0) || ($pocet_dospely != $_POST["pocet_dospely"]))
    {
        $chyby[] = "Počet dospělých musí být nezáporné celé číslo.";
    }

    $pocet_senior = intval($_POST["pocet_senior"]);
    if(($pocet_senior < 0) || ($pocet_senior != $_POST["pocet_senior"]))
    {
        $chyby[] = "Počet seniorů/studentů musí být nezáporné celé číslo.";
    }

    $pocet_dite = intval($_POST["pocet_dite"]);
    if(($pocet_dite < 0) || ($pocet_dite != $_POST["pocet_dite"]))
    {
        $chyby[] = "Počet dětí musí být nezáporné celé číslo.";
    }

    if($pocet_dospely + $pocet_senior <= 0)
    {
        $chyby[] = "Musí cestovat alespoň jeden dospělý/senior/student.";
    }
    $celkovy_pocet = $pocet_dospely + $pocet_senior + $pocet_dite;

    $objednano = Databaze::Dotaz("SELECT COUNT(*) FROM objednavka 
        WHERE id_zakaznik = :id_zakaznik AND id_zajezd = :id_zajezd",
    array(":id_zakaznik" => $_SESSION["id_uzivatel"],
            ":id_zajezd" => $_POST["id_zajezd"]))->fetch(PDO::FETCH_COLUMN);

    if($objednano > 0)
    {
        $chyby[] = "Tento zájezd už máte objednaný.";
    }


    $pocet_objednanych = Databaze::Dotaz("SELECT SUM(pocet_celkem) FROM objednavka 
        WHERE id_zajezd = ? GROUP BY id_zajezd",
array($_POST["id_zajezd"]))->fetch(PDO::FETCH_COLUMN);

    $kapacita = Databaze::Dotaz("SELECT kapacita FROM dopravni_prostredek 
        WHERE id_prostredek = ( SELECT vozidlo FROM `zajezd` WHERE `id_zajezd` = ? )",
    array($_POST["id_zajezd"]))->fetch(PDO::FETCH_COLUMN);

    if($kapacita - $pocet_objednanych - $celkovy_pocet < 0)
    {
        $chyby[] = "Je nám líto, tento zájezd má pouze " . ($kapacita - $pocet_objednanych) . " volných míst.";
    }


    if(count($chyby) == 0)
    {
        Databaze::Dotaz("INSERT INTO `objednavka`(`id_zakaznik`, `id_zajezd`, `pocet_dite`, 
            `pocet_dospely`, `pocet_senior`) VALUES (:id_zakaznik, :id_zajezd, :pocet_dite,
            :pocet_dospely, :pocet_senior)",
        array(":id_zakaznik" => $_SESSION["id_uzivatel"], ":id_zajezd" => $_POST["id_zajezd"],
            ":pocet_dite" => $pocet_dite, ":pocet_dospely" => $pocet_dospely, ":pocet_senior" => $pocet_senior));

        http_response_code("303");
        header("Location: objednavky.php");
    }

}

$neexistujici_zajezd = false;
$zajezd = null;

if(@$_GET["id_zajezdu"] != null)
{
    $zajezd = Databaze::Dotaz("SELECT zajezd.*, DATE(zacatek) AS zacatek_datum,  
            TIME_FORMAT(zacatek, '%H:%i') AS zacatek_cas, DATE(konec) AS konec_datum,
            TIME_FORMAT(konec, '%H:%i') AS konec_cas
            FROM zajezd WHERE id_zajezd = ?",
        array($_GET["id_zajezdu"]))->fetch();

    if($zajezd == null)
    {
        $neexistujici_zajezd = true;
        $chyby[] = "Vybraný zájezd neexistuje.";
    }
    else
    {
        $objednano = Databaze::Dotaz("SELECT COUNT(*) FROM objednavka 
        WHERE id_zakaznik = :id_zakaznik AND id_zajezd = :id_zajezd",
            array(":id_zakaznik" => $_SESSION["id_uzivatel"],
                ":id_zajezd" => $_GET["id_zajezdu"]))->fetch(PDO::FETCH_COLUMN);

        if($objednano > 0)
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
                        <h2>Tento zájezd jste si již objednali.</h2>
                        <p>Děkujeme, tento zájezd jste si úspěšně objednali. Seznam svých objednaných zájezdů
                            naleznete <a href='objednavky.php'>zde</a>.</p>
                    </main>
                </body>
            </html>
            <?php
            exit();
        }
    }
}
else
{
    $neexistujici_zajezd = true;
    $chyby[] = "Nezadáno ID zájezdu.";
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
        <main class='formular'>
            <?php
            if($neexistujici_zajezd == true)
            {
                echo "<h2>Zájezd nenalezen</h2>";
                foreach ($chyby as $chyba)
                {
                    echo "<div>$chyba</div>";
                }
                echo "<div><a href='index.php'>Zpět na přehled zájezdů.</a></div>";
                echo "</main></body></html>";
                exit();
            }

            if(!empty($chyby))
            {
                echo "<div class='chyby'>";
                foreach ($chyby as $chyba)
                {
                    echo "<div>{$chyba}</div>";
                }
                echo "</div>";
            }
            ?>
            <h2>
                Objednat zájezd <?php echo htmlspecialchars($zajezd["jmeno"]);?>
            </h2>
            <form method='post'>
                <div>
                    <label for='pocet_dospely'>Počet dospělých:</label>
                    <input type='number' id='pocet_dospely' name='pocet_dospely' min='0'
                           value='<?php echo intval(@$vychozi_hodnoty["pocet_dospely"]); ?>' required>
                </div>

                <div>
                    <label for='pocet_senior'>Počet studentů/seniorů:</label>
                    <input type='number' id='pocet_senior' name='pocet_senior' min='0'
                           value='<?php echo intval(@$vychozi_hodnoty["pocet_senior"]); ?>' required>
                </div>

                <div>
                    <label for='pocet_dite'>Počet dětí:</label>
                    <input type='number' id='pocet_dite' name='pocet_dite' min='0'
                           value='<?php echo intval(@$vychozi_hodnoty["pocet_dite"]); ?>' required>
                </div>

                <div>
                    <strong>Cena: <span id='cena_celkem'>0</span> Kč</strong>
                </div>
                <div>
                    <?php
                    echo "<input type='hidden' name='id_zajezd' value='{$zajezd["id_zajezd"]}'>";
                    echo "<input type='hidden' name='aktualizace' value='{$zajezd["aktualizace"]}'>";
                    echo "<input type='hidden' id='cena_dospely' name='cena_dospely' value='{$zajezd["cena_dospely"]}'>";
                    echo "<input type='hidden' id='cena_dite' name='cena_dite' value='{$zajezd["cena_dite"]}'>";
                    echo "<input type='hidden' id='cena_senior' name='cena_senior' value='{$zajezd["cena_senior"]}'>";
                    ?>
                    <input type='submit' name='odeslani' value='Potvrdit'>
                </div>
            </form>
            <div><a href='index.php'>Zpět na přehled zájezdů.</a></div>
        </main>
        <script>
            function Proved_pocty()
            {
                let pocet_dosp = Number(document.getElementById('pocet_dospely').value);
                let pocet_sen = Number(document.getElementById('pocet_senior').value);
                let pocet_deti = Number(document.getElementById('pocet_dite').value);

                let cena_dosp = Number(document.getElementById('cena_dospely').value);
                let cena_sen = Number(document.getElementById('cena_senior').value);
                let cena_deti = Number(document.getElementById('cena_dite').value);

                let cena_celkem = pocet_dosp * cena_dosp + pocet_sen * cena_sen + pocet_deti * cena_deti;
                document.getElementById('cena_celkem').textContent = cena_celkem;
            }

            document.getElementById('pocet_dospely').addEventListener('input', Proved_pocty);
            document.getElementById('pocet_senior').addEventListener('input', Proved_pocty);
            document.getElementById('pocet_dite').addEventListener('input', Proved_pocty);
        </script>
    </body>
</html>