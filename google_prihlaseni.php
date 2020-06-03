<?php
include_once './konfigurace.php';

if (!empty($_SESSION['id_uzivatel']))
{
    // Uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu.
    header('Location: index.php');
    exit();
}


require_once 'google-api-php-client-2.4.1/vendor/autoload.php';

$google_klient = new Google_Client();
$google_klient->setClientId('537251581249-k0u72vpbdurrumncns3nuhoqqot2fslc.apps.googleusercontent.com');
$google_klient->setClientSecret('h_0ZxKEt13xRoclvtA1JAI8S');
$google_klient->setRedirectUri('https://eso.vse.cz/~safj05/sem_prace/google_prihlaseni.php');
$google_klient->addScope('email');
$google_klient->addScope('profile');

$chyby = [];

if (isset($_GET["code"]))
{
    $token = $google_klient->fetchAccessTokenWithAuthCode($_GET["code"]);

    if (!isset($token['error']))
    {
        $google_klient->setAccessToken($token['access_token']);
        $_SESSION['token'] = $token['access_token'];
        $google_service = new Google_Service_Oauth2($google_klient);
        $data = $google_service->userinfo->get();

        if (!empty($data['email']))
        {
            $uzivatel = Databaze::Dotaz("SELECT * FROM uzivatel WHERE email = ?",
                array($data['email']))->fetch();
            if($uzivatel != null)
            {
                $_SESSION['id_uzivatel'] = $uzivatel['id_uzivatel'];
                $_SESSION['jmeno'] = $uzivatel['jmeno'];
                $_SESSION['opravneni'] = $uzivatel['opravneni'];
                header('Location: index.php');
                exit();
            }
            else
            {
                $chyby[] = "Je nám líto, nebyl nalezen uživatel s tímto emailem. Musíte být na tomto webu registrování 
                pod stejným emailem, jaký máte na Google, jinak nelze přihlášení přes Google použít.";
            }
        }
        else
        {
            $chyby[] = "Chyba v přihlášení.";
        }
    }
}
else
{
    $chyby[] = "Nebyla obdržena data od Googlu.";
}

if(count($chyby) > 0)
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
                <h2>Došlo k chybě.</h2>
                <?php
                echo "<div class='chyby'>";
                foreach ($chyby as $chyba)
                {
                    echo "<div>{$chyba}</div>";
                }
                echo "</div>";
                ?>
                <div><a href='prihlaseni.php'>Zpět na přihlášení.</a></div>
            </main>
        </body>
    </html>
    <?php
}