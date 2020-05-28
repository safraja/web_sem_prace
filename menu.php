<?php
include_once './konfigurace.php';
?>

<div id='menu-lista'>
    <nav>
        <ul>
            <li><a href="index.php">Zájezdy</a></li>
        </ul>
    </nav>
    <?php
    if (!empty($_SESSION["id_uzivatel"]))
    {
        echo "<a href='odhlaseni.php'><span class='tlacitko-prihl'>Odhlásit se</span></a>";
    }
    else
    {
        echo "<a href='prihlaseni.php'><span class='tlacitko-prihl'>Přihlásit se</span></a>";
    }
    ?>
</div>