<?php
    include_once './konfigurace.php';

    function Overit_datum($datum, $format = 'Y-m-d H:i')
    {
        $d = DateTime::createFromFormat($format, $datum);
        return $d && $d->format($format) == $datum;
    }

    if(@$_SESSION["opravneni"] != "spravce")
    {
        http_response_code(404);
        include_once "chyba.php";
        exit();
    }

    $chyby = [];
    $vychozi_hodnoty = [];

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
            array($_POST["id_zajezd"]))->fetch();

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
        }
        else
        {
            if(count($chyby) > 0)
            {
                $vychozi_hodnoty = $_POST;
                $vychozi_hodnoty["aktualizace"] = $zaznam["aktualizace"];
                $odlisne_hodnoty = [];
                foreach ($vychozi_hodnoty as $klic => $hodnota)
                {
                    if(array_key_exists($klic, $zaznam) && $hodnota != $zaznam[$klic])
                    {
                        $odlisne_hodnoty[$klic] = $zaznam[$klic];
                    }
                }
            }
            else
            {
                $vychozi_hodnoty = $zaznam;
            }
        }
    }
?>

<!doctype html>
<html>
    <head>
        <meta charset='utf8'>
        <title>VŠE Travel | Úpravy</title>
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
                echo "<p>Vybraný zájezd neexistuje.</p>";
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

                if(@count($odlisne_hodnoty) > 0)
                {
                    echo "<div>";
                    echo "<div><strong>Odlišné hodnoty:</strong></div>";
                    foreach ($odlisne_hodnoty as $klic => $hodnota)
                    {
                        $klic = htmlspecialchars($klic);
                        $hodnota = htmlspecialchars($hodnota);
                        echo "<div>{$klic}: {$hodnota}</div>";
                    }
                    echo "</div>";
                }
            }
            ?>
            <h2>
                Přidat zájezd
            </h2>
            <form method='post'>
                <div>
                    <label for='jmeno'>Jméno:</label>
                    <input type='text' id='jmeno' name='jmeno'
                           value='<?php echo @htmlspecialchars($vychozi_hodnoty["jmeno"]); ?>' required>
                </div>

                <div>
                    <label for='lokalita'>Lokalita:</label>
                    <select id='lokalita' name='lokalita'>
                        <?php
                        $staty = Databaze::Dotaz("SELECT kod, jmeno FROM stat")->fetchAll(PDO::FETCH_KEY_PAIR);
                        $vybrano = "";
                        foreach ($staty as $kod => $jmeno)
                        {
                            if(@$vychozi_hodnoty["lokalita"] == $kod)
                            {
                                $vybrano = "selected";
                            }
                            else
                            {
                                $vybrano = "";
                            }
                            echo "<option value='{$kod}' {$vybrano}>{$jmeno}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for='vozidlo'>Přiřazené vozidlo:</label>
                    <select id='vozidlo' name='vozidlo'>
                        <?php
                        $vozidla = Databaze::Dotaz("SELECT id_prostredek, spz, kapacita 
                            FROM dopravni_prostredek")->fetchAll();
                        $vybrano = "";
                        foreach ($vozidla as $vozidlo)
                        {
                            if(@$vychozi_hodnoty["lokalita"] == $vozidlo["id_prostredek"])
                            {
                                $vybrano = "selected";
                            }
                            else
                            {
                                $vybrano = "";
                            }
                            $text = htmlspecialchars("{$vozidlo["spz"]} (kapacita: {$vozidlo["kapacita"]})");
                            echo "<option value='{$vozidlo["id_prostredek"]}' {$vybrano}>{$text}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for='popis'>Začátek:</label>
                    <input type='date' id='zacatek_datum' name='zacatek_datum'
                           value='<?php echo @$vychozi_hodnoty["zacatek_datum"]; ?>' required>
                    <input type='time' id='zacatek_cas' name='zacatek_cas'
                           value='<?php echo @$vychozi_hodnoty["zacatek_cas"]; ?>' required>
                </div>

                <div>
                    <label for='popis'>Konec:</label>
                    <input type='date' id='konec_datum' name='konec_datum'
                           value='<?php echo @$vychozi_hodnoty["konec_datum"]; ?>' required>
                    <input type='time' id='konec_cas' name='konec_cas'
                           value='<?php echo @$vychozi_hodnoty["konec_cas"]; ?>' required>
                </div>

                <div class='form-ceny'>
                    <div>
                        <label for='jmeno'>Základní cena:</label>
                        <input type='number' id='cena_dospely' name='cena_dospely' min='500'
                               value='<?php echo @$vychozi_hodnoty["cena_dospely"]; ?>' required>
                    </div>
                    <div>
                        <label for='jmeno'>Snížená cena:</label>
                        <input type='number' id='cena_senior' name='cena_senior' min='500'
                               value='<?php echo @$vychozi_hodnoty["cena_senior"]; ?>' required>
                    </div>
                    <div>
                        <label for='jmeno'>Dětská cena:</label>
                        <input type='number' id='cena_dite' name='cena_dite' min='500'
                               value='<?php echo @$vychozi_hodnoty["cena_dite"]; ?>' required>
                    </div>
                </div>

                <div>
                    <label for='popis'>Popis:</label>
                    <textarea id='popis' name='popis' placeholder='Popis zájezdu...' required><?php echo @htmlspecialchars($vychozi_hodnoty["popis"]); ?></textarea>
                </div>

                <div>
                    <?php
                    if(!empty($vychozi_hodnoty))
                    {
                        echo "<input type='hidden' name='aktualizace' value='" .
                            @$vychozi_hodnoty["aktualizace"] . "'>";
                        echo "<input type='hidden' name='id_zajezd' 
                            value='{$vychozi_hodnoty["id_zajezd"]}'>";
                    }
                    ?>
                    <input type='submit' name='odeslani' value='Potvrdit'>
                </div>
            </form>
            <div><a href='index.php'>Zpět na přehled zájezdů.</a></div>
        </main>
    </body>
</html>