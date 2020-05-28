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

    if(empty($_POST["id_zajezd"]))
    {
        $typ_pridani = "pridani";
    }
    else
    {
        $typ_pridani = "upraveni";
        $zaznam_zajezdu = Databaze::Dotaz("SELECT * FROM zajezd WHERE id_zajezd = ?",
            array($_POST["id_zajezd"]));

        if($zaznam_zajezdu == null)
        {
            $chyby[] = "Upravovaný zájezd neexistuje.";
        }
        elseif($_POST["aktualizace"] != $zaznam_zajezdu["aktualizace"])
        {
            $chyby[] = "Záznam před vámi upravil jiný uživatel, pro přepsání jeho záznamu znovu odešlete formulář.";
            $vychozi_hodnoty["aktualizace"] = $zaznam_zajezdu["aktualizace"];
        }

    }

    $jmeno = trim(htmlspecialchars(@$_POST["jmeno"]));
    if($jmeno == "")
    {
        $chyby[] = "Nevyplněno jméno.";
    }

    $spravnost_datum = true;

    $zacatek = @$_POST["zacatek_datum"] . " " . @$_POST["zacatek_cas"];
    if(Overit_datum($zacatek) == false)
    {
        $chyby[] = "Zadáno nevalidní datum začátku.";
        $spravnost_datum = false;
    }
    $konec = @$_POST["konec_datum"] . " " . @$_POST["konec_cas"];
    if(Overit_datum($konec) == false)
    {
        $chyby[] = "Zadáno nevalidní datum konce.";
        $spravnost_datum = false;
    }

    $stat = "";

    if(empty($_POST["lokalita"]))
    {
        $chyby[] = "Nevybrána lokalita.";
    }
    else
    {
        $stat = $_POST["lokalita"];
        $existujici_stat = Databaze::Dotaz("SELECT COUNT(*) FROM stat WHERE kod = ?",
            array($stat))->fetch(PDO::FETCH_COLUMN);

        if($existujici_stat != 1)
        {
            $chyby[] = "Vybrán neexistující stát.";
        }
    }

    if(empty($_POST["vozidlo"]))
    {
        $chyby[] = "Nevybráno vozidlo.";
    }
    else
    {
        $vozidlo = $_POST["vozidlo"];
        $existujici_vozidlo = Databaze::Dotaz("SELECT COUNT(*) FROM dopravni_prostredek WHERE id_prostredek = ?",
            array($vozidlo))->fetch(PDO::FETCH_COLUMN);

        if($existujici_vozidlo != 1)
        {
            $chyby[] = "Vybráno neexistující vozidlo.";
        }
        else
        {
            if($spravnost_datum == true) //Pokud bylo zadáno validní datum, jinak nemá cenu ověřovat.
            {
                $obsazene_vozidlo = Databaze::Dotaz("SELECT COUNT(*) FROM zajezd 
                    WHERE vozidlo = :vozidlo AND ((:zacatek1 > zacatek AND :zacatek2 < konec) 
                    OR (:konec1 > zacatek AND :konec2 < konec))",
                    array(':vozidlo' => $vozidlo, ':zacatek1' => $zacatek, ':zacatek2' => $zacatek,
                        ':konec1' => $konec, ':konec2' => $konec))->fetch(PDO::FETCH_COLUMN);

                if($obsazene_vozidlo > 0)
                {
                    $chyby[] = "Vybrané vozidlo je v daném termínu již zabrané.";
                }
            }
        }
    }

    $cena_dospely = intval(@$_POST["cena_dospely"]);
    if($cena_dospely < 500)
    {
        $chyby[] = "Zadána nízká základní cena.";
    }

    $cena_senior = intval(@$_POST["cena_senior"]);
    if($cena_dospely < 500)
    {
        $chyby[] = "Zadána nízká snížená cena.";
    }

    $cena_dite = intval(@$_POST["cena_dite"]);
    if($cena_dospely < 500)
    {
        $chyby[] = "Zadána nízká dětská cena.";
    }


    $popis = trim(htmlspecialchars(@$_POST["popis"]));
    if($popis == "")
    {
        $chyby[] = "Nevyplněn popis.";
    }

    if(count($chyby) == 0)
    {
        if($typ_pridani == "pridani")
        {
            Databaze::Dotaz("INSERT INTO zajezd (`jmeno`, `popis`, `lokalita`, `vozidlo`, 
                    `cena_dite`, `cena_dospely`, `cena_senior`, `zacatek`, `konec`) 
                    VALUES (:jmeno, :popis, :lokalita, :vozidlo, :cena_dite, :cena_dospely,
                            :cena_senior, :zacatek, :konec)",
                array(":jmeno" => $jmeno, ":popis" => $popis, ":lokalita" => $stat,
                    ":vozidlo" => $vozidlo, ":cena_dite" => $cena_dite, ":cena_dospely" => $cena_dospely,
                    ":cena_senior" => $cena_senior, ":zacatek" => $zacatek, ":konec" => $konec));
        }
        else
        {
            Databaze::Dotaz("UPDATE zajezd SET `jmeno`=:jmeno,`popis`=:popis,
                    `lokalita`=:lokalita,`vozidlo`=:vozidlo,`cena_dite`=:cena_dite,`cena_dospely`=:cena_dospely,
                    `cena_senior`=:cena_senior,`zacatek`=:zacatek,`konec`=:konec,`aktualizace`=NOW(),`moderator`=:moderator
                    WHERE `id_zajezd`= :id_zajezd",
                array(":jmeno" => $jmeno, ":popis" => $popis, ":lokalita" => $stat, ":vozidlo" => $vozidlo,
                    ":vozidlo" => $vozidlo, ":cena_dite" => $cena_dite, ":cena_dospely" => $cena_dospely,
                    ":cena_senior" => $cena_senior, ":konec" => $konec, ":zacatek" => $zacatek,
                    ":moderator" => $_SESSION["id_uzivatel"], ":id_zajezd" => $_POST["id_zajezd"]));
        }
    }
}

