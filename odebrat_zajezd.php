<?php
include_once './konfigurace.php';

if(@$_SESSION["opravneni"] != "spravce")
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

    $zajezdy = Databaze::Dotaz("SELECT COUNT(*) FROM zajezd WHERE id_zajezd = ?",
        array($id))->fetch(PDO::FETCH_COLUMN);

    if($zajezdy == 0)
    {
        $chyby[] = "Vybraný zájezd neexistuje.";
    }


    $objednavka_exist = Databaze::Dotaz("SELECT COUNT(*) FROM objednavka
            WHERE id_zajezd = :id_zajezd",
        array(":id_zajezd" => $id))->fetch(PDO::FETCH_COLUMN);

    if($objednavka_exist > 0)
    {
        $chyby[] = "Tento zájezd nelze zrušit, jelikož ho má někdo objednaný.";
    }

    if(count($chyby) == 0)
    {
        Databaze::Dotaz("DELETE FROM zajezd WHERE id_zajezd = :id_zajezd", array(":id_zajezd" => $id));

        http_response_code("303");
        header("Location: index.php");
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
            <main style="padding: 50px;">
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