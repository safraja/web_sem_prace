<?php
include_once './konfigurace.php';
?>

<div id='menu-lista'>
    <nav>
        <ul>
            <li><a href='index.php'>Zájezdy</a></li>
            <?php
            if(@$_SESSION["id_uzivatel"] != null)
            {
                echo "<li><a href='objednavky.php'>Objednávky</a></li>";
            }
            ?>
        </ul>
    </nav>
    <?php
    if (!empty($_SESSION["id_uzivatel"]))
    {
        if(@$_SESSION["opravneni"] == "spravce")
        {
            echo "<span style='display: flex;'><a href='pridat_zajezd.php'><span class='tlacitko-prihl'>Přidat zájezd</span></a>";
            echo "<a href='objednavky_vse.php'><span class='tlacitko-prihl'>Přehled objednávek</span></a></span>";
        }
        echo "<a href='odhlaseni.php'><span class='tlacitko-prihl'>Odhlásit se</span></a>";
    }
    else
    {
        echo "<a href='prihlaseni.php'><span class='tlacitko-prihl'>Přihlásit se</span></a>";
    }
    ?>
</div>