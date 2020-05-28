<?php
include_once './konfigurace.php';

if (!empty($_SESSION['id_uzivatel']))
{
    // Uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu.
    header('Location: index.php');
    exit();
}

$chyby = [];
if (!empty($_POST))
{
    if($_POST['email'] == null || $_POST['heslo'] == null)
    {
        $chyby[] = "Nezadány oba údaje.";
    }
    else
    {
        $uzivatel = Databaze::Dotaz("SELECT * FROM uzivatel WHERE email = ?",
            array($_POST['email']))->fetch();

        if($uzivatel != null)
        {
            if (password_verify($_POST['heslo'], $uzivatel['heslo']))
            {
                $_SESSION['id_uzivatel'] = $uzivatel['id_uzivatel'];
                $_SESSION['jmeno'] = $uzivatel['jmeno'];
                $_SESSION['opravneni'] = $uzivatel['opravneni'];
                header('Location: index.php');
                exit();
            }
            else
            {
                $chyby[] = "Zadán špatný email nebo heslo.";
            }
        }
        else
        {
            $chyby[] = "Zadán špatný email nebo heslo.";
        }
    }
}

?>

<!doctype html>
<html>
    <head>
        <meta charset='utf8'>
        <title>VŠE Travel | Hlavní stránka</title>
        <link type='text/css' rel='stylesheet' href='styly.css'>
    </head>
    <body>
        <header class='horni-lista hlavni'>
            <h1>VŠE Travel</h1>
            <?php
            include_once "menu.php";
            ?>
        </header>
        <main id='stranka-prihlasrni'>
            <form method='post'>
                <article id='form-prihlaseni'>
                    <h1 style="text-align: center;">Přihlášeni</h1>
                    <?php
                    if(!empty($chyby))
                    {
                        echo "<div>";
                        foreach ($chyby as $chyba)
                        {
                            echo "<div>{$chyba}</div>";
                        }
                        echo "</div>";
                    }
                    ?>
                    <div>
                        <label for='popis'>Email:</label>
                        <input type='text' id='email' name='email' required>
                    </div>
                    <div>
                        <label for='popis'>Heslo:</label>
                        <input type='password' id='heslo' name='heslo' required>
                    </div>
                    <div>
                        <input type='submit' value='Přihlásit'>
                    </div>
                </article>
            </form>
        </main>
    </body>
</html>