$neexistujici_zajezd = false;
$zajezd = null;

if(@$_GET["id_zajezdu"] != null)
{
    $zaznam = Databaze::Dotaz("SELECT zajezd.*, DATE(zacatek) AS zacatek_datum,  
            TIME_FORMAT(zacatek, '%H:%i') AS zacatek_cas, DATE(konec) AS konec_datum,
            TIME_FORMAT(konec, '%H:%i') AS konec_cas
            FROM zajezd WHERE id_zajezd = ?",
        array($_GET["id_zajezdu"]))->fetch();

    if($zaznam == null)
    {
        $neexistujici_zajezd = true;
        $chyby[] = "Vybraný zájezd neexistuje.";
    }
    else
    {
        $vychozi_hodnoty = $zaznam;
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
                    <input type='number' id='pocet_dospely' name='pocet_dospely'
                           value='<?php echo intval(@$vychozi_hodnoty["pocet_dospely"]); ?>' required>
                </div>

                <div>
                    <label for='pocet_senior'>Počet studentů/seniorů:</label>
                    <input type='number' id='pocet_senior' name='pocet_senior'
                           value='<?php echo intval(@$vychozi_hodnoty["pocet_senior"]); ?>' required>
                </div>

                <div>
                    <label for='pocet_dite'>Počet dětí:</label>
                    <input type='number' id='pocet_dite' name='pocet_senior'
                           value='<?php echo intval(@$vychozi_hodnoty["pocet_dite"]); ?>' required>
                </div>

                <div>
                    <?php
                    echo "<input type='hidden' name='id_zajezd' value='{$zajezd["id_zajezd"]}'>";
                    ?>
                    <input type='submit' name='odeslani' value='Potvrdit'>
                </div>
            </form>
            <div><a href='index.php'>Zpět na přehled zájezdů.</a></div>
        </main>
    </body>
</html>