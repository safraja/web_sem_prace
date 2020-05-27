<?php
    include_once './konfigurace.php';

    function Overit_datum($datum, $format = 'Y-m-d H:i')
    {
        $d = DateTime::createFromFormat($format, $datum);
        return $d && $d->format($format) == $datum;
    }

    $chyby = [];
    $vychozi_hodnoty = [];

    if(@$_POST["odeslani"] != null)
    {
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

        var_dump($zacatek, $konec);

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
                    OR (:konec1 > zacatek AND :konec1 < konec))",
                        array(':vozidlo' => $vozidlo, ':zacatek1' => $zacatek, ':zacatek2' => $zacatek,
                            ':konec1' => $konec, ':konec2' => $konec))->fetch(PDO::FETCH_COLUMN);

                    if($obsazene_vozidlo > 0)
                    {
                        $chyby[] = "Vybrané vozidlo je v daném termínu již zabrané.";
                    }
                }
            }

        }

        $popis = trim(htmlspecialchars(@$_POST["popis"]));
        if($popis == "")
        {
            $chyby[] = "Nevyplněn popis.";
        }

        if(count($chyby) > 0)
        {
            $vychozi_hodnoty = $_POST;
        }
        else
        {
            // uložit
        }
    }

    $neexistujici_zajezd = false;

    if(@$_GET["id_zajezdu"] != null)
    {
        $zaznam = Databaze::Dotaz("SELECT zajezd.*, DATE(zacatek) AS zacatek_datum,  
            TIME_FORMAT(zacatek, '%H:%i') AS zacatek_cas, DATE(konec) AS konec_datum,
            TIME_FORMAT(konec, '%H:%i') AS konec_cas
            FROM zajezd WHERE zajezd = ?",
            array($_GET["id_zajezdu"]))->fetch();

        if($zaznam == null)
        {
            $neexistujici_zajezd = true;
        }
        else
        {
            $vychozi_hodnoty = $zaznam;
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
        </header>
        <main class='formular'>
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
                            $text = htmlspecialchars("{$vozidlo["spz"]} (kapacita: {$vozidlo["kapacita"]})");
                            echo "<option value='{$vozidlo["id_prostredek"]}' {$vybrano}>{$text}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for='popis'>Začátek:</label>
                    <input type='date' id='zacatek_datum' name='zacatek_datum' required>
                    <input type='time' id='zacatek_cas' name='zacatek_cas' required>
                </div>

                <div>
                    <label for='popis'>Konec:</label>
                    <input type='date' id='konec_datum' name='konec_datum' required>
                    <input type='time' id='konec_cas' name='konec_cas' required>
                </div>

                <div>
                    <label for='popis'>Popis:</label>
                    <textarea id='popis' name='popis' placeholder='Popis zájezdu...' required>
                        <?php echo @htmlspecialchars($vychozi_hodnoty["popis"]); ?>
                    </textarea>
                </div>

                <div>
                    <input type='hidden' name='aktualizace'
                           value='<?php echo @htmlspecialchars($vychozi_hodnoty["aktualizace"]); ?>'>
                    <input type='submit' name='odeslani' value='Potvrdit'>
                </div>
            </form>
        </main>
    </body>
</html>