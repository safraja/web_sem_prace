<?php
include_once './konfigurace.php';

if (!empty($_SESSION['id_uzivatel']))
{
    unset($_SESSION['id_uzivatel']);
    unset($_SESSION['jmeno']);
    unset($_SESSION['opravneni']);
}

header('Location: index.php');