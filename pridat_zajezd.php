<?php
    include_once './konfigurace.php';

    if(@$_POST["odeslani"] != null)
    {

    }
?>

<!doctype html>
<html>
    <head>
        <meta charset='utf8'>
        <title>Levné zájezdy</title>
        <link type='text/css' rel='stylesheet' href='styly.css'>
    </head>
    <body>
        <header class='horni-lista hlavni'>
            <h1>Přidat zájezd</h1>
        </header>
        <main>
            <label for='jmeno'>Jméno zájezdu</label>
            <input type='text' id='jmeno' name='jmeno' value='' required>

            <textarea id='popis' name='popis' placeholder='Popis zájezdu' required></textarea>

            <select id='lokalita' name='lokalita'>
                <?php

                ?>
            </select>


            <label for='jmeno'>Jméno zájezdu</label>
            <input type='text' id='jmeno' name='jmeno' value=''>
        </main>
    </body>
</html>