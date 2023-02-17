<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use App\Api;
use App\Auth\Discord\DiscordApi;
use App\Env\Env;
use App\Errors\DiscordErr;
use App\User\{Account, Sess};

if(Api::get('action') == 'login'){
    DiscordApi::login();
}

if(!Api::get('code')){
    die('empty code');
}
if(empty($_COOKIE['discordState']) || empty($_GET['state']) || ($_COOKIE['discordState'] !== $_GET['state'])){
    die('invalid state');
}
$DiscordUser = DiscordApi::getUser();


if ($Account = Account::byDiscord($DiscordUser->id)){
    //Такой уже есть

}elseif($Account = Account::bySess()){
    $Account = $Account::create($Account->user_id,4);
}else{
    $Account = $Account::create(authTypeId: 4)
    or die('Ошибка создания акаунта');
}
$Account->saveDiscordUser($DiscordUser)
or die('Ошибка при сохранении');

$Sess = Sess::newSess($Account->id)
or die('Ошибка создания сессии');

$Sess->goToClient();