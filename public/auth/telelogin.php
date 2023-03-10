<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use App\Auth\Telegram\{Telegram, TeleUser};
use App\User\User;


$User = new User();
$debug = false;
if(!empty($_GET['debug'])){
    setcookie('debug', 1);
    $debug = true;
}

$TeleUser = new TeleUser();

if(isset($_GET['logout'])) {
    setcookie('tg_user', '');
    header('Location: /telelogin.php');
    die();
}



$Telegram = new Telegram();
echo <<<HTML
    <!DOCTYPE html>
    <html lang="ru">
        <head>
        <meta charset="utf-8">
        <title>Login by Telegram</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
            <div style="margin: 0 auto; width: max-content; text-align: center">
                <h1>Graph Tools</h1>
                {$Telegram->anonymous('auth/teleauth.php')}
            </div>
        </body>
    </html>
HTML;

