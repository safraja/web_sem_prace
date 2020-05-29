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

$podminka = "";

if($_SESSION["opravneni"] != "spravce")
{
    $podminka = "id_zakaznik = ?";
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
            <p>Požadovaná stránka nebyla nalezane.</p>
        </main>
    </body>
</html>