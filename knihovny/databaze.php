<?php

class Databaze
{
    private static $spojeni;

    private static $nastaveni = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

    /**
     * Vytváří připojení k databázi.
     * 
     * @param string $host - Lokace (IP nebo 'localhost') databáze.
     * @param string $databaze - Jméno databáze.
     * @param string $uzivatel - Jméno přihlašeného uživatele.
     * @param string $heslo - Heslo přihlašovaného uživatele.
     * @return - Vrací PDO Objekt.
     */
    public static function Pripoj_DB(string $host, string $databaze, string $uzivatel, string $heslo)
    {
        if (!isset(self::$spojeni))
        {
            try
            {
                self::$spojeni = @new PDO("mysql:host=$host;dbname=$databaze;charset=utf8mb4", $uzivatel, $heslo, self::$nastaveni);
            }
            catch(PDOException $vyjimka)
            {
                include udrzba.php;
                exit;
            }
        }
        return self::$spojeni;
    }

    /**
     * Základní funkce pro SQL dotaz na databázi.
     * 
     * @param string $sql - SQL dotazu.
     * @param array $parametry - Pole s parametry dotazu při používání vázaných proměnných (prepared statements) v $sql.
     * @return mixed - Vrací výsledek dotazu.
     */
    public static function Dotaz(string $sql, array $parametry = array())
    {
        $dotaz = self::$spojeni->prepare($sql);
        $dotaz->execute($parametry);
        return $dotaz;
    }
    
    /**
     * Obalová funkce pro PDO::prepare(). Vrací připravený, ale neprovedený dotaz.
     * 
     * @param stringt $sql
     * @return mixed - PDO objekt.
     */
    public static function Pripraveny_dotaz(string $sql)
    {
        $dotaz = self::$spojeni->prepare($sql);
        return $dotaz;
    }
}