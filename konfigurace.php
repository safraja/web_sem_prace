<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Prague');

//************************************** Nastavení výchozí lokace knohoven pro třídy a trait **************************************

/**
 * Funkce sloužící k automatickému includování potřebných souborů v případě, kdy je volána třída nebo trait.
 * Pro funkgování je nezbytné, aby PRVNÍ ČÁST (před prvním podtržítkem) jména třídy (traitu) byla TOTOŽNÁ s
 * názvem souboru, ve kterém se třída nachází.
 * 
 * @param string $knihovna - Jméno volané třídy
 */
function Nacti_knihovnu_ze_souboru($knihovna)
{
    require "./knihovny/" . strtolower($knihovna) . ".php";
}

// Aktivování funkce automatického načítání tříd.
spl_autoload_register("Nacti_knihovnu_ze_souboru");


//************************************** Připojení databáze **************************************

Databaze::Pripoj_DB('localhost', 'safj05', 'safj05', 'zaedec4iethahN9eiz');

//************************************** Nastavení session **************************************

//Nastavení novější serializace.
ini_set('session.serialize_handler', 'php_serialize');

//Nastavení 5% pravděpodobnosti, že se po otevření nové SESSION promažou staré.
ini_set('session.gc_probability', "5");

//Nastavení délky života SESSION.
ini_set('session.gc_maxlifetime', "1800");
    
//Nastavení, aby se cookie přenášela pouze přes zabezpečené spojení (HTTPS).
ini_set('session.cookie_secure', 1);

//Zační DB SESSION.
session_start();

function Overit_datum($datum, $format = 'Y-m-d H:i')
{
    $d = DateTime::createFromFormat($format, $datum);
    return $d && $d->format($format) == $datum;
}