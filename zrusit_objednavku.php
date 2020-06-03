<?php
include_once './konfigurace.php';

if(@$_SESSION["id_uzivatel"] == null)
{
    http_response_code(404);
    include_once "chyba.php";
    exit();
}

$chyby = [];

if($_GET["id_zajezdu"] == null)
{
    $chyby[] = "Nebylo zadáno ID zájezdu.";
}
else
{
    $id = intval($_GET["id_zajezdu"]);

    $objednavka_exist = Databaze::Dotaz("SELECT COUNT(*) FROM objednavka
            WHERE id_zakaznik = :id_zakaznik AND id_zajezd = :id_zajezd",
        array(":id_zajezd" => $id, ":id_zakaznik" => $_SESSION["id_uzivatel"]))->fetch(PDO::FETCH_COLUMN);

    if($objednavka_exist == 0)
    {
        $chyby[] = "Tento zájezd nemáte objednaný.";
    }
    else
    {
        Databaze::Dotaz("DELETE FROM objednavka
                WHERE id_zakaznik = :id_zakaznik AND id_zajezd = :id_zajezd",
            array(":id_zajezd" => $id, ":id_zakaznik" => $_SESSION["id_uzivatel"]));

        http_response_code("303");
        header("Location: objednavky.php");
    }
}

if(count($chyby) > 0)
{
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
            <main>
                <h2>Chyba</h2>
                <?php
                echo "<div class='chyby'>";
                foreach ($chyby as $chyba)
                {
                    echo "<div>{$chyba}</div>";
                }
                echo "</div>";
                ?>
                <div><a href='index.php'>Zpět na přehled zájezdů.</a></div>
            </main>
        </body>
    </html>
    <?php
}