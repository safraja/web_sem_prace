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



require_once 'google-api-php-client-2.4.1/vendor/autoload.php';

$google_klient = new Google_Client();
$google_klient->setClientId('537251581249-k0u72vpbdurrumncns3nuhoqqot2fslc.apps.googleusercontent.com');
$google_klient->setClientSecret('h_0ZxKEt13xRoclvtA1JAI8S');
$google_klient->setRedirectUri('https://eso.vse.cz/~safj05/sem_prace/google_prihlaseni.php');
$google_klient->addScope('email');
$google_klient->addScope('profile');



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
                    <div class='google_tlac'>
                        <input type="submit" value="Přihlásit">
                        <?php echo '<a href="' . $google_klient->createAuthUrl() . '"><img class="google_log" src="google_log.png" /></a>'; ?>
                    </div>
                </article>
            </form>
        </main>
    </body>
</html>